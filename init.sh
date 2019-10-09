#!/bin/sh

echo "CREATE CONTAINERS"
echo "---------"
docker-compose build
echo

#intermediate start/stop to prevent "ERROR 2002 (HY000): Can't connect to local MySQL server through socket '/var/run/mysqld/mysqld.sock'" (not sure why this is needed...)
docker-compose up -d
docker-compose kill
echo

echo "INIT DB"
echo "---------"
docker-compose up -d
docker-compose exec mysql /bin/bash -c "mysql -u root mysql < /var/app/init.sql && echo 'SQL file run.'"
docker-compose kill
echo

echo 'done.'
