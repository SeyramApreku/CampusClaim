<?php
// items/report.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../classes/ItemDAO.php';
require_once '../classes/ItemFactory.php';

$itemDAO = new ItemDAO();
$categories = $itemDAO->getCategories();
$locations = $itemDAO->getLocations();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $location_id = $_POST['location_id'] ?? '';
    $item_date = $_POST['item_date'] ?? '';

    // Basic Validation
    if (empty($title) || empty($description) || empty($category_id) || empty($location_id) || empty($item_date)) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        // Use Factory to generate object
        $data = [
            'user_id' => $_SESSION['user_id'],
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'category_id' => $category_id,
            'location_id' => $location_id,
            'item_date' => $item_date
        ];

        try {
            $itemObj = ItemFactory::createItem($data);
            $new_item_id = $itemDAO->createItem($itemObj);
            
            if ($new_item_id) {
                // Execute Auto-Matching Strategy
                require_once '../classes/MatchingStrategy.php';
                $strategy = new ExactLocationCategoryStrategy();
                // Pass the object to the strategy
                $matches = $strategy->findMatches($itemObj, $itemDAO);
                
                if (count($matches) > 0) {
                    // Redirect to matches page
                    header("Location: matches.php?id=$new_item_id");
                } else {
                    // No matches, go to dashboard
                    header("Location: browse.php?reported=1");
                }
                exit;
            } else {
                $errors[] = "Failed to report item. Please try again.";
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Item | Ashesi Lost & Found</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body style="background-color: #f4f7f6;">
    <div class="form-container">
        <h2>Report an Item</h2>
        <p>Did you lose something or find something? Let the community know.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="report.php">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Report Type</label>
                <select name="type" class="form-control" required style="width: 100%; padding: 0.5rem;">
                    <option value="lost">I Lost Something</option>
                    <option value="found">I Found Something</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Title (What is it?)</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g., Black iPhone 13" style="width: 100%; padding: 0.5rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Category</label>
                <select name="category_id" class="form-control" required style="width: 100%; padding: 0.5rem;">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Location</label>
                <select name="location_id" class="form-control" required style="width: 100%; padding: 0.5rem;">
                    <option value="">-- Select Location --</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['location_id'] ?>"><?= htmlspecialchars($loc['location_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Date Lost/Found</label>
                <input type="date" name="item_date" class="form-control" required style="width: 100%; padding: 0.5rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Description</label>
                <textarea name="description" class="form-control" required rows="4" placeholder="Provide details like color, unique marks, etc." style="width: 100%; padding: 0.5rem;"></textarea>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Report</button>
                <a href="browse.php" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
