<?php
// Landing/Home page for Ashesi Lost & Found
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
    <title>Ashesi Lost &amp; Found</title>
    <meta name="description" content="Centralized lost and found platform for Ashesi University students.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.0.3/src/index.js" defer></script>
</head>

<body>
    <section class="hero">
        <div class="hero-badge">Ashesi University</div>

        <div class="hero-logo">Ashesi <span>Lost&amp;Found</span></div>
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