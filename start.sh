service mysql start
cat /ocean/sql_setup.sql | mysql -u root -ptest
php /ocean/server.php
