<?php
include("../auth.php");
include("../db.php");

$helper_id = $_SESSION['user_id'];

$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$helper_id'");
$user = mysqli_fetch_assoc($user_query);

$name_parts = explode(' ', trim($user['fullname']));
$initials = strtoupper(substr($name_parts[0], 0, 1));
if (count($name_parts) > 1) $initials .= strtoupper(substr(end($name_parts), 0, 1));

// Handle update
if (isset($_POST['update'])) {
    $task_id = (int)$_POST['task_id'];
    $status  = $_POST['status'];
    $allowed = ['Accepted', 'In Progress', 'Completed'];
    if (in_array($status, $allowed)) {
        $stmt = mysqli_prepare($conn, "UPDATE tasks SET status = ? WHERE id = ? AND helper_id = ?");
        mysqli_stmt_bind_param($stmt, "sii", $status, $task_id, $helper_id);
        mysqli_stmt_execute($stmt);
    }
}

$stmt2 = mysqli_prepare($conn, "SELECT * FROM tasks WHERE helper_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt2, "i", $helper_id);
mysqli_stmt_execute($stmt2);
$result = mysqli_stmt_get_result($stmt2);

function status_badge($s) {
    $map = [
        'Accepted'    => ['b-open',     'Accepted'],
        'In Progress' => ['b-progress', 'In Progress'],
        'Completed'   => ['b-done',     'Completed'],
    ];
    $d = $map[$s] ?? ['b-open', htmlspecialchars($s)];
    return '<span class="ep-badge ' . $d[0] . '">' . $d[1] . '</span>';
}

function status_dot($s) {
    return match($s) {
        'Accepted'    => '#FF8FAB',
        'In Progress' => '#F9A05C',
        'Completed'   => '#63A022',
        default       => '#C9A0B0',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tasks — ErrandPal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Nunito', sans-serif;
      background: linear-gradient(145deg, #FFF5F7 0%, #FFD6E0 55%, #F3E8FF 100%);
      min-height: 100vh; padding: 24px 16px;
    }
    .blob { position: fixed; border-radius: 50%; filter: blur(60px); opacity: .3; pointer-events: none; z-index: 0; }
    .blob-1 { width: 340px; height: 340px; background: #FFB8CC; top: -80px; right: -80px; }
    .blob-2 { width: 220px; height: 220px; background: #D4A8F0; bottom: 40px; left: -60px; }

    .ep-nav {
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 20px;
      padding: 14px 24px; margin-bottom: 24px; position: relative; z-index: 2;
    }
    .ep-logo { font-family: 'Pacifico', cursive; font-size: 1.4rem; color: #3D2B35; text-decoration: none; }
    .ep-logo span { color: #FF8FAB; }
    .ep-nav-links { display: flex; align-items: center; gap: 6px; }
    .ep-nav-links a {
      font-size: 0.8rem; font-weight: 800; color: #9E7A88; text-decoration: none;
      padding: 6px 14px; border-radius: 999px; border: 1.5px solid transparent; transition: all .15s;
    }
    .ep-nav-links a:hover, .ep-nav-links a.active { background: #FFE8EF; color: #C9748A; border-color: #FFB8CC; }
    .ep-nav-right { display: flex; align-items: center; gap: 10px; }
    .ep-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: linear-gradient(135deg, #FF8FAB, #C9748A);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-weight: 800; font-size: 0.85rem;
      border: 2.5px solid #FFD6E0; overflow: hidden; flex-shrink: 0;
    }
    .ep-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .ep-logout-btn {
      background: rgba(255,255,255,.7); border: 1.5px solid #FFB8CC; color: #C9748A;
      border-radius: 999px; padding: 7px 16px; font-family: 'Nunito', sans-serif;
      font-size: 0.8rem; font-weight: 800; cursor: pointer; text-decoration: none; transition: all .2s;
    }
    .ep-logout-btn:hover { background: #FFE8EF; color: #E0607E; }

    .ep-content { position: relative; z-index: 1; max-width: 840px; margin: 0 auto; }

    .ep-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 24px;
      padding: 28px 32px; margin-bottom: 20px;
    }
    .ep-page-title { font-size: 1.4rem; font-weight: 800; color: #3D2B35; margin-bottom: 6px; }
    .ep-page-sub   { font-size: 0.88rem; font-weight: 600; color: #9E7A88; margin-bottom: 24px; }
    .ep-back-link  { font-size: 0.82rem; font-weight: 700; color: #C9A0B0; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 20px; }
    .ep-back-link:hover { color: #C9748A; }
    .ep-section-title { font-size: 0.72rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 14px; }

    /* Task rows */
    .ep-task-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 0; border-bottom: 1px solid #FFF0F4; gap: 16px;
    }
    .ep-task-row:last-child { border-bottom: none; padding-bottom: 0; }
    .ep-task-left { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }
    .ep-task-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .ep-task-title { font-size: 0.92rem; font-weight: 700; color: #3D2B35; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ep-task-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

    /* Badge */
    .ep-badge { padding: 4px 12px; border-radius: 999px; font-size: 0.7rem; font-weight: 800; white-space: nowrap; }
    .b-open     { background: #FFE8EF; color: #E0607E; }
    .b-progress { background: #FFF0E8; color: #C8682A; }
    .b-done     { background: #EAF3DE; color: #3B6D11; }

    /* Inline form */
    .ep-form-inline { display: flex; align-items: center; gap: 8px; }
    .ep-select {
      padding: 7px 12px; border-radius: 10px; border: 1.5px solid #FFD6E0;
      background: rgba(255,255,255,.9); font-family: 'Nunito', sans-serif;
      font-size: 0.8rem; font-weight: 700; color: #3D2B35;
      cursor: pointer; outline: none; appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%239E7A88' stroke-width='1.8' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
      transition: border-color .15s;
    }
    .ep-select:focus { border-color: #FF8FAB; }
    .ep-btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 6px;
      padding: 8px 16px; border-radius: 999px; font-family: 'Nunito', sans-serif;
      font-size: 0.8rem; font-weight: 800; cursor: pointer; text-decoration: none;
      border: none; white-space: nowrap; transition: all .2s cubic-bezier(.34,1.56,.64,1);
    }
    .ep-btn-primary {
      background: linear-gradient(135deg, #FF8FAB, #C9748A); color: #fff;
      box-shadow: 0 4px 12px rgba(255,143,171,.35);
    }
    .ep-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,143,171,.5); color: #fff; text-decoration: none; }
    .ep-btn-outline {
      background: rgba(255,255,255,.8); border: 1.5px solid #FFB8CC; color: #C9748A;
    }
    .ep-btn-outline:hover { background: #FFE8EF; border-color: #FF8FAB; color: #E0607E; transform: translateY(-2px); text-decoration: none; }
    .ep-btn-purple {
      background: rgba(255,255,255,.8); border: 1.5px solid #D4B8F0; color: #9C6FD6;
    }
    .ep-btn-purple:hover { background: #F3E8FF; border-color: #B589D6; color: #7A4DB8; transform: translateY(-2px); text-decoration: none; }

    /* Empty */
    .ep-empty { text-align: center; padding: 48px 24px; color: #C9A0B0; }
    .ep-empty-icon { font-size: 2.5rem; margin-bottom: 10px; }
    .ep-empty strong { display: block; font-size: 1rem; color: #9E7A88; margin-bottom: 6px; }
    .ep-empty p { font-size: 0.85rem; font-weight: 600; margin-bottom: 20px; }

    @media (max-width: 640px) {
      .ep-nav-links { display: none; }
      .ep-card { padding: 20px; }
      .ep-task-row { flex-direction: column; align-items: flex-start; gap: 10px; }
      .ep-task-right { width: 100%; flex-wrap: wrap; }
      .ep-form-inline { flex-wrap: wrap; }
    }
    @media (prefers-reduced-motion: reduce) { .ep-btn { transition: none; } }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <nav class="ep-nav">
    <a href="dashboard.php" class="ep-logo">Errand<span>Pal</span></a>
    <div class="ep-nav-links">
      <a href="dashboard.php">🏠 Home</a>
      <a href="browse_tasks.php">🛍 Browse</a>
      <a href="update_status.php" class="active">🦸 My Tasks</a>
    </div>
    <div class="ep-nav-right">
      <div class="ep-avatar">
        <?php if (!empty($user['profile_image'])): ?>
          <img src="../uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
        <?php else: ?>
          <?php echo $initials; ?>
        <?php endif; ?>
      </div>
      <a href="../logout.php" class="ep-logout-btn">Logout</a>
    </div>
  </nav>

  <div class="ep-content">
    <div class="ep-card">

      <a href="dashboard.php" class="ep-back-link">← Back to Dashboard</a>
      <div class="ep-page-title">🦸 My Accepted Tasks</div>
      <div class="ep-page-sub">Update the status of your active errands below.</div>

      <?php $count = mysqli_num_rows($result); ?>

      <?php if ($count === 0): ?>
        <div class="ep-empty">
          <div class="ep-empty-icon">🌷</div>
          <strong>No tasks yet</strong>
          <p>Browse available errands and accept your first one!</p>
          <a href="browse_tasks.php" class="ep-btn ep-btn-primary">🛍 Browse Tasks</a>
        </div>

      <?php else: ?>
        <div class="ep-section-title">Your Tasks — <?php echo $count; ?> total</div>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <div class="ep-task-row">
            <div class="ep-task-left">
              <div class="ep-task-dot" style="background:<?php echo status_dot($row['status']); ?>"></div>
              <div class="ep-task-title"><?php echo htmlspecialchars($row['title']); ?></div>
            </div>
            <div class="ep-task-right">
              <?php echo status_badge($row['status']); ?>
              <form method="POST" class="ep-form-inline">
                <input type="hidden" name="task_id" value="<?php echo (int)$row['id']; ?>">
                <select name="status" class="ep-select">
                  <?php foreach (['Accepted', 'In Progress', 'Completed'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $row['status'] === $s ? 'selected' : ''; ?>>
                      <?php echo $s; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="update" class="ep-btn ep-btn-primary">✔ Save</button>
              </form>
              <a href="../chat.php?task_id=<?php echo (int)$row['id']; ?>" class="ep-btn ep-btn-outline">💬 Chat</a>
            </div>
          </div>
        <?php endwhile; ?>

      <?php endif; ?>
    </div>
  </div>
</body>
</html>