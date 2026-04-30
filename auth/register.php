<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../items/browse.php");
    exit;
}

require_once '../classes/UserDAO.php';

$errors = [];
$success = '';

$userDAO = new UserDAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        $errors[] = 'Full name is required.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (!str_ends_with(strtolower($email), '@ashesi.edu.gh')) {
        $errors[] = 'Only @ashesi.edu.gh email addresses are allowed.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if (empty($errors)) {
        if ($userDAO->emailExists($email)) {
            $errors[] = 'An account with that email address already exists.';
        }
    }

    if (empty($errors)) {
        $userDAO->createStudent($name, $email, $password, $phone);
        header("Location: login.php?registered=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Ashesi Lost &amp; Found</title>
    <meta name="description" content="Create your Ashesi Lost and Found account.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body class="auth-body">

    <div class="auth-split">

        <!-- Left panel — brand -->
        <div class="auth-panel">
            <div class="auth-panel-content">
                <div class="auth-logo">Ashesi <span>Lost&amp;Found</span></div>
                <p class="auth-tagline">
                    Reuniting the Ashesi community with their belongings, one report at a time.
                </p>
                <div class="auth-panel-links">
                    <p>Already have an account? <a href="login.php">Log in</a></p>
                </div>
            </div>
        </div>

        <!-- Right panel — form -->
        <div class="auth-form-side">
            <div class="auth-form-box">
                <h1 class="auth-title">Create your account</h1>
                <p class="auth-subtitle">Use your Ashesi email to get started.</p>

                <!-- Error messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $e): ?>
                            <p><?= htmlspecialchars($e) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" novalidate>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name"
                            class="form-control <?= in_array('Full name is required.', $errors) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($name ?? '') ?>" placeholder="e.g. Kwame Mensah" required
                            autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label for="email">Ashesi Email</label>
                        <input type="email" id="email" name="email"
                            class="form-control <?= (in_array('Email is required.', $errors) || in_array('Only @ashesi.edu.gh email addresses are allowed.', $errors)) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($email ?? '') ?>" placeholder="yourname@ashesi.edu.gh" required
                            autocomplete="email">
                        <p class="form-hint">Must be an @ashesi.edu.gh address.</p>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-toggle">
                            <input type="password" id="password" name="password"
                                class="form-control <?= in_array('Password must be at least 8 characters long.', $errors) ? 'is-invalid' : '' ?>"
                                placeholder="At least 8 characters" required autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="password"
                                aria-label="Toggle password visibility">Show</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number <span class="text-muted">(optional)</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                            value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="e.g. 0241234567"
                            autocomplete="tel">
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Create Account</button>

                    <p class="auth-switch">
                        Already have an account? <a href="login.php">Log in</a>
                    </p>

                </form>
            </div>
        </div>

    </div>

    <script src="../assets/js/auth.js"></script>
</body>

</html>