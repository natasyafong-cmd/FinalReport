<?php
ob_start();
include("../auth.php");
include("../db.php");

// DELETE USER (protect admins)
if (isset($_GET['delete'])) {
    $id    = (int) $_GET['delete'];
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='$id'"));
    if ($check && $check['role'] !== 'admin') {
        mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    }
    ob_end_clean();
    header("Location: manage_users.php?deleted=1");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

// Counts
function user_count($conn, $role = null) {
    $where = $role ? "WHERE role='$role'" : "";
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users $where"));
    return (int)$r['c'];
}
$total   = user_count($conn);
$users   = user_count($conn, 'user');
$helpers = user_count($conn, 'helper');
$admins  = user_count($conn, 'admin');

function role_badge($role) {
    $map = [
        'admin'  => ['bg'=>'#3D2B35','color'=>'#FFD6E0'],
        'helper' => ['bg'=>'#F3E8FF','color'=>'#9C6FD6'],
        'user'   => ['bg'=>'#FFE8EF','color'=>'#E0607E'],
    ];
    $s = $map[$role] ?? ['bg'=>'#F5F5F5','color'=>'#999'];
    return '<span style="background:'.$s['bg'].';color:'.$s['color'].';padding:4px 12px;border-radius:999px;font-size:0.7rem;font-weight:800;">'.htmlspecialchars(ucfirst($role)).'</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users — ErrandPal</title>
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
    .ep-btn-back {
      background: rgba(255,255,255,.7); border: 1.5px solid #FFB8CC; color: #C9748A;
      border-radius: 999px; padding: 7px 16px; font-family: 'Nunito', sans-serif;
      font-size: 0.8rem; font-weight: 800; text-decoration: none; transition: all .2s;
    }
    .ep-btn-back:hover { background: #FFE8EF; color: #E0607E; }

    .ep-content { position: relative; z-index: 1; max-width: 1000px; margin: 0 auto; }
    .ep-topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .ep-page-title { font-size: 1.5rem; font-weight: 800; color: #3D2B35; }

    /* Toast */
    .ep-toast {
      background: #EAF3DE; border: 1.5px solid #9FE1CB; border-radius: 14px;
      padding: 12px 18px; margin-bottom: 18px; font-size: 0.88rem; font-weight: 700; color: #0F6E56;
    }

    /* Stats */
    .ep-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 24px; }
    .ep-stat {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 20px;
      padding: 18px; text-align: center; transition: transform .2s;
    }
    .ep-stat:hover { transform: translateY(-4px); }
    .ep-stat-icon { font-size: 1.4rem; margin-bottom: 4px; }
    .ep-stat-num { font-size: 1.9rem; font-weight: 800; color: #3D2B35; line-height: 1; }
    .ep-stat-label { font-size: 0.7rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .08em; margin-top: 4px; }

    /* Table */
    .ep-table-card {
      background: rgba(255,255,255,.88); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 24px; overflow: hidden;
    }
    .ep-table-header {
      padding: 20px 24px; border-bottom: 1px solid #FFE8EF;
      display: flex; align-items: center; justify-content: space-between;
    }
    .ep-table-title { font-size: 0.72rem; font-weight: 800; color: #C9A0B0; text-transform: uppercase; letter-spacing: .1em; }
    .ep-table-count { font-size: 0.78rem; font-weight: 800; color: #FF8FAB; }

    .ep-table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 620px; }
    thead tr { background: #3D2B35; }
    thead th {
      padding: 12px 16px; text-align: left; font-size: 0.72rem; font-weight: 800;
      color: #FFD6E0; text-transform: uppercase; letter-spacing: .08em;
    }
    tbody tr { border-bottom: 1px solid #FFF0F4; transition: background .15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:nth-child(even) { background: rgba(255,214,224,.15); }
    tbody tr:hover { background: #FFF5F7; }
    td { padding: 13px 16px; font-size: 0.85rem; color: #3D2B35; font-weight: 600; vertical-align: middle; }
    .td-id    { color: #C9A0B0; font-size: 0.78rem; font-weight: 800; }
    .td-name  { font-weight: 800; }
    .td-email { color: #9E7A88; font-size: 0.82rem; }

    /* Avatar initials */
    .td-avatar {
      display: inline-flex; align-items: center; gap: 10px;
    }
    .avatar-circle {
      width: 32px; height: 32px; border-radius: 50%;
      background: linear-gradient(135deg,#FF8FAB,#C9748A);
      display: inline-flex; align-items: center; justify-content: center;
      color: #fff; font-size: 0.72rem; font-weight: 800; flex-shrink: 0;
    }

    /* Delete btn */
    .btn-delete {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 6px 14px; border-radius: 999px; font-family: 'Nunito', sans-serif;
      font-size: 0.75rem; font-weight: 800; cursor: pointer; text-decoration: none;
      background: #FFE8EF; color: #E0607E; border: 1.5px solid #FFBDD0; transition: all .15s;
    }
    .btn-delete:hover { background: #E0607E; color: #fff; border-color: #E0607E; }
    .admin-label { color: #C9A0B0; font-size: 0.78rem; font-weight: 800; }

    @media (max-width: 700px) {
      .ep-stats { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <nav class="ep-nav">
    <a href="dashboard.php" class="ep-logo">Errand<span>Pal</span></a>
    <a href="dashboard.php" class="ep-btn-back">← Dashboard</a>
  </nav>

  <div class="ep-content">

    <div class="ep-topbar">
      <div class="ep-page-title">Manage Users 👥</div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
      <div class="ep-toast">🗑 User deleted successfully.</div>
    <?php endif; ?>

    <div class="ep-stats">
      <div class="ep-stat">
        <div class="ep-stat-icon">👥</div>
        <div class="ep-stat-num"><?php echo $total; ?></div>
        <div class="ep-stat-label">Total Users</div>
      </div>
      <div class="ep-stat">
        <div class="ep-stat-icon">🙋</div>
        <div class="ep-stat-num"><?php echo $users; ?></div>
        <div class="ep-stat-label">Users</div>
      </div>
      <div class="ep-stat">
        <div class="ep-stat-icon">🦸</div>
        <div class="ep-stat-num"><?php echo $helpers; ?></div>
        <div class="ep-stat-label">Helpers</div>
      </div>
      <div class="ep-stat">
        <div class="ep-stat-icon">🛡</div>
        <div class="ep-stat-num"><?php echo $admins; ?></div>
        <div class="ep-stat-label">Admins</div>
      </div>
    </div>

    <div class="ep-table-card">
      <div class="ep-table-header">
        <div class="ep-table-title">All users</div>
        <div class="ep-table-count"><?php echo $total; ?> records</div>
      </div>
      <div class="ep-table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)):
              $parts    = explode(' ', trim($row['fullname']));
              $initials = strtoupper(substr($parts[0],0,1));
              if (count($parts) > 1) $initials .= strtoupper(substr(end($parts),0,1));
            ?>
            <tr>
              <td class="td-id"><?php echo $row['id']; ?></td>
              <td>
                <div class="td-avatar">
                  <div class="avatar-circle"><?php echo $initials; ?></div>
                  <span class="td-name"><?php echo htmlspecialchars($row['fullname']); ?></span>
                </div>
              </td>
              <td class="td-email"><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo role_badge($row['role']); ?></td>
              <td>
                <?php if ($row['role'] !== 'admin'): ?>
                  <a class="btn-delete"
                     href="manage_users.php?delete=<?php echo $row['id']; ?>"
                     onclick="return confirm('Delete <?php echo htmlspecialchars($row['fullname']); ?>? This cannot be undone.')">
                    🗑 Delete
                  </a>
                <?php else: ?>
                  <span class="admin-label">Protected</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>