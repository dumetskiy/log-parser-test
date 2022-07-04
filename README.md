# Log Parser

## Tech Stack
* PHP-fpm (version 8.1.1 Alpine)
* Nginx (version 1.21.6 Alpine)
* Symfony (version 6.2)
* ElasticSearch (version 8.2.3)
* Logstash (version 8.2.3)
* Kibana (version 8.2.3)

## Installation Guide

1. Clone the repository: `git clone https://github.com/dumetskiy/log-parser-test.git`
2. Make sure you have docker (with compose) installed on your machine
3. Inside the project directory make a copy of a default `.env` file: `cp .env .env.local`
4. Build the application using `./docker/build.sh local`
5. Start application containers using `./docker/start.sh local`
6. Get into the PHP-FPM container by executing `docker exec -it parser.php-fpm sh`
7. Install application dependencies and perform basic application configuration by executing `composer install` from the inside of the php-fpm container

## Composer command

Composer commands were added to simplify the most command operations triggering. They all should be executed from the inside of php-fpm container

`composer cs-fix` - runs PHP-cs-fixer in a fix mode 

`composer cs-check` - runs PHP-cs-fixer in a dry-run mode 

`composer phpstan-analyse` - runs PHPStan analysis of the codebase

`composer phpunit` - runs PHPUnit tests using the `phpunit.xml(.dist)` configuration

## How to use

Before the first run it is necessary to additionally run the Elastic mapping setup command: `bin/console log-parser:elastic:init`
This will init the optimal index mapping schema and will increase the index performance.

All logs are being stored in `logs` elastic index.

For the convenience of use Kibana was installed and configured (available on `http://127.0.0.1:5601` by default).

You can access ElasticSearch and LogStash monitoring details at `http://127.0.0.1:5601/app/monitoring`

Elastic console is available at `http://127.0.0.1:5601/app/dev_tools#/console` (in case you will decide to remove or inspect the index)

The command call structure is `log-parser:parse [--strategy [STRATEGY]] [--offset [OFFSET]] [--] <filename>` where:

* `filename` is the name of the file inside of `data` project directory. There is a `sample_log.txt` file there available for test runs.
* `strategy` is a handle of the log processing strategy (described more in detail below). By default, there are two strategies available:
  * `parse_and_proxy` (default) - parses raw log lines,normalizes to JSON and transfers it to LogStash
  * `raw_proxy` - is an additional strategy created just for my own interest (since it does not comply with the task details). The idea of it is passing raw log lines to LogStash and parse the data using the grok filter within the LogStash pipeline.
* `offset` (defaults to `0`) - the amount of lines to be skipped from the beginning of the file. This argument is used to restore the interrupted command parsing process.
* `-v` - use this flag in case you would like to increase the verbosity of the command output (e.g show HTTP client messages and extra details about the operations performed). 

There is no extra input required as soon as the command was started. 

You can interrupt the process at any moment with a minimal risk of data loss or entries duplication

## How it works
### Parse Log Command

Once the command has been started, it generates the first configuration object - `ParseOperationConfiguration` using the `ParseConfigurationFactory`.
There is a total of 3 configurations, and this configuration stores the current execution options plus the target log file resource wrapper object (`\SplFileObject`).
So besides gathering the command options and arguments the `ParseConfigurationFactory` service does the input validation, checks the provided file existence and "readability".
In addition to that the factory would insert an extra configuration object link into the operation configuration - `ParserConfiguration`.

`ParserConfiguration` is a general application configuration which stores the higher-level options: the amount of log lines to be processed within a single batch and the path to the log files directory to inspect.
Those configurations can be changed by changing th `log_parser.configuration.parsing.batch_size` and `log_parser.configuration.filesystem.logs_directory` parameters.

After that those configurations are passed to the `LogParseOperationProcessor` service and the parsing process itself begins.

It was mentioned before that the command allows you to select the "processing strategy". 
THis term is used to denote there might be more than one way to handle the data. 

In order to keep the application both SOLID and extensible the `Handler Stacks` were implemented.
This is a "pattern" which is something between the "Chain of Responsibility" and "decorator" patterns.

