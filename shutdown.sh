#!/bin/bash

if [ -f "docker-compose-prod.yml" ]; then
    docker-compose -f docker-compose-prod.yml down
else
    docker-compose down
fi

