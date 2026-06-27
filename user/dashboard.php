<?php
include("../auth.php");
include("../db.php");

$user_id = $_SESSION['user_id'];

// Fetch user info
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

// Fetch task counts
$total_row   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE user_id='$user_id'"));
$open_row    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE user_id='$user_id' AND status='Open'"));
$progress_row= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE user_id='$user_id' AND status='In Progress'"));
$done_row    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE user_id='$user_id' AND status='Completed'"));

$total_tasks    = $total_row['c'];
$open_tasks     = $open_row['c'];
$progress_tasks = $progress_row['c'];
$done_tasks     = $done_row['c'];

// Fetch 3 most recent tasks for preview
$recent_result = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id='$user_id' ORDER BY id DESC LIMIT 3");

// Build avatar initials from fullname
$name_parts = explode(' ', trim($user['fullname']));
$initials = strtoupper(substr($name_parts[0], 0, 1));
if (count($name_parts) > 1) $initials .= strtoupper(substr(end($name_parts), 0, 1));

// Status badge helper
function status_badge($status) {
    $map = [
        'Open'        => ['class' => 'b-open',      'label' => 'Open'],
        'In Progress' => ['class' => 'b-progress',  'label' => 'In Progress'],
        'Completed'   => ['class' => 'b-completed', 'label' => 'Completed'],
    ];
    $s = $map[$status] ?? ['class' => 'b-open', 'label' => htmlspecialchars($status)];
    return '<span class="ep-status-badge ' . $s['class'] . '">' . $s['label'] . '</span>';
}

