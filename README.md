# Collaborative Community Review

## Ubuntu 18.04 Installation steps

### Run the following commands to install the necessary apt packages

* Install php and supporting packages 
```
sudo apat-get install php
sudo apt-get install php-intl
sudo apt install  php-sqlite3
```
* Insatll composer [https://getcomposer.org/download/]

### Install the CCR app from git
* Checkout the project from git using the following command at `/var/www/`
```
git clone https://github.com/MESH-Research/CCR.git
``` 
* Run composer update to install all the dependencies 
```
composer install
```

### Install the database for the app
* Use the following commands to install MySQL 
```
sudo apt update
sudo apt install mysql-server
sudo mysql_secure_installation
```

* Connect to the MyQL db as the root user 
```
sudo mysql
```
* Create a db , dbuser and password for the app
```
CREATE USER 'db_user'@'localhost' IDENTIFIED BY 'db_pass';
CREATE DATABASE db_name ;
GRANT ALL PRIVILEGES ON *.* TO 'db_name'@'localhost';
```
You can now either use your machine's webserver to view the default home page, or start
up the built-in webserver with:

```bash
bin/cake server -p 8765
```

Then visit `http://localhost:8765` to see the welcome page.


## CCR front-end application

### Install system dependencies

1.  Yarn
2.  Node
3.  Quasar-Cli

### Install the dependencies

```bash
yarn
```

### Start the app in development mode (hot-code reloading, error reporting, etc.)

```bash
quasar dev
```

### Lint the files

```bash
yarn run lint
```

### Build the app for production

```bash
quasar build
```

### Customize the configuration

See [Configuring quasar.conf.js](https://quasar.dev/quasar-cli/quasar-conf-js).








