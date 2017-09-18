# How to Test
Use PHPUnit to perform unit and integration tests

### Setup Test Environment
```sh
$ sudo apt-get install mysql-server
$ sudo apt-get install php libapache2-mod-php php-mcrypt php-mysql
$ cat sql_setup.sql | mysql -u root -p
```

### Install PHPUnit
```sh
$ wget https://phar.phpunit.de/phpunit.phar
$ chmod +x phpunit.phar
$ sudo mv phpunit.phar /usr/local/bin/phpunit
$ phpunit --version
```

### Perform Tests
```sh
phpunit --bootstrap bootstrap.php IndexServerTest.php
phpunit --bootstrap bootstrap.php PackageIndexerTest.php
phpunit --bootstrap bootstrap.php IntegrationTest.php
```



