<?php

$host="localhost";
$user="root";
$pass="";
$dbname="errandpal";

$conn = new mysqli(
    $host,
    $user,
    $pass,
    $dbname
);

if($conn->connect_error){
    die("Database Failed");
}

?>
