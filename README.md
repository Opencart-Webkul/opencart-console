# Webkul Opencart Console Application

## Overview

Webkul Opencart Console Application is used to automate the opencart Installation ,Module Creation,Crearing the cache and Add some dummy data via Console commands.

## Getting Started

Clone this proejct over the directory where your will keep your opencart root setup.

## Prerequisite

Before using this app, you must have Opencart installed( Not for the OpenCart installer console) on your server and composer on your system. If composer is not installed on your system then run the following command-
- Syntax:  curl -s http://getcomposer.org/installer | php
- Syntax:  php composer.phar install
- After successfully installation of composer, you will see "vendor" and "composer.phar" directory and file (PHP Archive).


## Installing

Please follow the instruction to use this with Opencart plateform.

1 ) Put app and src folder and composer.json file on your installation root

2)	Open your command/terminal and go to your desired installation directory

3)	To download run command [composer install] and if you have made any changes to in your composer.json file for version then run command [composer update]

4) 	After the download is done

### Opencart Installer
This console will be used to install the opencart on your system

### How to use
By using this console app you can create Opencart setup for the 2.x.x.x,3.x.x.x versions.
- Step1: Execute the following command on console
    - Syntax:  app/console setup:install-opencart
- Step2: Enter required options.
    - Syntax: --db_hostname=localhost --db_username=root --db_password=root --db_database=console --db_port=80 --db_prefix=oc_ --username=admin --password=admin --email=johndoe@console.com  --http_server=http://127.0.0.1/opencart-install/ --oc_version=2.3.0.2 --destination=/home/users/webkul/www/html/opencart-install
- Step3: If all options are valid so required opencart will be downloaded and installed.
- Step4: You will get your store and admin url with success message

### Module Genrator

This console command will be used to create skeleton of the basic Module file structure on Admin and Catalog as well.

### How to use
 - Step : run command [app/console generate:module] and answer the questions and at last your extension will be ready, which you can see at your admin side and now you can start writing your logic for particular extension

### Database Import/Export

This console command will be used to export/import sql from/on the Opencart Database.

### How to use

 ## For Show all List of Opencart Table

 command : php app/console app:oc-sql oc_tables

 ## Export all table

 command  : php app/console app:oc-sql export all

 ## Export single table

 command  : php app/console app:oc-sql export table_name

 ## Import Table

 command  : php app/console app:oc-sql import table_name


 Note : For import table we put the sql file inside the sql_import folder

 and export command file will show in sql_export folder

### OpenCart Cache Clear

 This console command will be used to clear the cache of your project using the command mentioned below.

Run the command
   app/console clearcache

## Deployment
This project is still under process. there are some commnd need to add.


## Versioning
1.0.0.0
.

## Author
* **Webkul Software Private Limited (https://webkul.com)** -

## License

This project is licensed under the MIT License - see the [LICENSE.md](https://store.webkul.com/license.html) file for details

## Acknowledgments

* This project has been using symfony bundle to create commonds (https://github.com/symfony/console)
