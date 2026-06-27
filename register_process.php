<?php

include("db.php");

$fullname=$_POST['fullname'];
$email=$_POST['email'];
$password=password_hash(
$_POST['password'],
PASSWORD_DEFAULT
);
$role=$_POST['role'];

$sql="INSERT INTO users(
fullname,
email,
password,
role
)

VALUES(
'$fullname',
'$email',
'$password',
'$role'
)";

if(mysqli_query($conn,$sql)){

header("Location: login.php");

}else{

echo "Registration Failed";

}

?>