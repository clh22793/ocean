# How to Test
Use PHPUnit to perform unit and integration tests

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