The idea of this approach is providing an ability to create a set of reusable handlers for the logs data and an ability to group and order them into handler stacks. 

Each handler stack represents a "strategy" and each handler can be used in any amount of handler stacks.

In order to add a new handler you can just create the class implementing `LogProcessingHandlerInterface` and add special custom PHP8 attribute(s) to it:

```php
#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::PARSE_AND_PROXY,
    executionOrder: 3
)]
#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::RAW_LOG_PROXY,
    executionOrder: 2
)]
class FinishProcessingHandler implements LogProcessingHandlerInterface
...
```

There is a `LogHandlerStackFactoryCompilerPass` and a `LogHandlerStackFactory` which automatically gather all handlers and group them into handler stacks. 

After that, the `LogHandlerStackFactory::getForStrategy()` method can be used to fetch the handler stack for a certain strategy anywhere.

Currently configured strategies and their handler stacks are:

#### RAW_LOG_PROXY
* `InitProcessingHandler`
* `RawLogstashTransferHandler`
* `FinishProcessingHandler`

#### PARSE_AND_PROXY
* `InitProcessingHandler`
* `JsonLogNormalizationHandler`
* `JsonLogstashTransferHandler`
* `FinishProcessingHandler`

So you are totally free to extend the application logic however you like by adding new and modifying existent strategies and handlers which can allow you to easily configure: new log storages, new log formats, new middlewares for additional events management, logging and basically anything else.

#### Back to the command

Once the `LogParseOperationProcessor` is being called, it fetches the handler stack for the selected strategy.
After that it uses the file descriptor object to operate the log file in the following way:

1. Moving the pointer to the "offset" line value
2. Iteratively generating batches of log lines
3. Running every batch through every handler in the stack

By doing this the data is being prepared and sent to LogStash, where it is getting processed further.

### LogStash
The LogStash is configured to operate in a persisted queue mode with a relatively tight queued events limit which ensures maximum data stability even though it makes the process a bit slower.

The LogStash pipeline is configured to be able to handle both implemented strategies.

Depending upon the "Content-Type" header value it will operate differently in order to process the supplied data correctly:

#### Content-Type: application/json-lines
This header value means the logs were parsed before and the received data is actually the json lines.
Log lines will be separated, JSON parsed and the data sent to ElasticSearch

#### Content-Type: application/raw-log
This header value indicates the received data is in fact a number of raw log lines.
Log lines will be separated, parsed using the custom grok filter configuration and sent to ElasticSearch.

At the end of everything such LogStash configuration allows to safely pass the logs in any format into it and be confident that the data will be transferred to ElasticSearch safely.

### Some metrics

Initially multiple strategies were implemented  for 2 reasons:
1. Test and compare the performance 
2. Show and test the application extensibility and configurability

So in order to make a good comparison a 100 million lines log file was generated and processed both ways.
The results of the test can be found below.

Strategy        | Total time | Time per 1kk lines | PHP memory usage
---             | ---        | ---                | --- 
RAW_LOG_PROXY   | 00:12:22   | 00:01:14           | 14MB
PARSE_AND_PROXY | 00:05:44   | 00:00:34           | 16MB 

As you can clearly see by the results, parsing logs on PHP side is more than twice as fast as delegating parsing to LogStash.

The reason is quite simple: since persistent LogStash queues are single-threaded, with RAW_LOG_PROXY PHP just awaits for the thread 
to be available again and let LogStash process another batch data while with PARSE_AND_PROXY PHP is doing the next batch parsing while LogStash pipeline is processing the previous data batch

### Init Schema Command
This command is pretty simple and requires no extra arguments to be provided. 
The index configuration is stored in the YAML file (`config/elastica/configuration.yaml`).

The data is being loaded from the file, transformed into the `ElasticIndexConfiguration` objects (you can configure any amount of indexes if necessary)
and sent to ElasticSearch using the scoped HttpClient wrapped into `ElasticApiClient`

### Log analytics API
In order to expose a Swagger UI and create a self-documenting API endpoint, the NelmioApiDocBundle is used.
By default, Swagger UI is available at `127.0.0.1:8899`

The documentation is configured with PHP8 attributes.

