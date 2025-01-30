<?php
//config.php

$servername = "localhost";
$dbuser = "root";
$dbpass= "Salazar@11#";
$dbname = "churchdb";

$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);

if(!$conn) {
  die("Something went wrong :(");
}
?>
