<?php
include("../auth.php");
include("../db.php");

$user_id = $_SESSION['user_id'];

// Active filter
$allowed_filters = ['all', 'open', 'progress', 'completed'];
$filter = isset($_GET['filter']) && in_array($_GET['filter'], $allowed_filters)
    ? $_GET['filter'] : 'all';

// Build WHERE clause based on filter
$status_where = [
    'all'       => "",
    'open'      => "AND status = 'Open'",
    'progress'  => "AND status = 'In Progress'",
    'completed' => "AND status = 'Completed'",
];
$where_extra = $status_where[$filter];

// Fetch tasks
$sql = "SELECT * FROM tasks WHERE user_id='$user_id' $where_extra ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

// Counts for filter labels
function get_count($conn, $user_id, $status = null) {
    $where = $status ? "AND status='$status'" : "";
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE user_id='$user_id' $where"));
    return (int) $r['c'];
}
$count_all      = get_count($conn, $user_id);
$count_open     = get_count($conn, $user_id, 'Open');
$count_progress = get_count($conn, $user_id, 'In Progress');
$count_done     = get_count($conn, $user_id, 'Completed');

// Posted success message
$just_posted = isset($_GET['posted']) && $_GET['posted'] == '1';

// Status helpers
function badge_class($status) {
    return [
        'Open'        => 'b-open',
        'In Progress' => 'b-progress',
        'Completed'   => 'b-completed',
    ][$status] ?? 'b-open';
}
function dot_color($status) {
    return [
        'Open'        => '#FF8FAB',
        'In Progress' => '#F9A05C',
        'Completed'   => '#63A022',
    ][$status] ?? '#C9A0B0';
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
      min-height: 100vh;
      padding: 24px 16px;
    }

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
    .ep-back {
      background: rgba(255,255,255,.7); border: 1.5px solid #FFB8CC;
      color: #C9748A; border-radius: 999px; padding: 7px 16px;
      font-family: 'Nunito', sans-serif; font-size: 0.8rem; font-weight: 800;
      cursor: pointer; transition: all .2s; text-decoration: none;
    }
    .ep-back:hover { background: #FFE8EF; color: #E0607E; }

    /* Content */
    .ep-content { position: relative; z-index: 1; max-width: 860px; margin: 0 auto; }

    /* Top bar */
    .ep-topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .ep-page-title { font-size: 1.5rem; font-weight: 800; color: #3D2B35; }
    .ep-post-btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 10px 20px; border-radius: 999px;
      background: linear-gradient(135deg, #FF8FAB, #C9748A); color: #fff;
      font-family: 'Nunito', sans-serif; font-size: 0.88rem; font-weight: 800;
      text-decoration: none; border: none; cursor: pointer;
      box-shadow: 0 4px 14px rgba(255,143,171,.4); transition: all .2s;
    }
    .ep-post-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,143,171,.55); color: #fff; text-decoration: none; }

    /* Success toast */
    .ep-toast {
      background: #EAF3DE; border: 1.5px solid #9FE1CB; border-radius: 14px;
      padding: 12px 18px; margin-bottom: 18px;
      font-size: 0.88rem; font-weight: 700; color: #0F6E56;
    }

    /* Filter pills */
    .ep-filters { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; }
    .ep-filter {
      padding: 6px 16px; border-radius: 999px; font-size: 0.78rem; font-weight: 800;
      border: 1.5px solid #FFD6E0; background: rgba(255,255,255,.7);
      color: #9E7A88; cursor: pointer; transition: all .15s; text-decoration: none;
    }
    .ep-filter:hover, .ep-filter.active {
      background: #FF8FAB; color: #fff; border-color: #FF8FAB; text-decoration: none;
    }

    /* Task cards */
    .ep-task-card {
      background: rgba(255,255,255,.88); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 22px;
      margin-bottom: 12px; overflow: hidden; transition: transform .2s, box-shadow .2s;
    }
    .ep-task-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,143,171,.2); }

    .ep-card-body { padding: 18px 20px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .ep-card-left { flex: 1; min-width: 0; }

    .ep-card-main { display: flex; align-items: flex-start; gap: 12px; }
    .ep-task-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }

    .ep-task-title { font-size: 1rem; font-weight: 800; color: #3D2B35; margin-bottom: 4px; }
    .ep-task-desc {
      font-size: 0.82rem; color: #9E7A88; font-weight: 600; line-height: 1.4;
      margin-bottom: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 480px;
    }
    .ep-task-meta { display: flex; gap: 14px; flex-wrap: wrap; }
    .ep-meta-item { display: flex; align-items: center; gap: 4px; font-size: 0.75rem; color: #C9A0B0; font-weight: 700; }

    .ep-card-right { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; flex-shrink: 0; }
    .ep-budget { font-size: 1.15rem; font-weight: 800; color: #3D2B35; }

    .ep-status-badge { padding: 5px 14px; border-radius: 999px; font-size: 0.72rem; font-weight: 800; white-space: nowrap; }
    .b-open      { background: #FFE8EF; color: #E0607E; }
    .b-progress  { background: #FFF0E8; color: #C8682A; }
    .b-completed { background: #EAF3DE; color: #3B6D11; }

    /* Card footer actions */
    .ep-card-footer {
      border-top: 1px solid #FFF0F4; padding: 12px 20px;
      display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
    }
    .ep-action-btn {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 7px 16px; border-radius: 999px;
      font-family: 'Nunito', sans-serif; font-size: 0.78rem; font-weight: 800;
      cursor: pointer; border: 1.5px solid; text-decoration: none; transition: all .15s;
    }
    .ep-btn-chat  { background: rgba(255,143,171,.1); color: #C9748A; border-color: #FFBDD0; }
    .ep-btn-chat:hover  { background: #FFE8EF; color: #E0607E; text-decoration: none; }
    .ep-btn-rate  { background: rgba(212,168,240,.12); color: #9C6FD6; border-color: #D4B8F0; }
    .ep-btn-rate:hover  { background: #F3E8FF; color: #7A4DB8; text-decoration: none; }

    /* Category pill */
    .ep-cat-pill {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 3px 10px; border-radius: 999px; font-size: 0.68rem; font-weight: 800;
      background: #F3E8FF; color: #9C6FD6; border: 1.5px solid #D4B8F0;
      margin-left: 4px;
    }

    /* Empty state */
    .ep-empty {
      text-align: center; padding: 56px 24px;
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 24px;
    }
    .ep-empty-bear { font-size: 3rem; margin-bottom: 12px; }
    .ep-empty-title { font-size: 1.1rem; font-weight: 800; color: #3D2B35; margin-bottom: 6px; }
    .ep-empty-text  { color: #C9A0B0; font-weight: 600; font-size: 0.88rem; margin-bottom: 20px; }
    .ep-empty-btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 12px 24px; border-radius: 999px;
      background: linear-gradient(135deg, #FF8FAB, #C9748A); color: #fff;
      font-family: 'Nunito', sans-serif; font-size: 0.9rem; font-weight: 800;
      text-decoration: none; box-shadow: 0 4px 14px rgba(255,143,171,.4); transition: all .2s;
    }
    .ep-empty-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,143,171,.55); color: #fff; text-decoration: none; }

    @media (max-width: 600px) {
      .ep-card-body { flex-direction: column; gap: 10px; }
      .ep-card-right { flex-direction: row; align-items: center; }
      .ep-task-desc { max-width: 100%; }
    }

    @media (prefers-reduced-motion: reduce) {
      .ep-task-card { transition: none; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <nav class="ep-nav">
    <a href="dashboard.php" class="ep-logo">Errand<span>Pal</span></a>
    <a href="dashboard.php" class="ep-back">← Dashboard</a>
  </nav>

  <div class="ep-content">

    <div class="ep-topbar">
      <div class="ep-page-title">My tasks 📋</div>
      <a href="post_task.php" class="ep-post-btn">✨ Post task</a>
    </div>

    <?php if ($just_posted): ?>
      <div class="ep-toast">🎉 Task posted! A helper will pick it up soon.</div>
    <?php endif; ?>

    <!-- Filter tabs -->
    <div class="ep-filters">
      <a href="my_tasks.php?filter=all"      class="ep-filter <?php echo $filter === 'all'       ? 'active' : ''; ?>">All (<?php echo $count_all; ?>)</a>
      <a href="my_tasks.php?filter=open"     class="ep-filter <?php echo $filter === 'open'      ? 'active' : ''; ?>">Open (<?php echo $count_open; ?>)</a>
      <a href="my_tasks.php?filter=progress" class="ep-filter <?php echo $filter === 'progress'  ? 'active' : ''; ?>">In Progress (<?php echo $count_progress; ?>)</a>
      <a href="my_tasks.php?filter=completed"class="ep-filter <?php echo $filter === 'completed' ? 'active' : ''; ?>">Completed (<?php echo $count_done; ?>)</a>
    </div>

    <!-- Task list -->
    <?php if (mysqli_num_rows($result) === 0): ?>
      <div class="ep-empty">
        <div class="ep-empty-bear">🐻</div>
        <div class="ep-empty-title">No tasks here yet</div>
        <div class="ep-empty-text">Post your first errand and a helper will be on their way!</div>
        <a href="post_task.php" class="ep-empty-btn">✨ Post a task</a>
      </div>

    <?php else: ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="ep-task-card">
          <div class="ep-card-body">
            <div class="ep-card-left">
              <div class="ep-card-main">
                <div class="ep-task-dot" style="background:<?php echo dot_color($row['status']); ?>"></div>
                <div>
                  <div class="ep-task-title">
                    <?php echo htmlspecialchars($row['title']); ?>
                    <?php if (!empty($row['category'])): ?>
                      <span class="ep-cat-pill"><?php echo htmlspecialchars($row['category']); ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($row['description'])): ?>
                    <div class="ep-task-desc"><?php echo htmlspecialchars($row['description']); ?></div>
                  <?php endif; ?>
                  <div class="ep-task-meta">
                    <span class="ep-meta-item">💰 $<?php echo number_format($row['budget'], 2); ?></span>
                    <?php if (!empty($row['helper_id'])): ?>
                      <span class="ep-meta-item">🦸 Helper assigned</span>
                    <?php endif; ?>
                    <?php if (!empty($row['deadline'])): ?>
                      <span class="ep-meta-item">📅 Due <?php echo date('M j, Y', strtotime($row['deadline'])); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="ep-card-right">
              <span class="ep-status-badge <?php echo badge_class($row['status']); ?>">
                <?php echo htmlspecialchars($row['status']); ?>
              </span>
            </div>
          </div>

          <div class="ep-card-footer">
            <a class="ep-action-btn ep-btn-chat" href="../chat.php?task_id=<?php echo $row['id']; ?>">
              💬 Chat
            </a>
            <?php if ($row['status'] === 'Completed' && !empty($row['helper_id'])): ?>
              <a class="ep-action-btn ep-btn-rate"
                href="../rate_helper.php?task_id=<?php echo $row['id']; ?>&helper_id=<?php echo $row['helper_id']; ?>">
                ⭐ Rate helper
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</body>
</html>