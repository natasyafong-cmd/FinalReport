<?php
include("../auth.php");
include("../db.php");

$id        = (int)$_GET['id'];
$helper_id = (int)$_SESSION['user_id'];

// Only claim a task that is still Pending — prevents double-acceptance
$stmt = mysqli_prepare($conn,
    "UPDATE tasks SET helper_id = ?, status = 'Accepted' WHERE id = ? AND status = 'Pending'"
);
mysqli_stmt_bind_param($stmt, "ii", $helper_id, $id);
mysqli_stmt_execute($stmt);

header("Location: update_status.php");
exit;
