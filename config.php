<?php

$host = "localhost";
$user = "anonymchat";
$password = ""; 
$database = "my_anonymchat";

$connessione = new mysqli($host, $user, $password, $database)
or die ("Impossibile connettersi al server");
?>