#!/bin/sh

echo "START CONTAINER"
echo "---------"
docker-compose up -d
echo

echo "RUN APP"
echo "---------"
docker-compose run app php /var/app/src/app.php
echo

echo "KILL CONTAINER"
echo "---------"
docker-compose kill
echo

echo 'done.'