The API endpoint is powered with `FOSRestBundle`.

Though neither `FOSRestBundle` or Symfony provide a ParamConverter which could handle the query parameters transformation and validation into a DTO.
Since the filters amount can grow and there is a need to validate the provided filter values it could be a good idea to do this before the request even reaches the controller action.

This is why it was decided to develop a custom `RequestQueryDataParamConverter` which transforms all query parameters into a DTO and runs Symfony validator over it.

In future this will allow to keep the controller actions clean and compact. 

The communication between ElasticSearch is performed with a simple scoped HttpClient wrapped into `ElasticApiClient`. 
This has been done because most of the ElasticSearch client libraries are quite heavyweight in terms of provided functionality and the depth of integration into Symfony and Doctrine.

The only lib used to simplify the interactions with ElasticSearch is th `elasticsearch-query-builder` which is being used to transform the request filters into ElasticSearch filter.

On average, the count API call over the database with 10 million entries takes around 400ms once all data is indexed.

## Additional Tools Used
1. *PHPStan* - whole codebase is compliant with PHPStan level 9 rules which is much more than usually considered for the project (level 4-7)
2. *PHP-cs-fixer* - whole codebase is inspected by the fixer tool including `@Symfony` and `@Symfony:risky` rule groups

## Tests

It's a shame but there are not too many tests written right now due to the lack of time. I practically had to choose between the quality of the code and the test coverage within a given time frame.

Tests are currently powered with *PHPUnit 10.0-dev* which allows to cover the code with all PHP8.1 features used.

## Clarifications and Comments

There are some things worth mentioning in the way the application is implemented.

### Open API Spec
There was a number of minor changes made in the end application compared to the provided OpenAPI spec.
1. *HTTP codes*: an addition to listed 200 and 400 responses the 500 response code for the internal server errors.
2. *Responses*: original spec didn't specify any details of the 400 response content. Those details and functionality of returning responses for 400 and 500 errors were added
3. *Query arrays*: the original spec requested to use query array format described in RFC 3986 (section 3.4): `?foo=a&foo=b&foo=c`. I have tried to understand if it is possible to switch PHP and FPM to recognize query arrays in this format but unfortunately haven't found any possibility so far. PSR-7 also does not mention anything related to this topic. And this is why I had to modify the Open API doc in order to use classic PHP way of dealing with it with square braces.

### Use of Serializer and "data classes"
Some application features are implemented so that they operate arrays instead of denormalizing data into DTOs or ValueObjects. This is done in the parts of the application where the performance is valued over the best practices (e.g. JsonLogNormalizationHandler which can be called millions of times).

### Why ELK stack?
The main values I was trying to achieve when developing the application were data consistency and both indexing and search performance.
ELK stack is ideal for this for a number of reasons:
1. ElasticSearch is an ideal instrument to store huge indexes and search over them.
2. LogStash queue gives the maximum possible persistence level with all the mechanisms of data protection in case of an emergency. And since it is a fully asynchronous queue it returns the OK response as soon as it receives the data which saves you from most of the situations when the command termination interrupts a long synchronous operation making it impossible to track the data status.

### Progress restore mechanism
Progress restore mechanism is based on outputting the current offset value in the terminal. This value is not cached anywhere since it would add extra complexity plus it would be a possible cause of failures when the cache transaction is being interrupted in the middle or before it even starts.

Immediate LogStash responses give an almost fail-proof mechanism of progress tracking. 

In addition to that the console output is being handled with Monolog console handler which allowed to duplicate the console output to the log file which gives you another way to check where was the command interrupted.

So even though this approach is less comfortable compared to having the last processed line suggested to the user automatically (from cache) but it is way more optimal in terms of performance and data consistency.

### Data availability
LogStash queue is asynchronous, so even with a custom configuration limiting it to keeping only 3 events scheduled it will still have around 30k log lines still pending (with default batch size) after the command is completed/interrupted. This might take several seconds to handle.
Also, because of a number of obvious reasons, ElasticSearch will keep indexing the data even after the operation is finished. So it is okay for the API endpoint to be quite slow in the first seconds after the parsing.
