#!/bin/bash

cd ../

#Install composer
composer install
# Create database and tables.
php app/console doctrine:database:create
php app/console doctrine:schema:create
