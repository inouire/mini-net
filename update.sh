#!/bin/bash
echo "-------------------------------------------------------"
echo "Updating your mini-net installation"
echo "(use 'install' option to also install project dependencies)"
echo "(use 'update' option to also update project dependencies)"
echo "-------------------------------------------------------"

echo "* Retrieving last version of mini-net from github"
git pull

if [ "$1" == "install" ]; then
    echo "* Installing dependencies"
    composer install
fi
if [ "$1" == "update" ]; then
    echo "* Updating dependencies"
    composer update
fi

echo "* Clearing cache for env and prod environment"
php app/console cache:clear --env=dev
php app/console --no-debug cache:clear --env=prod
