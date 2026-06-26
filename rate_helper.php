<?php
include("auth.php");
include("db.php");
$submitted = false;
if(isset($_POST['submit'])){
  $task_id   = $_POST['task_id'];
  $helper_id = $_POST['helper_id'];
  $rating    = $_POST['rating'];
  $review    = $_POST['review'];
  mysqli_query(
    $conn,
    "INSERT INTO ratings(task_id, helper_id, rating, review)
     VALUES('$task_id','$helper_id','$rating','$review')"
  );
  $submitted = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rate Helper — ErrandPal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Nunito', sans-serif;
      background: linear-gradient(145deg, #FFF5F7 0%, #FFD6E0 55%, #F3E8FF 100%);
      min-height: 100vh;
    }

    /* ── Background blobs ── */
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

    /* ── Page wrapper ── */
    .page-wrapper {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 40px 20px;
    }

    /* ── Main card ── */
    .rating-card {
      background: rgba(255, 255, 255, 0.82);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1.5px solid rgba(255, 200, 220, 0.6);
      border-radius: 32px;
      padding: 52px 48px 44px;
      max-width: 480px;
      width: 100%;
      box-shadow: 0 24px 64px rgba(255, 143, 171, 0.2), 0 4px 16px rgba(201, 116, 138, 0.1);
      animation: cardIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }

    @keyframes cardIn {
      from { opacity: 0; transform: translateY(32px) scale(0.95); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ── Bear ── */
    .bear-wrap {
      display: flex;
      justify-content: center;
      margin-bottom: 12px;
    }
    .bear-svg {
      width: 90px;
      height: 90px;
      animation: bearBob 3s ease-in-out infinite;
      filter: drop-shadow(0 8px 16px rgba(255, 143, 171, 0.3));
    }
    @keyframes bearBob {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      30%       { transform: translateY(-7px) rotate(-3deg); }
      70%       { transform: translateY(-4px) rotate(2deg); }
    }

    /* ── Heading ── */
    .card-eyebrow {
      text-align: center;
      font-size: 0.72rem;
      font-weight: 800;
      color: #C9A0B0;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      margin-bottom: 4px;
    }
    .card-title {
      font-family: 'Pacifico', cursive;
      font-size: 2rem;
      color: #3D2B35;
      text-align: center;
      line-height: 1.15;
      margin-bottom: 6px;
    }
    .card-title span { color: #FF8FAB; }
    .card-sub {
      text-align: center;
      font-size: 0.88rem;
      color: #9E7A88;
      font-weight: 600;
      margin-bottom: 28px;
    }

    /* ── Divider ── */
    .divider {
      height: 1px;
      background: #F5C6D5;
      margin: 0 0 24px;
    }

    /* ── Star rating ── */
    .stars-label {
      font-size: 0.78rem;
      font-weight: 800;
      color: #C9748A;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 10px;
      display: block;
    }

    .stars-group {
      display: flex;
      justify-content: center;
      gap: 6px;
      margin-bottom: 6px;
      direction: rtl; /* right-to-left trick for CSS sibling fill */
    }
    .stars-group input[type="radio"] {
      display: none;
    }
    .stars-group label {
      font-size: 2.6rem;
      color: #E8CDD5;
      cursor: pointer;
      transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.15s;
      line-height: 1;
      user-select: none;
    }
    /* Hover: fill hovered star and all to the right (which are to the left in DOM due to rtl) */
    .stars-group label:hover,
    .stars-group label:hover ~ label {
      color: #FFD700;
      transform: scale(1.15);
    }
    /* Checked: fill selected star and all siblings before it (visually to the right in rtl) */
    .stars-group input[type="radio"]:checked ~ label {
      color: #FFD700;
    }
    .stars-group input[type="radio"]:checked + label {
      transform: scale(1.2);
      filter: drop-shadow(0 2px 8px rgba(255,215,0,0.5));
    }

    .stars-hint {
      text-align: center;
      font-size: 0.72rem;
      color: #C9A0B0;
      font-weight: 700;
      margin-bottom: 24px;
      min-height: 1em;
    }

    /* ── Textarea ── */
    .field-label {
      display: block;
      font-size: 0.78rem;
      font-weight: 800;
      color: #C9748A;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 8px;
    }
    textarea {
      width: 100%;
      min-height: 110px;
      padding: 14px 16px;
      border: 1.5px solid #FFBDD0;
      border-radius: 16px;
      font-family: 'Nunito', sans-serif;
      font-size: 0.92rem;
      font-weight: 600;
      color: #3D2B35;
      background: rgba(255,255,255,0.7);
      resize: vertical;
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
      margin-bottom: 24px;
    }
    textarea::placeholder { color: #C9A0B0; }
    textarea:focus {
      border-color: #FF8FAB;
      box-shadow: 0 0 0 3px rgba(255, 143, 171, 0.18);
    }

    /* ── Submit button ── */
    .btn-submit {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 15px 28px;
      border-radius: 999px;
      background: linear-gradient(135deg, #FF8FAB, #C9748A);
      color: white;
      font-family: 'Nunito', sans-serif;
      font-size: 1rem;
      font-weight: 800;
      border: none;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 6px 20px rgba(255, 143, 171, 0.45);
      letter-spacing: 0.02em;
    }
    .btn-submit:hover {
      transform: translateY(-3px) scale(1.03);
      box-shadow: 0 12px 32px rgba(255, 143, 171, 0.55);
    }
    .btn-submit:active { transform: translateY(0) scale(0.98); }

    /* ── Back link ── */
    .back-link {
      display: block;
      text-align: center;
      margin-top: 18px;
      font-size: 0.8rem;
      font-weight: 700;
      color: #C9748A;
      text-decoration: none;
      transition: color 0.15s;
    }
    .back-link:hover { color: #E0607E; text-decoration: underline; }

    /* ── Success state ── */
    .success-wrap {
      text-align: center;
      padding: 16px 0 8px;
    }
    .success-icon {
      font-size: 3.5rem;
      display: block;
      margin-bottom: 12px;
      animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
    @keyframes popIn {
      from { transform: scale(0); opacity: 0; }
      to   { transform: scale(1); opacity: 1; }
    }
    .success-title {
      font-family: 'Pacifico', cursive;
      font-size: 1.7rem;
      color: #3D2B35;
      margin-bottom: 8px;
    }
    .success-title span { color: #FF8FAB; }
    .success-msg {
      font-size: 0.9rem;
      color: #9E7A88;
      font-weight: 600;
      line-height: 1.6;
      margin-bottom: 24px;
    }
    .btn-secondary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 13px 28px;
      border-radius: 999px;
      background: rgba(255,255,255,0.7);
      color: #C9748A;
      font-family: 'Nunito', sans-serif;
      font-size: 0.95rem;
      font-weight: 800;
      text-decoration: none;
      border: 2px solid #FFB8CC;
      transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .btn-secondary:hover {
      background: #FFE8EF;
      border-color: #FF8FAB;
      color: #E0607E;
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(255,143,171,0.2);
      text-decoration: none;
    }

    /* ── Responsive ── */
    @media (max-width: 520px) {
      .rating-card { padding: 36px 22px 32px; }
      .card-title  { font-size: 1.65rem; }
      .stars-group label { font-size: 2rem; }
    }
    @media (prefers-reduced-motion: reduce) {
      .bear-svg, .rating-card { animation: none; }
    }
  </style>
</head>
<body>

  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <div class="page-wrapper">
    <div class="rating-card">

      <!-- Bear mascot -->
      <div class="bear-wrap">
        <svg class="bear-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
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
          <ellipse cx="32" cy="36" rx="5" ry="4" fill="#FFB8CC" opacity="0.5"/>
          <ellipse cx="88" cy="36" rx="5" ry="4" fill="#FFB8CC" opacity="0.5"/>
          <ellipse cx="60" cy="106" rx="14" ry="6" fill="#F5C6D5"/>
        </svg>
      </div>

      <?php if ($submitted): ?>

        <!-- ── Success state ── -->
        <div class="success-wrap">
          <span class="success-icon">🌸</span>
          <div class="success-title">Thanks for the <span>love!</span></div>
          <p class="success-msg">Your review has been submitted.<br>Your helper will really appreciate it!</p>
          <a href="dashboard.php" class="btn-secondary">🏠 Back to Dashboard</a>
        </div>

      <?php else: ?>

        <!-- ── Form state ── -->
        <div class="card-eyebrow">ErrandPal</div>
        <div class="card-title">Rate your <span>Helper</span></div>
        <p class="card-sub">How did your errand go? Share your experience 🌸</p>
        <div class="divider"></div>

        <form method="POST" id="ratingForm">
          <input type="hidden" name="task_id"   value="<?php echo htmlspecialchars($_GET['task_id']); ?>">
          <input type="hidden" name="helper_id" value="<?php echo htmlspecialchars($_GET['helper_id']); ?>">

          <!-- Star picker -->
          <span class="stars-label">Your rating</span>
          <div class="stars-group" role="radiogroup" aria-label="Star rating">
            <input type="radio" name="rating" id="s5" value="5" checked>
            <label for="s5" title="5 stars" aria-label="5 stars">★</label>
            <input type="radio" name="rating" id="s4" value="4">
            <label for="s4" title="4 stars" aria-label="4 stars">★</label>
            <input type="radio" name="rating" id="s3" value="3">
            <label for="s3" title="3 stars" aria-label="3 stars">★</label>
            <input type="radio" name="rating" id="s2" value="2">
            <label for="s2" title="2 stars" aria-label="2 stars">★</label>
            <input type="radio" name="rating" id="s1" value="1">
            <label for="s1" title="1 star"  aria-label="1 star">★</label>
          </div>
          <div class="stars-hint" id="starsHint">Amazing — 5 stars! ✨</div>

          <!-- Review textarea -->
          <label class="field-label" for="review">Leave a review</label>
          <textarea id="review" name="review" placeholder="Tell others what made this errand great (or how it could've been better)…"></textarea>

          <!-- Submit -->
          <button type="submit" name="submit" class="btn-submit">
            🌟 Submit Rating
          </button>
        </form>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

      <?php endif; ?>

    </div>
  </div>

  <script>
    // Live star hint text
    const hints = {
      '1': 'Could be better… 1 star',
      '2': 'Not great — 2 stars 😕',
      '3': 'Pretty okay — 3 stars 🙂',
      '4': 'Really good — 4 stars 😊',
      '5': 'Amazing — 5 stars! ✨'
    };
    const hintEl = document.getElementById('starsHint');
    document.querySelectorAll('.stars-group input[type="radio"]').forEach(radio => {
      radio.addEventListener('change', () => {
        hintEl.textContent = hints[radio.value] || '';
      });
    });
  </script>

</body>
</html>