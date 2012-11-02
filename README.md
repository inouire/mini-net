mini-net
========

Mini-net is a tiny social network, made for family.

It is based on the PHP framework Symfony2, and is using third party libraries such as [Bootstrap](http://twitter.github.com/bootstrap/), [jQuery](http://jquery.com/) and [Fancybox](http://fancyapps.com/fancybox/).

## Requirements

* Web server with PHP >= 5.3 
* Database engine supported by Doctrine ORM, and the PHP driver for this database

## Installation (for Debian 6 + Apache)

This guide is made for Debian6 with an Apache web server and a MySQL database, feel free to adapt it to your own configuration.

### Set up web server / database / tools

Install all the needed softwares:
``` bash
apt-get install apache2
apt-get install mysql-server
apt-get install php5 php5-mysql php5-sqlite php-apc php5-intl php5-gd
apt-get install git
```

Get composer on your system:
``` bash
curl -s https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```
Tip: you might need to modify you php.ini to install composer, juste follow the instructions of the installer.
For more information about composer see [getcomposer.org](http://getcomposer.org)

### Set up the project

Clone mini-get git repository from github
``` bash
git clone https://github.com/inouire/mini-net.git
```

Copy `app/config/parameters.yml.default` to `app/config/parameters.yml`
``` bash
cp app/config/parameters.yml.default app/config/parameters.yml
```

Edit `app/config/parameters.yml` with your database and locale settings

Automatically get project dependencies with composer
``` bash
composer install
```

Tip: if you are behing a proxy, set git variables http.proxy and/or https.proxy
``` bash
git config --global http.proxy http://login:password@host:port/
git config --global https.proxy https://login:password@host:port/
```

### Configure apache2 virtual host

Configure a new apache2 virtual host on "web" directory of git repository

For clean urls, activate mod_rewrite and AllowOverride of .htaccess in your apache virtual host
``` bash
a2enmod rewrite
```

Tip: if you intend to run a production environement, modifiy web/.htaccess like this:
```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ app.php [QSA,L]
</IfModule>
```
 
Give your web server read access, and set write permission for web server on app/cache, app/logs and web directory
``` bash
usermod -G www-data -a your_user 
chgrp -R www-data *
chmod g+rwx app/{cache,logs}
chmod g+rwx web/*
```

Edit /etc/php5/apache2/php.ini to set configuration recommended by Symfony
* set date.timezone (for example Europe/Paris)
* set short open tags to Off
* set max upload size to 10M (only images need to be uploaded in mini-net)

Restart your web server to take config into account

Check your config with Symfony built-in script at http://youradress/config.php
(if not on localhost, temporary disable source check at the beginning of the file config.php)

### Configure database

Create database with doctrine (if not already) 
``` bash
php app/console doctrine:database:create
```

Automatically create schema with doctrine
 ``` bash
php app/console doctrine:schema:update --force
```

Add one or more users with doctrine
 ``` bash
php app/console fos:user:create
```

### Test application

Go to http://youradress/home
You should get a login page, use the login/password of the user you created the step before

Enjoy !

## Troubleshooting

If you're having some issue and can't figure it with the message displayed in your browser, use the logs at app/logs/dev.log (or app/logs/prod.log for prod environment) to know what's going on

If running prod environnement, don't forget to clean the cache after each modification
``` bash
php app/console --no-debug cache:clear --env=prod
```


