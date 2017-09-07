<?php
/* Misc Variables */
define ('DS', DIRECTORY_SEPARATOR);
// Path to pdf folder on server
define ('PDF_URL', '/api/pdf/');

/* Security Variables */
define ('SECURITY_KEY', 'A38B6DD58F2700AA4ED202235C6C9F9FB3816CE20C7266F91DD0A4CE7239D516');
define ('TOKEN_EXPIRATION_TIME', 300); // Time in seconds


/* DB Variables */
define ('DB_HOSTNAME', 'localhost');
define ('DB_USERNAME', 'usr');
define ('DB_PASSWORD', 'pwd');
define ('DB_PORT', 3306);
define ('DB_DATABASE', 'token'); // Change DB in the SQL file if you change this

/* Load Classes */
// Composer autoload
include_once('vendor'. DS . 'autoload.php');
// Local class files
include_once('Database.php');
include_once('Response.php');
