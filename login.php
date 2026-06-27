<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — ErrandPal</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Nunito', sans-serif;
      background: linear-gradient(145deg, #FFF5F7 0%, #FFD6E0 55%, #F3E8FF 100%);
      min-height: 100vh;
      margin: 0;
    }
    .blob { position: fixed; border-radius: 50%; filter: blur(60px); opacity: 0.35; pointer-events: none; z-index: 0; }
    .blob-1 { width: 380px; height: 380px; background: #FFB8CC; top: -80px; right: -80px; }
    .blob-2 { width: 260px; height: 260px; background: #D4A8F0; bottom: 40px; left: -60px; }

    .auth-wrapper {
      position: relative; z-index: 1;
      display: flex; align-items: center; justify-content: center;
      min-height: 100vh; padding: 40px 20px;
    }

    .auth-card {
      background: rgba(255,255,255,0.88);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1.5px solid rgba(255,200,220,0.6);
      border-radius: 32px;
      padding: 48px 44px;
      width: 100%; max-width: 440px;
      box-shadow: 0 24px 64px rgba(255,143,171,0.2), 0 4px 16px rgba(201,116,138,0.1);
      animation: cardIn 0.5s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes cardIn {
      from { opacity: 0; transform: translateY(28px) scale(0.96); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Back link */
    .back-link {
      display: inline-flex; align-items: center; gap: 6px;
      color: #9E7A88; font-size: 0.8rem; font-weight: 700;
      text-decoration: none; margin-bottom: 28px;
      transition: color 0.2s;
    }
    .back-link:hover { color: #FF8FAB; }

    /* Header */
    .auth-header { text-align: center; margin-bottom: 32px; }
    .bear-mini {
      width: 72px; height: 72px;
      animation: bearBob 3s ease-in-out infinite;
      filter: drop-shadow(0 6px 12px rgba(255,143,171,0.3));
      margin-bottom: 6px;
    }
    @keyframes bearBob {
      0%,100% { transform: translateY(0) rotate(0deg); }
      40%      { transform: translateY(-6px) rotate(-3deg); }
      70%      { transform: translateY(-3px) rotate(2deg); }
    }
    .auth-title {
      font-family: 'Pacifico', cursive;
      font-size: 1.9rem; color: #3D2B35; line-height: 1; margin-bottom: 6px;
    }
    .auth-title span { color: #FF8FAB; }
    .auth-subtitle { font-size: 0.88rem; color: #9E7A88; font-weight: 600; }

    /* Form */
    .auth-form { display: flex; flex-direction: column; gap: 0; }

    .form-group { display: flex; flex-direction: column; margin-bottom: 18px; }

    .form-label {
      font-size: 0.82rem; font-weight: 800; color: #6B4A58;
      margin-bottom: 7px; display: flex; align-items: center; gap: 5px;
    }

    .input-wrap { position: relative; }

    .input-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      font-size: 1rem; pointer-events: none;
    }

    .form-input {
      width: 100%;
      padding: 12px 16px 12px 42px;
      border: 1.5px solid #F5C6D5;
      border-radius: 14px;
      background: #FFF5F7;
      font-family: 'Nunito', sans-serif;
      font-size: 0.9rem;
      color: #3D2B35;
      outline: none;
      transition: all 0.2s ease;
      box-sizing: border-box;
    }
    .form-input::placeholder { color: #C9A0B0; }
    .form-input:focus {
      border-color: #FF8FAB;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(255,143,171,0.15);
    }

    /* Show/hide password toggle */
    .toggle-pw {
      position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      font-size: 1rem; color: #C9A0B0; padding: 0;
      transition: color 0.2s;
    }
    .toggle-pw:hover { color: #FF8FAB; }

    /* Forgot password */
    .forgot-link {
      text-align: right; margin-top: -10px; margin-bottom: 14px;
    }
    .forgot-link a {
      font-size: 0.78rem; font-weight: 700; color: #FF8FAB;
      text-decoration: none; transition: color 0.2s;
    }
    .forgot-link a:hover { color: #E0607E; text-decoration: underline; }

    /* Submit button */
    .btn-submit {
      width: 100%; padding: 14px;
      border-radius: 999px;
      background: linear-gradient(135deg, #FF8FAB, #C9748A);
      color: white;
      font-family: 'Nunito', sans-serif;
      font-size: 1rem; font-weight: 800;
      border: none; cursor: pointer;
      transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1);
      box-shadow: 0 6px 20px rgba(255,143,171,0.45);
      letter-spacing: 0.02em;
      margin-top: 4px;
    }
    .btn-submit:hover {
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 12px 28px rgba(255,143,171,0.5);
    }
    .btn-submit:active { transform: translateY(0) scale(0.98); }

    /* Divider */
    .or-divider {
      display: flex; align-items: center; gap: 12px;
      color: #C9A0B0; font-size: 0.75rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.08em;
      margin: 22px 0;
    }
    .or-divider::before, .or-divider::after {
      content: ''; flex: 1; height: 1px; background: #F5C6D5;
    }

    /* Register link */
    .register-cta {
      text-align: center;
      font-size: 0.85rem; color: #9E7A88; font-weight: 600;
    }
    .register-cta a {
      color: #FF8FAB; font-weight: 800; text-decoration: none;
      transition: color 0.2s;
    }
    .register-cta a:hover { color: #E0607E; text-decoration: underline; }

    /* Error message (PHP will echo this) */
    .error-msg {
      background: #FFF0F0; border: 1.5px solid #F9C0C0;
      border-radius: 12px; padding: 12px 16px;
      color: #C0392B; font-size: 0.84rem; font-weight: 700;
      margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
    }

    /* Success message */
    .success-msg {
      background: #E8FFF5; border: 1.5px solid #A8E6CF;
      border-radius: 12px; padding: 12px 16px;
      color: #1A7A50; font-size: 0.84rem; font-weight: 700;
      margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
    }

    @media (max-width: 480px) {
      .auth-card { padding: 36px 22px; }
      .auth-title { font-size: 1.6rem; }
    }
    @media (prefers-reduced-motion: reduce) {
      .bear-mini { animation: none; }
      .auth-card { animation: none; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <div class="auth-wrapper">
    <div class="auth-card">

      <!-- Back to home -->
      <a href="index.php" class="back-link">← Back to home</a>

      <!-- Header -->
      <div class="auth-header">
        <svg class="bear-mini" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
          <ellipse cx="32" cy="36" rx="16" ry="15" fill="#F5C6D5"/>
          <ellipse cx="88" cy="36" rx="16" ry="15" fill="#F5C6D5"/>
          <ellipse cx="32" cy="36" rx="10" ry="10" fill="#FFD6E0"/>
          <ellipse cx="88" cy="36" rx="10" ry="10" fill="#FFD6E0"/>
          <ellipse cx="60" cy="72" rx="42" ry="40" fill="#F5C6D5"/>
          <ellipse cx="60" cy="70" rx="37" ry="35" fill="#FFD6E0"/>
          <ellipse cx="60" cy="75" rx="20" ry="15" fill="#FFEDF3"/>
          <circle cx="49" cy="62" r="5.5" fill="#3D2B35"/>
          <circle cx="71" cy="62" r="5.5" fill="#3D2B35"/>
          <circle cx="50.8" cy="60.2" r="2" fill="#fff"/>
          <circle cx="72.8" cy="60.2" r="2" fill="#fff"/>
          <ellipse cx="60" cy="70" rx="5" ry="3.5" fill="#C9748A"/>
          <path d="M53 76 Q60 83 67 76" stroke="#C9748A" stroke-width="2.2" fill="none" stroke-linecap="round"/>
          <ellipse cx="41" cy="68" rx="7" ry="4" fill="#FFB8CC" opacity="0.7"/>
          <ellipse cx="79" cy="68" rx="7" ry="4" fill="#FFB8CC" opacity="0.7"/>
          <ellipse cx="60" cy="106" rx="14" ry="6" fill="#F5C6D5"/>
        </svg>
        <div class="auth-title">Welcome <span>back!</span></div>
        <p class="auth-subtitle">Log in to your ErrandPal account 🌸</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="success-msg">✅ <?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <!-- Login Form -->
      <form action="login_process.php" method="POST" class="auth-form">

        <div class="form-group">
          <label class="form-label">📧 Email Address</label>
          <div class="input-wrap">
            <span class="input-icon">✉️</span>
            <input
              class="form-input"
              type="email"
              name="email"
              placeholder="you@example.com"
              required
              autocomplete="email"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            >
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">🔒 Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔑</span>
            <input
              class="form-input"
              type="password"
              name="password"
              id="passwordField"
              placeholder="Enter your password"
              required
              autocomplete="current-password"
              style="padding-right: 44px;"
            >
            <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Show/hide password">👁</button>
          </div>
        </div>

        <div class="forgot-link">
          <a href="forgot_password.php">Forgot password?</a>
        </div>

        <button type="submit" class="btn-submit">🔑 Login to ErrandPal</button>

      </form>

      <div class="or-divider">or</div>

      <div class="register-cta">
        Don't have an account yet?
        <a href="register.php">Create one for free ✨</a>
      </div>

    </div>
  </div>

  <script>
    function togglePassword() {
      const field = document.getElementById('passwordField');
      const btn = field.nextElementSibling;
      if (field.type === 'password') {
        field.type = 'text';
        btn.textContent = '🙈';
      } else {
        field.type = 'password';
        btn.textContent = '👁';
      }
    }
  </script>

</body>
</html>