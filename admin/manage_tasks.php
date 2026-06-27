<?php
include("../auth.php");
include("../db.php");

// FIXED: correct column name 'status' and correct values
$sql = "
    SELECT tasks.*, users.fullname AS customer, helper.fullname AS helper_name,
           ratings.rating, ratings.review
    FROM tasks
    LEFT JOIN users ON tasks.user_id = users.id
    LEFT JOIN users helper ON tasks.helper_id = helper.id
    LEFT JOIN ratings ON tasks.id = ratings.task_id
    ORDER BY tasks.id DESC
";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// FIXED: correct column name 'status' and correct enum values
function task_count($conn, $status = null) {
    $where = $status ? "WHERE status='$status'" : "";
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM tasks $where"));
    return (int)$r['c'];
}
$total    = task_count($conn);
$pending  = task_count($conn, 'Pending');
$accepted = task_count($conn, 'Accepted');
$progress = task_count($conn, 'In Progress');
$done     = task_count($conn, 'Completed');

function badge($status) {
    $map = [
        'Pending'     => 'b-pending',
        'Accepted'    => 'b-accepted',
        'In Progress' => 'b-progress',
        'Completed'   => 'b-completed',
    ];
    $cls = $map[$status] ?? 'b-pending';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars($status) . '</span>';
}

