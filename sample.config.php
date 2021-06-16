<?php

/* Change the database credentials to your appropriate credentials if cloning this repository then rename the file to config.php instead of sample.config.php */

/* Database credentials: */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'database_username');
define('DB_PASSWORD', 'database_password');
define('DB_NAME', 'database_name');
 
/* Attempt to connect to MySQL database */
$conn = new mysqli(
  DB_SERVER,
  DB_USERNAME,
  DB_PASSWORD,
  DB_NAME
);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

?>