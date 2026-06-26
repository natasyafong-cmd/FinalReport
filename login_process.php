<?php

session_start();

include("db.php");

$email=$_POST['email'];
$password=$_POST['password'];

$sql="SELECT *
FROM users
WHERE email='$email'";

$result=mysqli_query($conn,$sql);

if(mysqli_num_rows($result)>0){

$user=mysqli_fetch_assoc($result);

if(
password_verify(
$password,
$user['password']
)
){

$_SESSION['user_id']
=
$user['id'];

$_SESSION['role']
=
$user['role'];

$_SESSION['fullname']
=
$user['fullname'];

if($user['role']=="admin"){

header(
"Location: admin/dashboard.php"
);

}
elseif(
$user['role']=="helper"
){

header(
"Location: helper/dashboard.php"
);

}
else{

header(
"Location: user/dashboard.php"
);

}

}else{

echo "Wrong Password";

}

}else{

echo "Email Not Found";

}

?>