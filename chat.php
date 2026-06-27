<?php
include("auth.php");
include("db.php");

$task_id   = (int)$_GET['task_id'];
$user_id   = $_SESSION['user_id'];

// Load current user info
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

// Avatar initials
$name_parts = explode(' ', trim($user['fullname']));
$initials = strtoupper(substr($name_parts[0], 0, 1));
if (count($name_parts) > 1) $initials .= strtoupper(substr(end($name_parts), 0, 1));

// Load task info
$task_query = mysqli_query($conn, "SELECT * FROM tasks WHERE id='$task_id'");
$task = mysqli_fetch_assoc($task_query);

// Load messages with sender info
$message_query = mysqli_query($conn,
    "SELECT messages.*, users.fullname, users.profile_image
     FROM messages
     JOIN users ON users.id = messages.sender_id
     WHERE task_id='$task_id'
     ORDER BY id ASC"
);

// Helper: initials from name
function make_initials($name) {
    $parts = explode(' ', trim($name));
    $i = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) $i .= strtoupper(substr(end($parts), 0, 1));
    return $i;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat — ErrandPal</title>
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

    /* Layout */
    .ep-content { position: relative; z-index: 1; max-width: 720px; margin: 0 auto; }

    .ep-back-link {
      display: inline-flex; align-items: center; gap: 4px;
      font-size: 0.82rem; font-weight: 700; color: #C9A0B0;
      text-decoration: none; margin-bottom: 16px; transition: color .15s;
    }
    .ep-back-link:hover { color: #C9748A; }

    /* Chat card */
    .ep-chat-card {
      background: rgba(255,255,255,.82); backdrop-filter: blur(16px);
      border: 1.5px solid rgba(255,200,220,.6); border-radius: 28px;
      overflow: hidden;
    }

    /* Chat header */
    .ep-chat-header {
      padding: 22px 28px;
      border-bottom: 1.5px solid #FFF0F4;
      display: flex; align-items: center; gap: 14px;
    }
    .ep-chat-header-icon {
      width: 46px; height: 46px; border-radius: 16px;
      background: linear-gradient(135deg, #FF8FAB, #C9748A);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem; flex-shrink: 0;
    }
    .ep-chat-header-info h2 { font-size: 1.05rem; font-weight: 800; color: #3D2B35; margin-bottom: 2px; }
    .ep-chat-header-info p  { font-size: 0.76rem; font-weight: 600; color: #9E7A88; }

    /* Messages area */
    .ep-messages {
      padding: 20px 28px;
      display: flex;
      flex-direction: column;
      gap: 14px;
      max-height: 420px;
      overflow-y: auto;
      scroll-behavior: smooth;
    }
    .ep-messages::-webkit-scrollbar { width: 4px; }
    .ep-messages::-webkit-scrollbar-track { background: transparent; }
    .ep-messages::-webkit-scrollbar-thumb { background: #FFD6E0; border-radius: 99px; }

    /* Individual message bubble */
    .ep-msg { display: flex; gap: 10px; align-items: flex-end; }
    .ep-msg.mine { flex-direction: row-reverse; }

    .ep-msg-avatar {
      width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
      background: linear-gradient(135deg, #FFB8CC, #D4A8F0);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.68rem; font-weight: 800; color: #3D2B35;
      border: 2px solid #FFD6E0; overflow: hidden;
    }
    .ep-msg-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .ep-msg.mine .ep-msg-avatar { background: linear-gradient(135deg, #FF8FAB, #C9748A); color: #fff; border-color: #FFB8CC; }

    .ep-msg-body { max-width: 68%; }
    .ep-msg-name {
      font-size: 0.7rem; font-weight: 800; color: #C9A0B0;
      margin-bottom: 4px; padding: 0 4px;
    }
    .ep-msg.mine .ep-msg-name { text-align: right; }

    .ep-msg-bubble {
      padding: 10px 16px;
      border-radius: 20px 20px 20px 4px;
      background: rgba(255,255,255,.9);
      border: 1.5px solid #FFE0EC;
      font-size: 0.88rem; font-weight: 600; color: #3D2B35;
      line-height: 1.5; word-break: break-word;
    }
    .ep-msg.mine .ep-msg-bubble {
      background: linear-gradient(135deg, #FF8FAB, #C9748A);
      color: #fff;
      border: none;
      border-radius: 20px 20px 4px 20px;
    }

    /* Empty state */
    .ep-chat-empty {
      text-align: center; padding: 40px 20px; color: #C9A0B0;
    }
    .ep-chat-empty .icon { font-size: 2rem; margin-bottom: 8px; }
    .ep-chat-empty p { font-size: 0.85rem; font-weight: 700; }

    /* Input area */
    .ep-chat-form {
      padding: 18px 28px;
      border-top: 1.5px solid #FFF0F4;
      display: flex; gap: 10px; align-items: flex-end;
    }
    .ep-textarea {
      flex: 1; padding: 12px 16px; border-radius: 16px;
      border: 1.5px solid #FFD6E0;
      background: rgba(255,255,255,.9);
      font-family: 'Nunito', sans-serif; font-size: 0.9rem; font-weight: 600;
      color: #3D2B35; resize: none; outline: none;
      transition: border-color .15s; line-height: 1.5; min-height: 48px; max-height: 120px;
    }
    .ep-textarea:focus { border-color: #FF8FAB; }
    .ep-textarea::placeholder { color: #C9A0B0; }
    .ep-send-btn {
      width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
      background: linear-gradient(135deg, #FF8FAB, #C9748A);
      border: none; color: #fff; font-size: 1.1rem; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 14px rgba(255,143,171,.45);
      transition: all .2s cubic-bezier(.34,1.56,.64,1);
    }
    .ep-send-btn:hover { transform: scale(1.1); box-shadow: 0 8px 22px rgba(255,143,171,.55); }
    .ep-send-btn:active { transform: scale(0.95); }

    @media (max-width: 600px) {
      .ep-messages { max-height: 320px; padding: 16px 16px; }
      .ep-chat-header { padding: 18px 18px; }
      .ep-chat-form   { padding: 14px 16px; }
      .ep-msg-body    { max-width: 82%; }
    }
    @media (prefers-reduced-motion: reduce) {
      .ep-send-btn { transition: none; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <!-- Nav -->
  <nav class="ep-nav">
    <a href="<?php echo isset($user['role']) && $user['role'] === 'helper' ? 'helper/dashboard.php' : 'user/dashboard.php'; ?>" class="ep-logo">
      Errand<span>Pal</span>
    </a>
    <div class="ep-nav-right">
      <div class="ep-avatar">
        <?php if (!empty($user['profile_image'])): ?>
          <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
        <?php else: ?>
          <?php echo $initials; ?>
        <?php endif; ?>
      </div>
      <a href="logout.php" class="ep-logout-btn">Logout</a>
    </div>
  </nav>

  <div class="ep-content">

    <a href="javascript:history.back()" class="ep-back-link">← Go back</a>

    <div class="ep-chat-card">

      <!-- Header -->
      <div class="ep-chat-header">
        <div class="ep-chat-header-icon">💬</div>
        <div class="ep-chat-header-info">
          <h2><?php echo $task ? htmlspecialchars($task['title']) : 'Task Chat'; ?></h2>
          <p>Task #<?php echo $task_id; ?> · Chat with your errand partner</p>
        </div>
      </div>

      <!-- Messages -->
      <div class="ep-messages" id="msgList">
        <?php $msg_count = mysqli_num_rows($message_query); ?>
        <?php if ($msg_count === 0): ?>
          <div class="ep-chat-empty">
            <div class="icon">🌸</div>
            <p>No messages yet — say hello!</p>
          </div>
        <?php else: ?>
          <?php while ($msg = mysqli_fetch_assoc($message_query)): ?>
            <?php $is_mine = ($msg['sender_id'] == $user_id); ?>
            <div class="ep-msg <?php echo $is_mine ? 'mine' : ''; ?>">
              <div class="ep-msg-avatar">
                <?php if (!empty($msg['profile_image'])): ?>
                  <img src="uploads/<?php echo htmlspecialchars($msg['profile_image']); ?>" alt="">
                <?php else: ?>
                  <?php echo make_initials($msg['fullname']); ?>
                <?php endif; ?>
              </div>
              <div class="ep-msg-body">
                <div class="ep-msg-name"><?php echo htmlspecialchars($msg['fullname']); ?></div>
                <div class="ep-msg-bubble"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>

      <!-- Input -->
      <form action="send_message.php" method="POST" class="ep-chat-form">
        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
        <textarea
          name="message"
          class="ep-textarea"
          placeholder="Type a message…"
          rows="1"
          required
          id="msgInput"></textarea>
        <button type="submit" class="ep-send-btn" title="Send">➤</button>
      </form>

    </div>
  </div>

  <script>
    // Auto-scroll to bottom of messages on load
    const list = document.getElementById('msgList');
    if (list) list.scrollTop = list.scrollHeight;

    // Auto-grow textarea
    const ta = document.getElementById('msgInput');
    if (ta) {
      ta.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
      });
      // Send on Enter, new line on Shift+Enter
      ta.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          this.closest('form').submit();
        }
      });
    }
  </script>
</body>
</html>