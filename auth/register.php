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
    <title>Register | CampusClaim</title>
    <meta name="description" content="Create your CampusClaim account.">
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
                                placeholder="Create a password" required autocomplete="new-password" onkeyup="checkPassword(this.value)">
                            <button type="button" class="toggle-password" data-target="password"
                                aria-label="Toggle password visibility">Show</button>
                        </div>
                        <ul id="password-criteria" style="list-style-type: none; padding-left: 0; margin-top: 0.5rem; font-size: 0.85rem; color: #666;">
                            <li id="req-length">❌ At least 8 characters long</li>
                            <li id="req-upper">❌ At least one uppercase letter</li>
                            <li id="req-number">❌ At least one number</li>
                        </ul>
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
    <script>
        function checkPassword(pwd) {
            const reqLength = document.getElementById('req-length');
            const reqUpper = document.getElementById('req-upper');
            const reqNumber = document.getElementById('req-number');

            if (pwd.length >= 8) {
                reqLength.innerHTML = '✅ At least 8 characters long';
                reqLength.style.color = 'green';
            } else {
                reqLength.innerHTML = '❌ At least 8 characters long';
                reqLength.style.color = '#666';
            }

            if (/[A-Z]/.test(pwd)) {
                reqUpper.innerHTML = '✅ At least one uppercase letter';
                reqUpper.style.color = 'green';
            } else {
                reqUpper.innerHTML = '❌ At least one uppercase letter';
                reqUpper.style.color = '#666';
            }

            if (/\d/.test(pwd)) {
                reqNumber.innerHTML = '✅ At least one number';
                reqNumber.style.color = 'green';
            } else {
                reqNumber.innerHTML = '❌ At least one number';
                reqNumber.style.color = '#666';
            }
        }
    </script>
</body>

</html>