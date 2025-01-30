<?php
//config.php

$servername = "localhost";
$dbuser = "root";
$dbpass= "";
$dbname = "churchdb";

$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);

if(!$conn) {
  die("Something went wrong :(");
}
?>