<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../items/browse.php");
    }
    exit;
}

require_once '../classes/UserDAO.php';

$error = '';
$success = '';

$userDAO = new UserDAO();

if (isset($_GET['registered'])) {
    $success = 'Account created successfully. Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter both your email and password.';
    } else {
        $user = $userDAO->getUserByEmail($email);

        if ($user && password_verify($password, $user->getPasswordHash())) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user->getId();
            $_SESSION['name'] = $user->getName();
            $_SESSION['email'] = $user->getEmail();
            $_SESSION['role'] = $user->getRole();

            header("Location: " . $user->getDashboardUrl());
            exit;
        } else {
            $error = 'Incorrect email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | CampusClaim</title>
    <meta name="description" content="Log in to the CampusClaim platform.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body class="auth-body">

    <div class="auth-split">

        <!-- Left panel — brand -->
        <div class="auth-panel">
            <div class="auth-panel-content">
                <div class="auth-logo" style="display: flex; align-items: center; justify-content: flex-start; gap: 10px;">
                    <img src="../assets/images/logo.png" alt="Ashesi Logo" style="height: 40px; border-radius: 4px;">
                    Campus<span>Claim</span>
                </div>
                <p class="auth-tagline" style="font-size: 1.2rem; font-weight: 500; margin-bottom: 1rem;">
                    Ashesi Lost and Found
                </p>
                <p class="auth-tagline">
                    Reuniting the Ashesi community with their belongings, one report at a time.
                </p>
                <div class="auth-panel-links">
                    <p>Do not have an account? <a href="register.php">Register</a></p>
                </div>
            </div>
        </div>

        <!-- Right panel — form -->
        <div class="auth-form-side">
            <div class="auth-form-box">
                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-subtitle">Log in with your Ashesi email.</p>

                <!-- Success message (after registration) -->
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Error message -->
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php" novalidate>

                    <div class="form-group">
                        <label for="email">Ashesi Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($email ?? '') ?>" placeholder="yourname@ashesi.edu.gh" required
                            autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-toggle">
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Your password" required autocomplete="current-password">
                            <button type="button" class="toggle-password" data-target="password"
                                aria-label="Toggle password visibility">Show</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Log In</button>

                    <p class="auth-switch">
                        Do not have an account? <a href="register.php">Create one</a>
                    </p>

                </form>
            </div>
        </div>

    </div>

    <script src="../assets/js/auth.js"></script>
</body>

</html>