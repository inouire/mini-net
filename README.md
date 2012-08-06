mini-net
========

Mini-net is a tiny social network, made for family.
It is based on the PHP framework Symfony2, and is using third party library such as Bootstrap, JQuery and lightbox

As mini-net software, this README is still under construction

## Requirements

* Web server with PHP5
* Database engine supported by Doctrine ORM, and PHP module for this database

## Installation (for Debian 6 + Apache)

This guide is made for Debian6 with an Apache2 web server, feel free to adapt it to your own configuration.

### Set up web server / database / tools

Install all the needed packages
``` bash
$ apt-get install apache2
$ apt-get install mysql-server
$ apt-get install php5 php5-mysql php5-sqlite php-apc php5-intl
$ apt-get install git
```

### Set up the project

Clone mini-get git repository
``` bash
$ git clone https://github.com/inouire/mini-net.git
```

Automatically get dependencies with vendors script
``` bash
$ php bin/vendors install
```

### Configure apache2 virtual host

Configure a new apache2 virtual host on web directory 

Set write permission for web server on app/cache et app/logs

For clean urls, activate mod_rewrite and AllowOverride of .htaccess
``` bash
$ a2enmod rewrite
```

In your php.ini, set configuration recommended by Symfony
* set date.timezone in php.ini (Europe/Paris)
* set short open tags to Off

Check your config at http://youradress/config.php
(if not on localhost, temp disable source check at the beginning of the file config.php)

### Configure database

Copy app/config/parameters.ini.default to app/config/parameters.ini
Modify it with your settings (database type, host, name, port...)

Create database with doctrine (if not already) 
``` bash
$ php app/console doctrine:database:create
```

Create schema with doctrine
 ``` bash
$ php app/console doctrine:schema:update --force
```

Add one or more users with doctrine
 ``` bash
$ php app/console fos:user:create
```

### Test application

Go to http://youradress/home
You should get a login page, use the login/password of the user you created the step before
Enjoy

### Troubleshooting

If running prod environnement, don't forget to clean the cache after each modification
``` bash
$ rm -rf app/cache/prod
```

Use logs at app/logs/dev.log or app/logs/prod.log to know what's going on
 