// Status dot color
function status_dot($status) {
    $map = [
        'Open'        => '#FF8FAB',
        'In Progress' => '#F9A05C',
        'Completed'   => '#63A022',
    ];
    return $map[$status] ?? '#C9A0B0';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — ErrandPal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Nunito', sans-serif;
      background: linear-gradient(145deg, #FFF5F7 0%, #FFD6E0 55%, #F3E8FF 100%);
      min-height: 100vh;
      padding: 24px 16px;
    }

    /* Blobs */
    .blob { position: fixed; border-radius: 50%; filter: blur(60px); opacity: .3; pointer-events: none; z-index: 0; }
    .blob-1 { width: 340px; height: 340px; background: #FFB8CC; top: -80px; right: -80px; }
    .blob-2 { width: 220px; height: 220px; background: #D4A8F0; bottom: 40px; left: -60px; }

    /* Nav */
    .ep-nav {
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 20px;
      padding: 14px 24px; margin-bottom: 24px; position: relative; z-index: 2;
    }
    .ep-logo { font-family: 'Pacifico', cursive; font-size: 1.4rem; color: #3D2B35; text-decoration: none; }
    .ep-logo span { color: #FF8FAB; }
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
      background: rgba(255,255,255,.7); border: 1.5px solid #FFB8CC;
      color: #C9748A; border-radius: 999px; padding: 7px 16px;
      font-family: 'Nunito', sans-serif; font-size: 0.8rem; font-weight: 800;
      cursor: pointer; transition: all .2s; text-decoration: none;
    }
    .ep-logout-btn:hover { background: #FFE8EF; color: #E0607E; }

    /* Layout */
    .ep-content { position: relative; z-index: 1; max-width: 840px; margin: 0 auto; }

    /* Welcome card */
    .ep-welcome-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 28px;
      padding: 28px 32px; margin-bottom: 20px;
      display: flex; align-items: center; gap: 24px;
    }
    .ep-profile-wrap {
      width: 80px; height: 80px; border-radius: 50%;
      background: linear-gradient(135deg, #FFB8CC, #D4A8F0);
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; border: 3px solid #FFD6E0; flex-shrink: 0; overflow: hidden;
    }
    .ep-profile-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .ep-welcome-text h2 { font-size: 1.5rem; color: #3D2B35; font-weight: 800; margin-bottom: 4px; }
    .ep-welcome-text p { color: #9E7A88; font-size: 0.9rem; font-weight: 600; }
    .ep-role-pill {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 4px 12px; border-radius: 999px; font-size: 0.72rem; font-weight: 800;
      background: #FFE8EF; color: #E0607E; border: 1.5px solid #FFBDD0; margin-top: 8px;
    }

    /* Stat cards */
    .ep-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px; }
    .ep-stat-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 20px;
      padding: 20px 18px; text-align: center; transition: transform .2s;
    }
    .ep-stat-card:hover { transform: translateY(-4px); }
    .ep-stat-icon { font-size: 1.4rem; margin-bottom: 6px; }
    .ep-stat-num { font-size: 2rem; font-weight: 800; color: #3D2B35; line-height: 1; }
    .ep-stat-label { font-size: 0.72rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .08em; margin-top: 4px; }

    /* Action buttons */
    .ep-actions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
    .ep-action-btn {
      display: flex; align-items: center; justify-content: center; gap: 10px;
      padding: 18px 24px; border-radius: 20px; font-family: 'Nunito', sans-serif;
      font-size: 1rem; font-weight: 800; text-decoration: none; border: none;
      cursor: pointer; transition: all .2s cubic-bezier(.34,1.56,.64,1);
    }
    .ep-action-btn:hover { text-decoration: none; }
    .ep-btn-primary {
      background: linear-gradient(135deg, #FF8FAB, #C9748A); color: #fff;
      box-shadow: 0 6px 20px rgba(255,143,171,.4);
    }
    .ep-btn-primary:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 10px 28px rgba(255,143,171,.55); color: #fff; }
    .ep-btn-secondary {
      background: rgba(255,255,255,.82); border: 1.5px solid #FFB8CC;
      color: #C9748A; backdrop-filter: blur(10px);
    }
    .ep-btn-secondary:hover { background: #FFE8EF; border-color: #FF8FAB; color: #E0607E; transform: translateY(-3px); }
    .ep-btn-purple {
      background: rgba(255,255,255,.82); border: 1.5px solid #D4B8F0;
      color: #9C6FD6; backdrop-filter: blur(10px);
    }
    .ep-btn-purple:hover { background: #F3E8FF; border-color: #B589D6; color: #7A4DB8; transform: translateY(-3px); }

    /* Recent tasks */
    .ep-recent-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 24px; padding: 24px;
    }
    .ep-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .ep-section-title { font-size: 0.72rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .1em; }
    .ep-view-all { font-size: 0.78rem; font-weight: 800; color: #FF8FAB; text-decoration: none; }
    .ep-view-all:hover { color: #E0607E; text-decoration: underline; }
    .ep-task-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 0; border-bottom: 1px solid #FFF0F4;
    }
    .ep-task-row:last-child { border-bottom: none; padding-bottom: 0; }
    .ep-task-info { display: flex; align-items: center; gap: 12px; }
    .ep-task-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .ep-task-title { font-size: 0.9rem; font-weight: 700; color: #3D2B35; }
    .ep-task-meta { font-size: 0.72rem; color: #9E7A88; font-weight: 600; margin-top: 2px; }
    .ep-status-badge { padding: 4px 12px; border-radius: 999px; font-size: 0.7rem; font-weight: 800; }
    .b-open       { background: #FFE8EF; color: #E0607E; }
    .b-progress   { background: #FFF0E8; color: #C8682A; }
    .b-completed  { background: #EAF3DE; color: #3B6D11; }
    .ep-empty { text-align: center; padding: 32px; color: #C9A0B0; font-weight: 700; font-size: 0.9rem; }

    @media (max-width: 600px) {
      .ep-welcome-card { flex-direction: column; text-align: center; padding: 24px 20px; }
      .ep-stats-grid { grid-template-columns: 1fr 1fr; }
      .ep-stats-grid .ep-stat-card:last-child { grid-column: span 2; }
      .ep-actions-grid { grid-template-columns: 1fr; }
    }

    @media (prefers-reduced-motion: reduce) {
      .ep-stat-card, .ep-action-btn { transition: none; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <nav class="ep-nav">
    <a href="dashboard.php" class="ep-logo">Errand<span>Pal</span></a>
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

    <!-- Welcome -->
    <div class="ep-welcome-card">
      <div class="ep-profile-wrap">
        <?php if (!empty($user['profile_image'])): ?>
          <img src="../uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile photo">
        <?php else: ?>
          🐻
        <?php endif; ?>
      </div>
      <div class="ep-welcome-text">
        <h2>Welcome back, <?php echo htmlspecialchars($user['fullname']); ?>! 🌸</h2>
        <p>Here's what's going on with your errands today.</p>
        <span class="ep-role-pill">🙋 User Account</span>
      </div>
    </div>

    <!-- Stats -->
    <div class="ep-stats-grid">
      <div class="ep-stat-card">
        <div class="ep-stat-icon">📋</div>
        <div class="ep-stat-num"><?php echo $total_tasks; ?></div>
        <div class="ep-stat-label">Total Tasks</div>
      </div>
      <div class="ep-stat-card">
        <div class="ep-stat-icon">⏳</div>
        <div class="ep-stat-num"><?php echo $progress_tasks; ?></div>
        <div class="ep-stat-label">In Progress</div>
      </div>
      <div class="ep-stat-card">
        <div class="ep-stat-icon">✅</div>
        <div class="ep-stat-num"><?php echo $done_tasks; ?></div>
        <div class="ep-stat-label">Completed</div>
      </div>
    </div>

    <!-- Actions -->
    <div class="ep-actions-grid">
      <a href="post_task.php" class="ep-action-btn ep-btn-primary">✨ Post a new task</a>
      <a href="my_tasks.php"  class="ep-action-btn ep-btn-secondary">📋 View my tasks</a>
      <a href="../upload_profile.php" class="ep-action-btn ep-btn-purple">📸 Update picture</a>
      <a href="../chat.php"   class="ep-action-btn ep-btn-secondary">💬 My chats</a>
    </div>

    <!-- Recent tasks -->
    <div class="ep-recent-card">
      <div class="ep-section-header">
        <div class="ep-section-title">Recent tasks</div>
        <a href="my_tasks.php" class="ep-view-all">View all →</a>
      </div>

      <?php if (mysqli_num_rows($recent_result) === 0): ?>
        <div class="ep-empty">No tasks yet — post your first errand! 🌸</div>
      <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
          <div class="ep-task-row">
            <div class="ep-task-info">
              <div class="ep-task-dot" style="background:<?php echo status_dot($row['status']); ?>"></div>
              <div>
                <div class="ep-task-title"><?php echo htmlspecialchars($row['title']); ?></div>
                <div class="ep-task-meta">Budget: $<?php echo number_format($row['budget'], 2); ?></div>
              </div>
            </div>
            <?php echo status_badge($row['status']); ?>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>

  </div>
</body>
</html>