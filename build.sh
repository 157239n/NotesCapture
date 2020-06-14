#!/bin/bash

if [ -f "docker-compose-prod.yml" ]; then
    docker-compose -f docker-compose-prod.yml build
    docker-compose -f docker-compose-prod.yml up -d
else
    docker-compose build
    docker-compose up -d
fi