function stars($rating) {
    if (!$rating) return '<span class="muted">—</span>';
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $rating ? '<span class="star filled">★</span>' : '<span class="star">★</span>';
    }
    return $out . ' <span class="rating-num">' . $rating . '/5</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Tasks — ErrandPal</title>
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
    .ep-nav-right { display: flex; gap: 10px; }
    .ep-btn-back {
      background: rgba(255,255,255,.7); border: 1.5px solid #FFB8CC; color: #C9748A;
      border-radius: 999px; padding: 7px 16px; font-family: 'Nunito', sans-serif;
      font-size: 0.8rem; font-weight: 800; text-decoration: none; transition: all .2s;
    }
    .ep-btn-back:hover { background: #FFE8EF; color: #E0607E; }
    .ep-btn-report {
      display: inline-flex; align-items: center; gap: 6px;
      background: linear-gradient(135deg,#FF8FAB,#C9748A); color: #fff;
      border-radius: 999px; padding: 7px 18px; font-family: 'Nunito', sans-serif;
      font-size: 0.8rem; font-weight: 800; text-decoration: none;
      box-shadow: 0 4px 14px rgba(255,143,171,.4); transition: all .2s;
    }
    .ep-btn-report:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,143,171,.55); color: #fff; }

    .ep-content { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; }
    .ep-topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
    .ep-page-title { font-size: 1.5rem; font-weight: 800; color: #3D2B35; }

    /* UPDATED: 5 stat cards to match all 4 statuses + total */
    .ep-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 24px; }
    .ep-stat {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 20px;
      padding: 18px; text-align: center; transition: transform .2s;
    }
    .ep-stat:hover { transform: translateY(-4px); }
    .ep-stat-icon { font-size: 1.4rem; margin-bottom: 4px; }
    .ep-stat-num { font-size: 1.9rem; font-weight: 800; color: #3D2B35; line-height: 1; }
    .ep-stat-label { font-size: 0.7rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .08em; margin-top: 4px; }

    .ep-table-card {
      background: rgba(255,255,255,.88); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 24px;
      overflow: hidden;
    }
    .ep-table-header {
      padding: 20px 24px; border-bottom: 1px solid #FFE8EF;
      display: flex; align-items: center; justify-content: space-between;
    }
    .ep-table-title { font-size: 0.72rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .1em; }
    .ep-table-count { font-size: 0.78rem; font-weight: 800; color: #FF8FAB; }

    .ep-table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 860px; }
    thead tr { background: #3D2B35; }
    thead th {
      padding: 12px 16px; text-align: left; font-size: 0.72rem; font-weight: 800;
      color: #FFD6E0; text-transform: uppercase; letter-spacing: .08em; white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid #FFF0F4; transition: background .15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:nth-child(even) { background: rgba(255,214,224,.15); }
    tbody tr:hover { background: #FFF5F7; }
    td { padding: 13px 16px; font-size: 0.85rem; color: #3D2B35; font-weight: 600; vertical-align: middle; }
    .td-id { color: #C9A0B0; font-size: 0.78rem; font-weight: 800; }
    .td-title { font-weight: 800; color: #3D2B35; max-width: 180px; }
    .td-user { color: #9E7A88; }
    .td-helper { color: #9C6FD6; font-weight: 700; }
    .td-unassigned { color: #C9A0B0; font-style: italic; }
    .td-budget { font-weight: 800; color: #3D2B35; }
    .td-review { max-width: 160px; color: #9E7A88; font-size: 0.8rem; font-style: italic; }
    .muted { color: #C9A0B0; }

    /* FIXED: badge colours for all 4 statuses */
    .badge { padding: 4px 12px; border-radius: 999px; font-size: 0.7rem; font-weight: 800; white-space: nowrap; }
    .b-pending   { background: #F3E8FF; color: #9C6FD6; }
    .b-accepted  { background: #FFE8EF; color: #E0607E; }
    .b-progress  { background: #FFF0E8; color: #C8682A; }
    .b-completed { background: #EAF3DE; color: #3B6D11; }

    .star { color: #FFD6E0; font-size: 0.9rem; }
    .star.filled { color: #FF8FAB; }
    .rating-num { font-size: 0.75rem; font-weight: 800; color: #9E7A88; }

    @media (max-width: 860px) {
      .ep-stats { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 600px) {
      .ep-stats { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <nav class="ep-nav">
    <a href="dashboard.php" class="ep-logo">Errand<span>Pal</span></a>
    <div class="ep-nav-right">
      <a href="generate_report.php" class="ep-btn-report" target="_blank">📄 Export PDF</a>
      <a href="dashboard.php" class="ep-btn-back">← Dashboard</a>
    </div>
  </nav>

  <div class="ep-content">

    <div class="ep-topbar">
      <div class="ep-page-title">Manage Tasks 📋</div>
    </div>

    <!-- UPDATED: 5 cards for all statuses -->
    <div class="ep-stats">
      <div class="ep-stat">
        <div class="ep-stat-icon">📋</div>
        <div class="ep-stat-num"><?php echo $total; ?></div>
        <div class="ep-stat-label">Total Tasks</div>
      </div>
      <div class="ep-stat">
        <div class="ep-stat-icon">⏰</div>
        <div class="ep-stat-num"><?php echo $pending; ?></div>
        <div class="ep-stat-label">Pending</div>
      </div>
      <div class="ep-stat">
        <div class="ep-stat-icon">🟣</div>
        <div class="ep-stat-num"><?php echo $accepted; ?></div>
        <div class="ep-stat-label">Accepted</div>
      </div>
      <div class="ep-stat">
        <div class="ep-stat-icon">⏳</div>
        <div class="ep-stat-num"><?php echo $progress; ?></div>
        <div class="ep-stat-label">In Progress</div>
      </div>
      <div class="ep-stat">
        <div class="ep-stat-icon">✅</div>
        <div class="ep-stat-num"><?php echo $done; ?></div>
        <div class="ep-stat-label">Completed</div>
      </div>
    </div>

    <div class="ep-table-card">
      <div class="ep-table-header">
        <div class="ep-table-title">All tasks</div>
        <div class="ep-table-count"><?php echo $total; ?> records</div>
      </div>
      <div class="ep-table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Customer</th>
              <th>Helper</th>
              <th>Status</th>
              <th>Budget</th>
              <th>Rating</th>
              <th>Review</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td class="td-id"><?php echo $row['id']; ?></td>
              <td class="td-title"><?php echo htmlspecialchars($row['title']); ?></td>
              <td class="td-user"><?php echo htmlspecialchars($row['customer'] ?? '—'); ?></td>
              <td>
                <?php if (!empty($row['helper_name'])): ?>
                  <span class="td-helper">🦸 <?php echo htmlspecialchars($row['helper_name']); ?></span>
                <?php else: ?>
                  <span class="td-unassigned">Unassigned</span>
                <?php endif; ?>
              </td>
              <td><?php echo badge($row['status']); ?></td>
              <td class="td-budget">$<?php echo number_format($row['budget'], 2); ?></td>
              <td><?php echo stars($row['rating']); ?></td>
              <td class="td-review"><?php echo $row['review'] ? htmlspecialchars($row['review']) : '<span class="muted">—</span>'; ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>