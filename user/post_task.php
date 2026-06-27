<?php
ob_start();
include("../auth.php");
include("../db.php");

$errors = [];

if (isset($_POST['submit'])) {
    $user_id     = $_SESSION['user_id'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $budget      = trim($_POST['budget']);

    if (empty($title))       $errors[] = 'Task title is required.';
    if (empty($description)) $errors[] = 'Description is required.';
    if (!is_numeric($budget) || $budget <= 0) $errors[] = 'Please enter a valid budget.';

    if (empty($errors)) {
        $title       = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);
        $budget      = (float) $budget;

        $sql = "INSERT INTO tasks (user_id, title, description, budget) VALUES ('$user_id', '$title', '$description', '$budget')";

        if (mysqli_query($conn, $sql)) {
            ob_end_clean();
            header("Location: my_tasks.php?posted=1");
            exit();
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Post Task — ErrandPal</title>
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
    .blob-1 { width: 320px; height: 320px; background: #FFB8CC; top: -80px; right: -80px; }
    .blob-2 { width: 200px; height: 200px; background: #D4A8F0; bottom: 40px; left: -60px; }
    .ep-nav {
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 20px;
      padding: 14px 24px; margin-bottom: 24px; position: relative; z-index: 2;
    }
    .ep-logo { font-family: 'Pacifico', cursive; font-size: 1.4rem; color: #3D2B35; text-decoration: none; }
    .ep-logo span { color: #FF8FAB; }
    .ep-back {
      background: rgba(255,255,255,.7); border: 1.5px solid #FFB8CC; color: #C9748A;
      border-radius: 999px; padding: 7px 16px; font-family: 'Nunito', sans-serif;
      font-size: 0.8rem; font-weight: 800; text-decoration: none; transition: all .2s;
    }
    .ep-back:hover { background: #FFE8EF; color: #E0607E; }
    .ep-center { position: relative; z-index: 1; max-width: 560px; margin: 0 auto; }
    .ep-card {
      background: rgba(255,255,255,.88); backdrop-filter: blur(20px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 28px;
      padding: 36px 32px; box-shadow: 0 16px 48px rgba(255,143,171,.15);
    }
    .ep-card-header { text-align: center; margin-bottom: 28px; }
    .ep-card-bear { font-size: 2.5rem; margin-bottom: 6px; }
    .ep-card-header h2 { font-size: 1.6rem; color: #3D2B35; font-weight: 800; margin-bottom: 6px; }
    .ep-card-header p { color: #9E7A88; font-size: 0.88rem; font-weight: 600; }
    .ep-errors {
      background: #FFE8EF; border: 1.5px solid #FFBDD0; border-radius: 14px;
      padding: 14px 16px; margin-bottom: 20px;
    }
    .ep-errors p { color: #E0607E; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px; }
    .ep-errors p:last-child { margin-bottom: 0; }
    .ep-field { margin-bottom: 20px; }
    .ep-label {
      display: block; font-size: 0.78rem; font-weight: 800; color: #9E7A88;
      text-transform: uppercase; letter-spacing: .08em; margin-bottom: 8px;
    }
    .ep-input, .ep-textarea {
      width: 100%; background: rgba(255,255,255,.7); border: 1.5px solid #FFD6E0;
      border-radius: 14px; padding: 13px 16px; font-family: 'Nunito', sans-serif;
      font-size: 0.95rem; color: #3D2B35; font-weight: 600;
      outline: none; transition: border-color .2s, box-shadow .2s;
    }
    .ep-input:focus, .ep-textarea:focus { border-color: #FF8FAB; box-shadow: 0 0 0 3px rgba(255,143,171,.2); }
    .ep-textarea { resize: vertical; min-height: 120px; line-height: 1.6; }
    .ep-input-wrap { position: relative; }
    .ep-input-wrap .ep-input { padding-left: 32px; }
    .ep-prefix {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: #C9A0B0; font-weight: 800; font-size: 0.95rem; pointer-events: none;
    }
    .ep-tip {
      background: #FFF5F7; border: 1.5px solid #FFD6E0; border-radius: 14px;
      padding: 14px 16px; font-size: 0.8rem; color: #9E7A88; font-weight: 600; line-height: 1.5;
    }
    .ep-tip strong { color: #C9748A; }
    .ep-divider { height: 1px; background: #F5C6D5; margin: 24px 0; }
    .ep-submit {
      width: 100%; padding: 16px; border-radius: 999px;
      background: linear-gradient(135deg, #FF8FAB, #C9748A); color: #fff;
      font-family: 'Nunito', sans-serif; font-size: 1.05rem; font-weight: 800;
      border: none; cursor: pointer; box-shadow: 0 6px 20px rgba(255,143,171,.4);
      transition: all .2s cubic-bezier(.34,1.56,.64,1);
    }
    .ep-submit:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 10px 28px rgba(255,143,171,.55); }
    .ep-submit:active { transform: scale(.98); }
    @media (max-width: 540px) {
      .ep-card { padding: 28px 18px; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <nav class="ep-nav">
    <a href="dashboard.php" class="ep-logo">Errand<span>Pal</span></a>
    <a href="dashboard.php" class="ep-back">← Back to dashboard</a>
  </nav>

  <div class="ep-center">
    <div class="ep-card">

      <div class="ep-card-header">
        <div class="ep-card-bear">🐻</div>
        <h2>Post a new task</h2>
        <p>Describe your errand and a helper will pick it up!</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="ep-errors">
          <?php foreach ($errors as $e): ?>
            <p>⚠️ <?php echo htmlspecialchars($e); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="post_task.php">

        <div class="ep-field">
          <label class="ep-label" for="title">Task title</label>
          <input class="ep-input" type="text" name="title" id="title" required
            placeholder="e.g. Pick up groceries from NTUC"
            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </div>

        <div class="ep-field">
          <label class="ep-label" for="description">Description</label>
          <textarea class="ep-textarea" name="description" id="description" required
            placeholder="Give helpers all the details — where to go, what to get, any special instructions…"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="ep-field">
          <label class="ep-label" for="budget">Budget</label>
          <div class="ep-input-wrap">
            <span class="ep-prefix">$</span>
            <input class="ep-input" type="number" name="budget" id="budget"
              min="1" step="0.01" required placeholder="0.00"
              value="<?php echo htmlspecialchars($_POST['budget'] ?? ''); ?>">
          </div>
        </div>

        <div class="ep-tip">
          <strong>💡 Tip:</strong> Tasks with clear descriptions and fair budgets get picked up faster!
        </div>

        <div class="ep-divider"></div>

        <button type="submit" class="ep-submit" name="submit">✨ Post task</button>

      </form>
    </div>
  </div>

</body>
</html>