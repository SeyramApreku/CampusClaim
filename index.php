<?php
// Landing/Home page for CampusClaim
// Redirects to browse if logged in, otherwise shows welcome screen.

session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: items/browse.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusClaim</title>
    <meta name="description" content="Centralized lost and found platform for Ashesi University students.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.0.3/src/index.js" defer></script>
</head>

<body>
    <section class="hero">
        <div class="hero-badge">CampusClaim</div>

        <div class="hero-logo" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
            <img src="assets/images/logo.png" alt="Ashesi Logo" style="height: 55px; border-radius: 8px;">
            Campus<span>Claim</span>
        </div>
        <p class="hero-sub" style="font-size: 1.5rem; font-weight: 500; margin-bottom: 1rem; margin-top: -1rem;">
            Ashesi Lost and Found
        </p>
        <p class="hero-sub">
            The centralized platform to report, search, and recover lost items on campus.
            Made by students, for students.
        </p>

        <div class="hero-actions">
            <a href="auth/login.php" class="btn btn-gold">Log In</a>
            <a href="auth/register.php" class="btn-outline-white">Create Account</a>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="icon">Search</div>
                <strong>Browse Items</strong>
                <p>Search all reported lost &amp; found items on campus</p>
            </div>
            <div class="feature-card">
                <div class="icon">Report</div>
                <strong>Report an Item</strong>
                <p>Lost or found something? Post it in seconds</p>
            </div>
            <div class="feature-card">
                <div class="icon">Match</div>
                <strong>Auto-Matching</strong>
                <p>Get notified when a potential match is found</p>
            </div>
        </div>
    </section>
</body>

</html>