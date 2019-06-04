# Webkul opencart console application

## Overview

Webkul opencart console application is used to automate the opencart installation, module creation, clearing the cache files and database import/export via console commands.

## Getting started

Clone this project over the directory where you will keep your opencart root setup.

## Prerequisite

Before using this app, you must have opencart installed (not for the opencart installer console) on your server and composer on your system. If composer is not installed on your system then run the following commands-
- Syntax:  curl -s http://getcomposer.org/installer | php
- Syntax:  php composer.phar install
- After successfully installation of composer, you will see "vendor" and "composer.phar" directory and file (PHP Archive).


## Installing

Please follow the instruction to use this with opencart platform.

1) Put app and src folder and composer.json file on your installation root

2) Open your command/terminal and go to your desired installation directory

3) To download run command [composer install] and if you have made any changes to in your composer.json file for version then run command [composer update]

4) After the composer is installed successfully, now you can use the console commands by following below instruction.

### Opencart Installer
This console command is used to install the opencart on your system

#### How to use
By using this console app you can create Opencart setup for the 2.x.x.x,3.x.x.x versions.
- Step 1: Execute the following command on console
    - Syntax:  app/console setup:install-opencart
- Step 2: Enter required options.
    - Syntax: --db_hostname=localhost --db_username=root --db_password=root --db_database=console --db_port=80 --db_prefix=oc_ --username=admin --password=admin --email=johndoe@console.com  --http_server=http://127.0.0.1/opencart-install/ --oc_version=2.3.0.2 --destination=/path/of-the-your/opencart-setup
- Step 3: If all the options are valid then required opencart will be downloaded and installed.
- Step 4: You will get your store and admin url with success message.

### Module genrator

This console command is used to create skeleton of the basic module file structure on admin and catalog as well.

#### How to use
 run command [app/console generate:module] and answer the questions and at last your extension will be ready, which you can see at your admin side and now you can start writing your logic for particular extension.

### Database import/export

This console command is used to export/import sql from/on the opencart database.

### How to use

 #### For Show all list of opencart table

 command : php app/console app:oc-sql oc_tables

 #### Export all table

 command  : php app/console app:oc-sql export all

 #### Export single table

 command  : php app/console app:oc-sql export table_name

 #### Import table

 command  : php app/console app:oc-sql import table_name

 Note : For import table we put the sql file inside the sql_import folder

 and export command file will be in sql_export folder

### Opencart cache clear

 This console command is used to clear the cache of your project using the command mentioned below.

 Run the command
   - app/console clearcache

### Opencart create dummy data

 This console command is used to create dummy data for Customer, Order, Product, Category.

### How to use

 ## for create dummy product
 command : php app/console app:dummy-data create-product

 ## for create dummy customer
 command  : php app/console app:dummy-data create-customer

 ## for create dummy category
 command  : php app/console app:dummy-data create-category

 ## for create dummy order
 command  : php app/console app:dummy-data create-order

## Deployment
This project is still under process. There are some command need to add.


## Versioning
1.0.0.0
.

## Author
* **Webkul Software Private Limited (https://webkul.com)** -

## License

This project is licensed under the *Webkul Software Private Limited* License - see the [LICENSE.md](https://store.webkul.com/license.html) file for details

## Acknowledgments

* This project is using symfony bundle to create commands (https://github.com/symfony/console)
