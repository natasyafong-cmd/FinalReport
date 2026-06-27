<?php
include("auth.php");
include("db.php");

$task_id   = (int)$_POST['task_id'];
$sender_id = (int)$_SESSION['user_id'];
$message   = trim($_POST['message']);

// Only insert if message is not empty
if (!empty($message)) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO messages (task_id, sender_id, message) VALUES (?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iis", $task_id, $sender_id, $message);
    mysqli_stmt_execute($stmt);
}

header("Location: chat.php?task_id=" . $task_id);
exit;