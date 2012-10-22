#!/bin/bash
echo "-------------------------------------------------------"
echo "Updating your mini-net installation"
echo "(use 'full' option to also update project dependencies)"
echo "-------------------------------------------------------"

echo "* Retrieving last version of mini-net from github"
git pull

if [ "$1" == "full" ]; then
    echo "* Updating dependencies"
    composer install
fi


echo "* Clearing cache for env and prod environment"
php app/console cache:clear --env=dev
php app/console --no-debug cache:clear --env=prod
