<?php

$host="sql202.infinityfree.com";
$user="if0_42277886";
$pass="aKPspvQ49w";
$dbname="if0_42277886_errandpal";

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
