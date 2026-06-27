<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ErrandPal — Your Errand Companion</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
  <style>
    /* ── Page-level overrides for index.php ── */
    body {
      font-family: 'Nunito', sans-serif;
      background: linear-gradient(145deg, #FFF5F7 0%, #FFD6E0 55%, #F3E8FF 100%);
      min-height: 100vh;
      margin: 0;
    }

    /* Floating blobs for background depth */
    .blob {
      position: fixed;
      border-radius: 50%;
      filter: blur(60px);
      opacity: 0.35;
      pointer-events: none;
      z-index: 0;
    }
    .blob-1 { width: 420px; height: 420px; background: #FFB8CC; top: -100px; right: -100px; }
    .blob-2 { width: 300px; height: 300px; background: #D4A8F0; bottom: 60px; left: -80px; }
    .blob-3 { width: 200px; height: 200px; background: #FFD6A5; bottom: 200px; right: 100px; }

    /* Hero wrapper */
    .hero-wrapper {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 40px 20px;
    }

    /* Main hero card */
    .hero-card {
      background: rgba(255, 255, 255, 0.82);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1.5px solid rgba(255, 200, 220, 0.6);
      border-radius: 32px;
      padding: 56px 52px;
      max-width: 520px;
      width: 100%;
      text-align: center;
      box-shadow: 0 24px 64px rgba(255, 143, 171, 0.2), 0 4px 16px rgba(201, 116, 138, 0.1);
      animation: cardFloat 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes cardFloat {
      from { opacity: 0; transform: translateY(32px) scale(0.95); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Bear illustration */
    .bear-wrap {
      margin-bottom: 8px;
      display: flex;
      justify-content: center;
    }
    .bear-svg {
      width: 110px;
      height: 110px;
      animation: bearBob 3s ease-in-out infinite;
      filter: drop-shadow(0 8px 16px rgba(255, 143, 171, 0.3));
    }
    @keyframes bearBob {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      30%       { transform: translateY(-8px) rotate(-3deg); }
      70%       { transform: translateY(-5px) rotate(2deg); }
    }

    /* Logo text */
    .logo-title {
      font-family: 'Pacifico', cursive;
      font-size: 2.6rem;
      color: #3D2B35;
      line-height: 1;
      margin-bottom: 10px;
      letter-spacing: -0.5px;
    }
    .logo-title span { color: #FF8FAB; }

    /* Tagline */
    .tagline {
      font-size: 1rem;
      color: #9E7A88;
      font-weight: 600;
      margin-bottom: 6px;
      line-height: 1.5;
    }

    /* Pill badges row */
    .feature-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
      margin: 20px 0 32px;
    }
    .pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 6px 14px;
      border-radius: 999px;
      font-size: 0.78rem;
      font-weight: 700;
      border: 1.5px solid;
    }
    .pill-rose   { background: #FFE8EF; color: #E0607E; border-color: #FFBDD0; }
    .pill-purple { background: #F3E8FF; color: #9C6FD6; border-color: #D4B8F0; }
    .pill-peach  { background: #FFF0E8; color: #C8682A; border-color: #F9C8A0; }

    /* Divider */
    .divider-text {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      color: #C9A0B0;
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }
    .divider-text::before,
    .divider-text::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #F5C6D5;
    }

    /* Button group */
    .btn-group {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .btn-primary {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 15px 28px;
      border-radius: 999px;
      background: linear-gradient(135deg, #FF8FAB, #C9748A);
      color: white;
      font-family: 'Nunito', sans-serif;
      font-size: 1rem;
      font-weight: 800;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 6px 20px rgba(255, 143, 171, 0.45);
      letter-spacing: 0.02em;
    }
    .btn-primary:hover {
      transform: translateY(-3px) scale(1.03);
      box-shadow: 0 12px 32px rgba(255, 143, 171, 0.55);
      color: white;
      text-decoration: none;
    }
    .btn-primary:active {
      transform: translateY(0) scale(0.98);
    }

    .btn-secondary {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 14px 28px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.7);
      color: #C9748A;
      font-family: 'Nunito', sans-serif;
      font-size: 1rem;
      font-weight: 800;
      text-decoration: none;
      border: 2px solid #FFB8CC;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
      letter-spacing: 0.02em;
    }
    .btn-secondary:hover {
      background: #FFE8EF;
      border-color: #FF8FAB;
      color: #E0607E;
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(255, 143, 171, 0.2);
      text-decoration: none;
    }

    /* Role cards at bottom */
    .roles-section {
      margin-top: 36px;
      padding-top: 28px;
      border-top: 1px solid #F5C6D5;
    }
    .roles-label {
      font-size: 0.72rem;
      font-weight: 800;
      color: #C9A0B0;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      margin-bottom: 14px;
    }
    .roles-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
    }
    .role-card {
      background: rgba(255, 255, 255, 0.6);
      border: 1.5px solid #F5C6D5;
      border-radius: 16px;
      padding: 14px 8px;
      text-align: center;
      transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
      cursor: default;
    }
    .role-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(255, 143, 171, 0.2);
    }
    .role-card:nth-child(1):hover { background: #FFE8EF; border-color: #FF8FAB; }
    .role-card:nth-child(2):hover { background: #F3E8FF; border-color: #B589D6; }
    .role-card:nth-child(3):hover { background: #FFF0E8; border-color: #F9A05C; }

    .role-icon { font-size: 1.6rem; margin-bottom: 5px; }
    .role-name {
      font-size: 0.78rem;
      font-weight: 800;
      color: #3D2B35;
      display: block;
    }
    .role-desc {
      font-size: 0.66rem;
      color: #9E7A88;
      font-weight: 600;
      margin-top: 2px;
      display: block;
    }

    /* Footer note */
    .footer-note {
      margin-top: 28px;
      font-size: 0.72rem;
      color: #C9A0B0;
      font-weight: 600;
    }
    .footer-note a {
      color: #FF8FAB;
      text-decoration: none;
      font-weight: 700;
    }
    .footer-note a:hover { color: #E0607E; text-decoration: underline; }

    /* Responsive */
    @media (max-width: 540px) {
      .hero-card { padding: 40px 24px; }
      .logo-title { font-size: 2rem; }
      .bear-svg   { width: 88px; height: 88px; }
      .roles-grid { grid-template-columns: 1fr; gap: 8px; }
    }

    @media (prefers-reduced-motion: reduce) {
      .bear-svg   { animation: none; }
      .hero-card  { animation: none; }
    }
  </style>
</head>
<body>

  <!-- Background blobs -->
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <div class="hero-wrapper">
    <div class="hero-card">

      <!-- Rilakkuma Bear -->
      <div class="bear-wrap">
        <svg class="bear-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
          <!-- Ears -->
          <ellipse cx="32" cy="36" rx="16" ry="15" fill="#F5C6D5"/>
          <ellipse cx="88" cy="36" rx="16" ry="15" fill="#F5C6D5"/>
          <ellipse cx="32" cy="36" rx="10" ry="10" fill="#FFD6E0"/>
          <ellipse cx="88" cy="36" rx="10" ry="10" fill="#FFD6E0"/>
          <!-- Body/Head -->
          <ellipse cx="60" cy="72" rx="42" ry="40" fill="#F5C6D5"/>
          <ellipse cx="60" cy="70" rx="37" ry="35" fill="#FFD6E0"/>
          <!-- Belly -->
          <ellipse cx="60" cy="75" rx="20" ry="15" fill="#FFEDF3"/>
          <!-- Eyes -->
          <circle cx="49" cy="62" r="5.5" fill="#3D2B35"/>
          <circle cx="71" cy="62" r="5.5" fill="#3D2B35"/>
          <circle cx="50.8" cy="60.2" r="2" fill="#fff"/>
          <circle cx="72.8" cy="60.2" r="2" fill="#fff"/>
          <!-- Nose -->
          <ellipse cx="60" cy="70" rx="5" ry="3.5" fill="#C9748A"/>
          <!-- Mouth -->
          <path d="M53 76 Q60 83 67 76" stroke="#C9748A" stroke-width="2.2" fill="none" stroke-linecap="round"/>
          <!-- Cheeks -->
          <ellipse cx="41" cy="68" rx="7" ry="4" fill="#FFB8CC" opacity="0.7"/>
          <ellipse cx="79" cy="68" rx="7" ry="4" fill="#FFB8CC" opacity="0.7"/>
          <!-- Ears inner detail -->
          <ellipse cx="32" cy="36" rx="5" ry="4" fill="#FFB8CC" opacity="0.5"/>
          <ellipse cx="88" cy="36" rx="5" ry="4" fill="#FFB8CC" opacity="0.5"/>
          <!-- Little paws at bottom -->
          <ellipse cx="60" cy="106" rx="14" ry="6" fill="#F5C6D5"/>
        </svg>
      </div>

      <!-- Brand -->
      <div class="logo-title">Errand<span>Pal</span></div>
      <p class="tagline">Your cozy errand companion 🌸<br>Get things done with a little help!</p>

      <!-- Feature pills -->
      <div class="feature-pills">
        <span class="pill pill-rose">🛍 Task Management</span>
        <span class="pill pill-purple">🤝 Find Helpers</span>
        <span class="pill pill-peach">📍 Nearby Errands</span>
      </div>

      <!-- CTA Buttons -->
      <div class="divider-text">Get Started</div>
      <div class="btn-group">
        <a href="login.php" class="btn-primary">
          🔑 Login to your account
        </a>
        <a href="register.php" class="btn-secondary">
          ✨ Create a free account
        </a>
      </div>

      <!-- Role info -->
      <div class="roles-section">
        <div class="roles-label">Who's ErrandPal for?</div>
        <div class="roles-grid">
          <div class="role-card">
            <div class="role-icon">🙋</div>
            <span class="role-name">Users</span>
            <span class="role-desc">Post tasks & get help</span>
          </div>
          <div class="role-card">
            <div class="role-icon">🦸</div>
            <span class="role-name">Helpers</span>
            <span class="role-desc">Earn by helping out</span>
          </div>
          <div class="role-card">
            <div class="role-icon">🛡</div>
            <span class="role-name">Admins</span>
            <span class="role-desc">Keep things running</span>
          </div>
        </div>
      </div>

      <!-- Footer note -->
      <p class="footer-note">
        By signing up, you agree to our
        <a href="#">Terms of Service</a> and
        <a href="#">Privacy Policy</a>
      </p>

    </div>
  </div>

</body>
</html>