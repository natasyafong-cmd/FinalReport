<?php
include("../auth.php");
include("../db.php");

$user_query  = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$user        = mysqli_fetch_assoc($user_query);

$task_query  = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks");
$task        = mysqli_fetch_assoc($task_query);

$pending_query  = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status='Pending'");
$pending        = mysqli_fetch_assoc($pending_query);

$progress_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status='In Progress'");
$progress       = mysqli_fetch_assoc($progress_query);

$complete_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status='Completed'");
$complete       = mysqli_fetch_assoc($complete_query);

// 5 most recent tasks
$recent_result = mysqli_query($conn, "SELECT t.*, u.fullname FROM tasks t LEFT JOIN users u ON t.user_id=u.id ORDER BY t.id DESC LIMIT 5");

function admin_badge($status) {
    $map = [
        'Pending'     => ['b-pending',  'Pending'],
        'Accepted'    => ['b-open',     'Accepted'],
        'In Progress' => ['b-progress', 'In Progress'],
        'Completed'   => ['b-done',     'Completed'],
    ];
    $s = $map[$status] ?? ['b-pending', htmlspecialchars($status)];
    return '<span class="ep-badge ' . $s[0] . '">' . $s[1] . '</span>';
}

function status_dot($status) {
    return match($status) {
        'Pending'     => '#C9A0B0',
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
  <title>Admin Dashboard — ErrandPal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Nunito', sans-serif;
      background: linear-gradient(145deg, #FFF5F7 0%, #FFD6E0 55%, #F3E8FF 100%);
      min-height: 100vh;
      padding: 24px 16px 48px;
    }

    /* ── Blobs ── */
    .blob { position: fixed; border-radius: 50%; filter: blur(60px); opacity: .3; pointer-events: none; z-index: 0; }
    .blob-1 { width: 340px; height: 340px; background: #FFB8CC; top: -80px; right: -80px; }
    .blob-2 { width: 220px; height: 220px; background: #D4A8F0; bottom: 40px; left: -60px; }
    .blob-3 { width: 180px; height: 180px; background: #FFD6A5; bottom: 160px; right: 60px; }

    /* ── Nav ── */
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
      padding: 6px 14px; border-radius: 999px; border: 1.5px solid transparent;
      transition: all .15s;
    }
    .ep-nav-links a:hover,
    .ep-nav-links a.active { background: #FFE8EF; color: #C9748A; border-color: #FFB8CC; }
    .ep-nav-right { display: flex; align-items: center; gap: 10px; }
    .ep-admin-pill {
      display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px;
      border-radius: 999px; font-size: 0.75rem; font-weight: 800;
      background: linear-gradient(135deg,#FF8FAB,#C9748A); color: #fff;
      border: none; letter-spacing: .02em;
    }
    .ep-logout-btn {
      background: rgba(255,255,255,.7); border: 1.5px solid #FFB8CC;
      color: #C9748A; border-radius: 999px; padding: 7px 16px;
      font-family: 'Nunito', sans-serif; font-size: 0.8rem; font-weight: 800;
      cursor: pointer; text-decoration: none; transition: all .2s;
    }
    .ep-logout-btn:hover { background: #FFE8EF; color: #E0607E; text-decoration: none; }

    /* ── Layout ── */
    .ep-content { position: relative; z-index: 1; max-width: 960px; margin: 0 auto; }

    /* ── Welcome banner ── */
    .ep-welcome-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 28px;
      padding: 28px 32px; margin-bottom: 20px;
      display: flex; align-items: center; gap: 24px;
    }
    .ep-shield-wrap {
      width: 80px; height: 80px; border-radius: 50%;
      background: linear-gradient(135deg, #FFB8CC, #D4A8F0);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.2rem; border: 3px solid #FFD6E0; flex-shrink: 0;
    }
    .ep-welcome-text h2 { font-size: 1.5rem; color: #3D2B35; font-weight: 800; margin-bottom: 4px; }
    .ep-welcome-text p  { color: #9E7A88; font-size: 0.9rem; font-weight: 600; }
    .ep-role-pill {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 4px 12px; border-radius: 999px; font-size: 0.72rem; font-weight: 800;
      background: #F3E8FF; color: #9C6FD6; border: 1.5px solid #D4B8F0; margin-top: 8px;
    }

    /* ── Stats grid ── */
    .ep-stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 20px; }
    .ep-stat-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 20px;
      padding: 20px 14px; text-align: center; transition: transform .2s;
    }
    .ep-stat-card:hover { transform: translateY(-4px); }
    .ep-stat-icon  { font-size: 1.35rem; margin-bottom: 6px; }
    .ep-stat-num   { font-size: 1.9rem; font-weight: 800; color: #3D2B35; line-height: 1; }
    .ep-stat-label { font-size: 0.66rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .08em; margin-top: 4px; }

    /* ── Two-column lower section ── */
    .ep-lower { display: grid; grid-template-columns: 1fr 340px; gap: 20px; margin-bottom: 20px; }

    /* ── Recent tasks ── */
    .ep-recent-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 24px; padding: 24px;
    }
    .ep-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .ep-section-title  { font-size: 0.72rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .1em; }
    .ep-view-all       { font-size: 0.78rem; font-weight: 800; color: #FF8FAB; text-decoration: none; }
    .ep-view-all:hover { color: #E0607E; text-decoration: underline; }

    .ep-task-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 0; border-bottom: 1px solid #FFF0F4;
    }
    .ep-task-row:last-child { border-bottom: none; padding-bottom: 0; }
    .ep-task-info  { display: flex; align-items: center; gap: 12px; }
    .ep-task-dot   { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .ep-task-title { font-size: 0.88rem; font-weight: 700; color: #3D2B35; }
    .ep-task-meta  { font-size: 0.7rem; color: #9E7A88; font-weight: 600; margin-top: 2px; }

    .ep-badge { padding: 4px 12px; border-radius: 999px; font-size: 0.68rem; font-weight: 800; white-space: nowrap; }
    .b-pending  { background: #F3E8FF; color: #9C6FD6; }
    .b-open     { background: #FFE8EF; color: #E0607E; }
    .b-progress { background: #FFF0E8; color: #C8682A; }
    .b-done     { background: #EAF3DE; color: #3B6D11; }

    .ep-empty { text-align: center; padding: 32px; color: #C9A0B0; font-weight: 700; font-size: 0.9rem; }

    /* ── Chart card ── */
    .ep-chart-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 24px;
      padding: 24px; display: flex; flex-direction: column;
    }
    .ep-chart-wrap { flex: 1; display: flex; align-items: center; justify-content: center; margin-top: 12px; }

    /* ── Action buttons ── */
    .ep-actions-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .ep-action-btn {
      display: flex; align-items: center; justify-content: center; gap: 10px;
      padding: 16px 20px; border-radius: 20px; font-family: 'Nunito', sans-serif;
      font-size: 0.95rem; font-weight: 800; text-decoration: none; border: none;
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

    /* ── Responsive ── */
    @media (max-width: 860px) {
      .ep-stats-grid { grid-template-columns: repeat(3, 1fr); }
      .ep-stats-grid .ep-stat-card:nth-child(4),
      .ep-stats-grid .ep-stat-card:nth-child(5) { grid-column: span 1; }
      .ep-lower { grid-template-columns: 1fr; }
      .ep-actions-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
      .ep-welcome-card { flex-direction: column; text-align: center; padding: 24px 20px; }
      .ep-stats-grid   { grid-template-columns: 1fr 1fr; }
      .ep-actions-grid { grid-template-columns: 1fr; }
      .ep-nav-links    { display: none; }
    }
    @media (prefers-reduced-motion: reduce) {
      .ep-stat-card, .ep-action-btn { transition: none; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <!-- Nav -->
  <nav class="ep-nav">
    <a href="dashboard.php" class="ep-logo">Errand<span>Pal</span></a>
    <div class="ep-nav-links">
      <a href="dashboard.php"    class="active">🛡 Overview</a>
      <a href="manage_users.php">👥 Users</a>
      <a href="manage_tasks.php">📋 Tasks</a>
      <a href="generate_report.php">📄 Reports</a>
    </div>
    <div class="ep-nav-right">
      <span class="ep-admin-pill">🛡 Admin</span>
      <a href="../logout.php" class="ep-logout-btn">Logout</a>
    </div>
  </nav>

  <div class="ep-content">

    <!-- Welcome banner -->
    <div class="ep-welcome-card">
      <div class="ep-shield-wrap">🛡</div>
      <div class="ep-welcome-text">
        <h2>Admin Dashboard 🌸</h2>
        <p>Here's a live overview of everything happening on ErrandPal.</p>
        <span class="ep-role-pill">🛡 Administrator</span>
      </div>
    </div>

    <!-- Stats -->
    <div class="ep-stats-grid">
      <div class="ep-stat-card">
        <div class="ep-stat-icon">👥</div>
        <div class="ep-stat-num"><?php echo $user['total']; ?></div>
        <div class="ep-stat-label">Total Users</div>
      </div>
      <div class="ep-stat-card">
        <div class="ep-stat-icon">🛍</div>
        <div class="ep-stat-num"><?php echo $task['total']; ?></div>
        <div class="ep-stat-label">Total Tasks</div>
      </div>
      <div class="ep-stat-card">
        <div class="ep-stat-icon">⏰</div>
        <div class="ep-stat-num"><?php echo $pending['total']; ?></div>
        <div class="ep-stat-label">Pending</div>
      </div>
      <div class="ep-stat-card">
        <div class="ep-stat-icon">⚡</div>
        <div class="ep-stat-num"><?php echo $progress['total']; ?></div>
        <div class="ep-stat-label">In Progress</div>
      </div>
      <div class="ep-stat-card">
        <div class="ep-stat-icon">🎉</div>
        <div class="ep-stat-num"><?php echo $complete['total']; ?></div>
        <div class="ep-stat-label">Completed</div>
      </div>
    </div>

    <!-- Recent tasks + Chart -->
    <div class="ep-lower">

      <!-- Recent tasks -->
      <div class="ep-recent-card">
        <div class="ep-section-header">
          <div class="ep-section-title">Recent tasks</div>
          <a href="manage_tasks.php" class="ep-view-all">View all →</a>
        </div>

        <?php if (mysqli_num_rows($recent_result) === 0): ?>
          <div class="ep-empty">No tasks yet — the platform is fresh! 🌸</div>
        <?php else: ?>
          <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
            <div class="ep-task-row">
              <div class="ep-task-info">
                <div class="ep-task-dot" style="background:<?php echo status_dot($row['status']); ?>"></div>
                <div>
                  <div class="ep-task-title"><?php echo htmlspecialchars($row['title']); ?></div>
                  <div class="ep-task-meta">by <?php echo htmlspecialchars($row['fullname'] ?? 'Unknown'); ?></div>
                </div>
              </div>
              <?php echo admin_badge($row['status']); ?>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>

      <!-- Donut chart -->
      <div class="ep-chart-card">
        <div class="ep-section-header">
          <div class="ep-section-title">Task breakdown</div>
        </div>
        <div class="ep-chart-wrap">
          <canvas id="taskChart" style="max-height:240px;"></canvas>
        </div>
      </div>

    </div>

    <!-- Quick actions -->
    <div class="ep-actions-grid">
      <a href="manage_users.php"    class="ep-action-btn ep-btn-primary">👥 Manage Users</a>
      <a href="manage_tasks.php"    class="ep-action-btn ep-btn-secondary">📋 Manage Tasks</a>
      <a href="generate_report.php" class="ep-action-btn ep-btn-purple">📄 Export PDF</a>
    </div>

  </div>

  <script>
    const ctx = document.getElementById('taskChart');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Pending', 'In Progress', 'Completed'],
        datasets: [{
          data: [
            <?php echo $pending['total']; ?>,
            <?php echo $progress['total']; ?>,
            <?php echo $complete['total']; ?>
          ],
          backgroundColor: ['#F3E8FF', '#FFF0E8', '#EAF3DE'],
          borderColor:     ['#D4B8F0', '#F9C8A0', '#A3CC6C'],
          borderWidth: 2,
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        cutout: '68%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: { family: 'Nunito', weight: '700', size: 12 },
              color: '#9E7A88',
              padding: 16,
              usePointStyle: true,
              pointStyleWidth: 8
            }
          },
          tooltip: {
            bodyFont: { family: 'Nunito', weight: '700' },
            titleFont: { family: 'Nunito', weight: '800' }
          }
        }
      }
    });
  </script>

</body>
</html>
