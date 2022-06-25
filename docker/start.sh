#!/bin/bash

set -e

ENVIRONMENT=${1:-dev}

cd `dirname $0`/..

if [[ $ENVIRONMENT == 'dev' || $ENVIRONMENT == 'local' ]]; then
    docker-compose -f docker-compose.yml -f docker-compose.local.yml up -d
fi
