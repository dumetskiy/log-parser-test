#!/bin/bash

set -e

cd `dirname $0`/..

ENVIRONMENT=${1:-dev}

if [[ $ENVIRONMENT == 'dev' || $ENVIRONMENT == 'local' ]]; then
    docker-compose -f docker-compose.yml -f docker-compose.local.yml build
fi
