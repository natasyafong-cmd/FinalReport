<?php
include("../auth.php");
include("../db.php");

$user_id = $_SESSION['user_id'];

$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

$name_parts = explode(' ', trim($user['fullname']));
$initials = strtoupper(substr($name_parts[0], 0, 1));
if (count($name_parts) > 1) $initials .= strtoupper(substr(end($name_parts), 0, 1));

$keyword = "";
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
}

$stmt = mysqli_prepare($conn, "SELECT * FROM tasks WHERE status = 'Pending' AND title LIKE ? ORDER BY id DESC");
$like = "%" . $keyword . "%";
mysqli_stmt_bind_param($stmt, "s", $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Tasks — ErrandPal</title>
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

    /* Page card */
    .ep-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 24px;
      padding: 28px 32px; margin-bottom: 20px;
    }
    .ep-page-title { font-size: 1.4rem; font-weight: 800; color: #3D2B35; margin-bottom: 20px; }

    /* Search */
    .ep-search-row { display: flex; gap: 10px; margin-bottom: 20px; }
    .ep-search-input {
      flex: 1; padding: 12px 18px; border-radius: 999px;
      border: 1.5px solid #FFD6E0; background: rgba(255,255,255,.9);
      font-family: 'Nunito', sans-serif; font-size: 0.9rem; font-weight: 600;
      color: #3D2B35; outline: none; transition: border-color .15s;
    }
    .ep-search-input:focus { border-color: #FF8FAB; }
    .ep-search-input::placeholder { color: #C9A0B0; }
    .ep-btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 6px;
      padding: 12px 22px; border-radius: 999px; font-family: 'Nunito', sans-serif;
      font-size: 0.9rem; font-weight: 800; cursor: pointer; text-decoration: none;
      border: none; transition: all .2s cubic-bezier(.34,1.56,.64,1);
    }
    .ep-btn-primary {
      background: linear-gradient(135deg, #FF8FAB, #C9748A); color: #fff;
      box-shadow: 0 5px 16px rgba(255,143,171,.4);
    }
    .ep-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,143,171,.5); color: #fff; text-decoration: none; }
    .ep-btn-sm { padding: 8px 16px; font-size: 0.82rem; }

    /* Count pill */
    .ep-count-pill {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 4px 14px; border-radius: 999px; font-size: 0.74rem; font-weight: 800;
      background: #FFE8EF; color: #E0607E; border: 1.5px solid #FFBDD0; margin-bottom: 16px;
    }

    /* Section label */
    .ep-section-title { font-size: 0.72rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 14px; }

    /* Task rows */
    .ep-task-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 14px 0; border-bottom: 1px solid #FFF0F4; gap: 12px;
    }
    .ep-task-row:last-child { border-bottom: none; padding-bottom: 0; }
    .ep-task-left { display: flex; align-items: center; gap: 14px; flex: 1; }
    .ep-task-icon {
      width: 40px; height: 40px; border-radius: 14px; flex-shrink: 0;
      background: #FFE8EF; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .ep-task-title { font-size: 0.92rem; font-weight: 700; color: #3D2B35; margin-bottom: 2px; }
    .ep-task-desc  { font-size: 0.74rem; color: #9E7A88; font-weight: 600; }
    .ep-budget     { font-size: 0.88rem; font-weight: 800; color: #2E7D52; white-space: nowrap; }
    .ep-task-right { display: flex; align-items: center; gap: 14px; flex-shrink: 0; }

    /* Empty */
    .ep-empty { text-align: center; padding: 48px 24px; color: #C9A0B0; }
    .ep-empty-icon { font-size: 2.5rem; margin-bottom: 10px; }
    .ep-empty strong { display: block; font-size: 1rem; color: #9E7A88; margin-bottom: 6px; }
    .ep-empty p { font-size: 0.85rem; font-weight: 600; }

    @media (max-width: 600px) {
      .ep-nav-links { display: none; }
      .ep-card { padding: 20px; }
      .ep-task-right { flex-direction: column; align-items: flex-end; gap: 6px; }
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
      <a href="browse_tasks.php" class="active">🛍 Browse</a>
      <a href="update_status.php">🦸 My Tasks</a>
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

      <div class="ep-page-title">🛍 Browse Tasks</div>

      <form method="GET">
        <div class="ep-search-row">
          <input
            type="text"
            name="keyword"
            class="ep-search-input"
            placeholder="Search for errands…"
            value="<?php echo htmlspecialchars($keyword); ?>">
          <button type="submit" class="ep-btn ep-btn-primary">🔍 Search</button>
        </div>
      </form>

      <?php $count = mysqli_num_rows($result); ?>

      <?php if ($count === 0): ?>
        <div class="ep-empty">
          <div class="ep-empty-icon">🌸</div>
          <strong>No tasks found</strong>
          <p><?php echo $keyword ? 'Try a different search term.' : 'Check back soon — new errands are posted all the time!'; ?></p>
        </div>

      <?php else: ?>
        <div class="ep-count-pill">🌸 <?php echo $count; ?> task<?php echo $count == 1 ? '' : 's'; ?> available</div>
        <div class="ep-section-title">Available Errands</div>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <div class="ep-task-row">
            <div class="ep-task-left">
              <div class="ep-task-icon">📦</div>
              <div>
                <div class="ep-task-title"><?php echo htmlspecialchars($row['title']); ?></div>
                <div class="ep-task-desc"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 72, '…')); ?></div>
              </div>
            </div>
            <div class="ep-task-right">
              <span class="ep-budget">$<?php echo number_format($row['budget'], 2); ?></span>
              <a href="accept_task.php?id=<?php echo (int)$row['id']; ?>"
                 class="ep-btn ep-btn-primary ep-btn-sm"
                 onclick="return confirm('Accept this task?')">
                ✅ Accept
              </a>
            </div>
          </div>
        <?php endwhile; ?>

      <?php endif; ?>
    </div>
  </div>
</body>
</html>