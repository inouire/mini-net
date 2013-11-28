#!/bin/bash
echo "-------------------------------------------------------"
echo "Updating your mini-net installation"
echo "-------------------------------------------------------"

echo "* Retrieving last version of mini-net from github"
git checkout master
git pull

echo "* Installing dependencies"
composer install --no-dev --prefer-dist

echo "* Clearing cache for prod environment"
php app/console cache:clear --env=prod --no-debug 
