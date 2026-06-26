<?php

include("auth.php");
include("db.php");

$user_id=$_SESSION['user_id'];

$query=mysqli_query(
$conn,
"SELECT * FROM users
WHERE id='$user_id'"
);

$user=mysqli_fetch_assoc($query);

if(isset($_POST['upload'])){

$file=time()."_".$_FILES['image']['name'];

$tmp=$_FILES['image']['tmp_name'];

move_uploaded_file(
$tmp,
"uploads/".$file
);

mysqli_query(
$conn,
"UPDATE users
SET profile_image='$file'
WHERE id='$user_id'"
);

header("Location: upload_profile.php");

}

?>

<!DOCTYPE html>
<html>
<head>

<title>Profile Picture</title>

<link rel="stylesheet"
href="css/style.css">

</head>
<body>

<div class="container">

<div class="card">

<h2>My Profile Picture</h2>

<img
src="uploads/<?php echo $user['profile_image']; ?>"
width="150"
height="150"
style="border-radius:50%;object-fit:cover;">

<br><br>

<form
method="POST"
enctype="multipart/form-data">

<input
type="file"
name="image"
required>

<button
class="btn"
name="upload">

Upload

</button>

</form>

</div>

</div>

</body>
</html>


