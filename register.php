<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account — ErrandPal</title>
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
    .blob-3 { width: 180px; height: 180px; background: #FFD6A5; bottom: 180px; right: 60px; }

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
      width: 100%; max-width: 480px;
      box-shadow: 0 24px 64px rgba(255,143,171,0.2), 0 4px 16px rgba(201,116,138,0.1);
      animation: cardIn 0.5s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes cardIn {
      from { opacity: 0; transform: translateY(28px) scale(0.96); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .back-link {
      display: inline-flex; align-items: center; gap: 6px;
      color: #9E7A88; font-size: 0.8rem; font-weight: 700;
      text-decoration: none; margin-bottom: 28px; transition: color 0.2s;
    }
    .back-link:hover { color: #FF8FAB; }

    .auth-header { text-align: center; margin-bottom: 28px; }
    .bear-mini {
      width: 68px; height: 68px;
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
      font-size: 1.8rem; color: #3D2B35; line-height: 1; margin-bottom: 6px;
    }
    .auth-title span { color: #FF8FAB; }
    .auth-subtitle { font-size: 0.86rem; color: #9E7A88; font-weight: 600; }

    /* Form */
    .auth-form { display: flex; flex-direction: column; }
    .form-group { display: flex; flex-direction: column; margin-bottom: 16px; }
    .form-label {
      font-size: 0.82rem; font-weight: 800; color: #6B4A58;
      margin-bottom: 7px; display: flex; align-items: center; gap: 5px;
    }
    .input-wrap { position: relative; }
    .input-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      font-size: 1rem; pointer-events: none;
    }
    .form-input, .form-select {
      width: 100%;
      padding: 12px 16px 12px 42px;
      border: 1.5px solid #F5C6D5;
      border-radius: 14px;
      background: #FFF5F7;
      font-family: 'Nunito', sans-serif;
      font-size: 0.9rem; color: #3D2B35;
      outline: none;
      transition: all 0.2s ease;
      box-sizing: border-box;
      appearance: none;
      -webkit-appearance: none;
    }
    .form-input::placeholder { color: #C9A0B0; }
    .form-input:focus, .form-select:focus {
      border-color: #FF8FAB;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(255,143,171,0.15);
    }

    /* Select arrow */
    .select-wrap { position: relative; }
    .select-wrap::after {
      content: '▾'; position: absolute; right: 14px; top: 50%;
      transform: translateY(-50%); pointer-events: none;
      color: #C9A0B0; font-size: 0.85rem;
    }

    /* Password toggle */
    .toggle-pw {
      position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      font-size: 1rem; color: #C9A0B0; padding: 0; transition: color 0.2s;
    }
    .toggle-pw:hover { color: #FF8FAB; }

    /* ── Role Selector ── */
    .role-label {
      font-size: 0.82rem; font-weight: 800; color: #6B4A58;
      margin-bottom: 10px; display: block;
    }
    .role-grid {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 10px; margin-bottom: 20px;
    }
    .role-option { display: none; }
    .role-card-label {
      display: flex; flex-direction: column; align-items: center;
      gap: 6px; padding: 16px 10px;
      border: 2px solid #F5C6D5;
      border-radius: 16px; cursor: pointer;
      background: #FFF5F7;
      transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1);
      text-align: center;
    }
    .role-card-label:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 18px rgba(255,143,171,0.2);
    }
    .role-option[value="user"] + .role-card-label   { }
    .role-option[value="helper"] + .role-card-label { }

    .role-option:checked + .role-card-label {
      border-width: 2.5px;
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(255,143,171,0.25);
    }
    #role_user:checked   + .role-card-label { border-color: #FF8FAB; background: #FFE8EF; }
    #role_helper:checked + .role-card-label { border-color: #B589D6; background: #F3E8FF; }

    .role-icon-big { font-size: 2rem; }
    .role-card-name {
      font-size: 0.85rem; font-weight: 800; color: #3D2B35;
    }
    .role-card-desc {
      font-size: 0.7rem; color: #9E7A88; font-weight: 600; line-height: 1.4;
    }

    /* Role perks */
    .role-perks {
      background: #FFF5F7; border: 1.5px solid #F5C6D5;
      border-radius: 14px; padding: 12px 16px;
      margin-bottom: 18px; font-size: 0.78rem;
      color: #6B4A58; font-weight: 600;
      display: none;
    }
    .role-perks.visible { display: block; }
    .role-perks ul { margin: 6px 0 0 0; padding-left: 18px; }
    .role-perks ul li { margin-bottom: 3px; }

    /* Password strength */
    .pw-strength { margin-top: 6px; }
    .pw-strength-bar {
      height: 4px; border-radius: 999px;
      background: #F5C6D5; overflow: hidden;
    }
    .pw-strength-fill {
      height: 100%; border-radius: 999px;
      width: 0%; transition: width 0.3s ease, background 0.3s ease;
    }
    .pw-strength-text {
      font-size: 0.7rem; font-weight: 700; color: #C9A0B0;
      margin-top: 4px;
    }

    /* Terms checkbox */
    .terms-row {
      display: flex; align-items: flex-start; gap: 10px;
      margin-bottom: 20px;
    }
    .terms-checkbox {
      width: 18px; height: 18px; flex-shrink: 0;
      border: 2px solid #F5C6D5; border-radius: 6px;
      accent-color: #FF8FAB; cursor: pointer; margin-top: 1px;
    }
    .terms-text {
      font-size: 0.78rem; color: #9E7A88; font-weight: 600; line-height: 1.5;
    }
    .terms-text a {
      color: #FF8FAB; text-decoration: none; font-weight: 700;
    }
    .terms-text a:hover { color: #E0607E; text-decoration: underline; }

    /* Submit */
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
    }
    .btn-submit:hover {
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 12px 28px rgba(255,143,171,0.5);
    }
    .btn-submit:active { transform: translateY(0) scale(0.98); }

    .or-divider {
      display: flex; align-items: center; gap: 12px;
      color: #C9A0B0; font-size: 0.75rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.08em;
      margin: 22px 0;
    }
    .or-divider::before, .or-divider::after {
      content: ''; flex: 1; height: 1px; background: #F5C6D5;
    }

    .login-cta {
      text-align: center; font-size: 0.85rem;
      color: #9E7A88; font-weight: 600;
    }
    .login-cta a {
      color: #FF8FAB; font-weight: 800;
      text-decoration: none; transition: color 0.2s;
    }
    .login-cta a:hover { color: #E0607E; text-decoration: underline; }

    /* Error / success */
    .error-msg {
      background: #FFF0F0; border: 1.5px solid #F9C0C0;
      border-radius: 12px; padding: 12px 16px;
      color: #C0392B; font-size: 0.84rem; font-weight: 700;
      margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
    }
    .success-msg {
      background: #E8FFF5; border: 1.5px solid #A8E6CF;
      border-radius: 12px; padding: 12px 16px;
      color: #1A7A50; font-size: 0.84rem; font-weight: 700;
      margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
    }

    @media (max-width: 480px) {
      .auth-card { padding: 36px 20px; }
      .auth-title { font-size: 1.55rem; }
      .role-grid { grid-template-columns: 1fr 1fr; }
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
  <div class="blob blob-3"></div>

  <div class="auth-wrapper">
    <div class="auth-card">

      <a href="index.php" class="back-link">← Back to home</a>

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
        <div class="auth-title">Join <span>ErrandPal!</span></div>
        <p class="auth-subtitle">Create your account and get started 🌸</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="success-msg">✅ <?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form action="register_process.php" method="POST" class="auth-form" id="registerForm">

        <!-- Full Name -->
        <div class="form-group">
          <label class="form-label">🙋 Full Name</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input
              class="form-input"
              type="text"
              name="fullname"
              placeholder="Your full name"
              required
              autocomplete="name"
              value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>"
            >
          </div>
        </div>

        <!-- Email -->
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

        <!-- Password -->
        <div class="form-group">
          <label class="form-label">🔒 Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔑</span>
            <input
              class="form-input"
              type="password"
              name="password"
              id="passwordField"
              placeholder="Create a strong password"
              required
              autocomplete="new-password"
              oninput="checkStrength(this.value)"
              style="padding-right: 44px;"
            >
            <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Show/hide password">👁</button>
          </div>
          <div class="pw-strength">
            <div class="pw-strength-bar">
              <div class="pw-strength-fill" id="strengthFill"></div>
            </div>
            <div class="pw-strength-text" id="strengthText">Enter a password</div>
          </div>
        </div>

        <!-- Role Selector -->
        <div class="form-group" style="margin-bottom: 8px;">
          <span class="role-label">🎭 I want to join as...</span>
          <div class="role-grid">

            <div>
              <input type="radio" class="role-option" name="role" id="role_user" value="user" checked onchange="updateRole()">
              <label class="role-card-label" for="role_user">
                <span class="role-icon-big">🙋</span>
                <span class="role-card-name">User</span>
                <span class="role-card-desc">Post tasks & hire helpers</span>
              </label>
            </div>

            <div>
              <input type="radio" class="role-option" name="role" id="role_helper" value="helper" onchange="updateRole()">
              <label class="role-card-label" for="role_helper">
                <span class="role-icon-big">🦸</span>
                <span class="role-card-name">Helper</span>
                <span class="role-card-desc">Accept tasks & earn money</span>
              </label>
            </div>

          </div>
        </div>

        <!-- Role perks hint -->
        <div class="role-perks visible" id="perks_user">
          <strong>As a User you can:</strong>
          <ul>
            <li>Post errands and tasks nearby</li>
            <li>Choose from trusted helpers</li>
            <li>Track your tasks in real-time</li>
          </ul>
        </div>
        <div class="role-perks" id="perks_helper">
          <strong>As a Helper you can:</strong>
          <ul>
            <li>Browse and accept nearby tasks</li>
            <li>Earn money on your own schedule</li>
            <li>Build your rating and reputation</li>
          </ul>
        </div>

        <!-- Terms -->
        <div class="terms-row">
          <input type="checkbox" class="terms-checkbox" id="terms" name="terms" required>
          <label class="terms-text" for="terms">
            I agree to ErrandPal's <a href="#">Terms of Service</a> and
            <a href="#">Privacy Policy</a>
          </label>
        </div>

        <button type="submit" class="btn-submit">✨ Create my account</button>

      </form>

      <div class="or-divider">or</div>

      <div class="login-cta">
        Already have an account?
        <a href="login.php">Log in here 🔑</a>
      </div>

    </div>
  </div>

  <script>
    function togglePassword() {
      const field = document.getElementById('passwordField');
      const btn = field.nextElementSibling;
      field.type = field.type === 'password' ? 'text' : 'password';
      btn.textContent = field.type === 'password' ? '👁' : '🙈';
    }

    function checkStrength(pw) {
      const fill = document.getElementById('strengthFill');
      const text = document.getElementById('strengthText');
      let score = 0;
      if (pw.length >= 8)          score++;
      if (/[A-Z]/.test(pw))        score++;
      if (/[0-9]/.test(pw))        score++;
      if (/[^A-Za-z0-9]/.test(pw)) score++;

      const levels = [
        { pct: '0%',   color: '#F5C6D5', label: 'Enter a password' },
        { pct: '25%',  color: '#E05050', label: '😬 Too weak' },
        { pct: '50%',  color: '#F9A05C', label: '😐 Getting there' },
        { pct: '75%',  color: '#FFD700', label: '🙂 Almost strong' },
        { pct: '100%', color: '#1DB87A', label: '💪 Strong password!' },
      ];
      const lvl = pw.length === 0 ? levels[0] : levels[score] || levels[1];
      fill.style.width = lvl.pct;
      fill.style.background = lvl.color;
      text.textContent = lvl.label;
      text.style.color = lvl.color === '#F5C6D5' ? '#C9A0B0' : lvl.color;
    }

    function updateRole() {
      const role = document.querySelector('input[name="role"]:checked').value;
      document.getElementById('perks_user').classList.toggle('visible', role === 'user');
      document.getElementById('perks_helper').classList.toggle('visible', role === 'helper');
    }
  </script>

</body>
</html>