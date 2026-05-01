<?php
require_once '../classes/Database.php';
$pdo = Database::getInstance()->getConnection();

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

// Restore old values so the form doesn't blank out on validation error
$old = [
    'type' => $_POST['type'] ?? 'lost',
    'title' => $_POST['title'] ?? '',
    'description' => $_POST['description'] ?? '',
    'category_id' => $_POST['category_id'] ?? '',
    'location_id' => $_POST['location_id'] ?? '',
    'item_date' => $_POST['item_date'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $location_id = $_POST['location_id'] ?? '';
    $item_date = $_POST['item_date'] ?? '';

    if (empty($title) || empty($description) || empty($category_id) || empty($location_id) || empty($item_date)) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        $image_url = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = basename($_FILES['image']['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($fileExt, $allowedExts)) {
                $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
                $targetFilePath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $image_url = 'assets/uploads/' . $newFileName;
                } else {
                    $errors[] = "Error uploading the image.";
                }
            } else {
                $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        }

        if (empty($errors)) {
            $data = [
                'user_id' => $_SESSION['user_id'],
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'category_id' => $category_id,
                'location_id' => $location_id,
                'item_date' => $item_date,
                'image_url' => $image_url
            ];

            try {
                $itemObj = ItemFactory::createItem($data);
                $new_item_id = $itemDAO->createItem($itemObj);

                if ($new_item_id) {
                    require_once '../classes/MatchingStrategy.php';
                    $strategy = new ExactLocationCategoryStrategy();
                    $matches = $strategy->findMatches($itemObj, $itemDAO);

                    header($matches ? "Location: matches.php?id=$new_item_id" : "Location: browse.php?reported=1");
                    exit;
                } else {
                    $errors[] = "Failed to report item. Please try again.";
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Report an Item';
require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="container">
        <div style="max-width: 680px; margin: 0 auto;">

            <div style="margin-bottom: 2rem;">
                <h1>Report an Item</h1>
                <p class="text-muted">Did you lose something or find something? Let the community know.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <?php foreach ($errors as $err)
                        echo "<p style='margin:0'>$err</p>"; ?>
                </div>
            <?php endif; ?>

            <div style="background: var(--white); padding: 2rem; border-radius: 8px; box-shadow: var(--shadow);">
                <form method="POST" action="report.php" enctype="multipart/form-data">

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 500; display: block; margin-bottom: 0.4rem;">Report Type</label>
                        <select name="type" class="form-control" required>
                            <option value="lost" <?= $old['type'] === 'lost' ? 'selected' : '' ?>>I Lost Something</option>
                            <option value="found" <?= $old['type'] === 'found' ? 'selected' : '' ?>>I Found Something
                            </option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 500; display: block; margin-bottom: 0.4rem;">Title <span
                                style="color:var(--crimson);">*</span></label>
                        <input type="text" name="title" class="form-control" required
                            placeholder="e.g., Black iPhone 13" value="<?= htmlspecialchars($old['title']) ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label style="font-weight: 500; display: block; margin-bottom: 0.4rem;">Category <span
                                    style="color:var(--crimson);">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= $old['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 500; display: block; margin-bottom: 0.4rem;">Location <span
                                    style="color:var(--crimson);">*</span></label>
                            <select name="location_id" class="form-control" required>
                                <option value="">-- Select Location --</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc['location_id'] ?>" <?= $old['location_id'] == $loc['location_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loc['location_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 500; display: block; margin-bottom: 0.4rem;">Date Lost / Found <span
                                style="color:var(--crimson);">*</span></label>
                        <input type="date" name="item_date" class="form-control" required
                            value="<?= htmlspecialchars($old['item_date']) ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 500; display: block; margin-bottom: 0.4rem;">Description <span
                                style="color:var(--crimson);">*</span></label>
                        <textarea name="description" class="form-control" required rows="4"
                            placeholder="Provide details like color, unique marks, brand, etc."><?= htmlspecialchars($old['description']) ?></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label style="font-weight: 500; display: block; margin-bottom: 0.4rem;">Upload Image <span
                                style="color:#888; font-weight:400;">(Optional)</span></label>
                        <input type="file" name="image" class="form-control"
                            accept="image/png, image/jpeg, image/jpg, image/gif, image/webp">
                        <small style="color: #666; display: block; margin-top: 0.25rem;">Max 5 MB &mdash; JPG, PNG, GIF,
                            WEBP</small>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Report</button>
                        <a href="browse.php" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>