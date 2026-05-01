<?php
// items/report.php
require_once '../classes/ItemDAO.php';
require_once '../classes/ItemFactory.php';

// Set page title before including header
$pageTitle = 'Report Item';
require_once '../includes/header.php';
require_once '../classes/Database.php';

// Get database connection
$db = Database::getInstance();
$pdo = $db->getConnection();

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
        $image_url = null;
        
        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/uploads/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = basename($_FILES['image']['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($fileExt, $allowedExts)) {
                // Generate unique filename to prevent overwrites
                $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
                $targetFilePath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $image_url = 'assets/uploads/' . $newFileName; // Store relative path for DB
                } else {
                    $errors[] = "Error uploading the image.";
                }
            } else {
                $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
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
                'item_date' => $item_date,
                'image_url' => $image_url
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
<!-- Main content starts here -->
<div class="form-container" style="max-width: 600px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <h2>Report an Item</h2>
    <p>Did you lose something or find something? Let the community know.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="report.php" enctype="multipart/form-data">
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

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Upload Image (Optional)</label>
                <input type="file" name="image" class="form-control" accept="image/png, image/jpeg, image/jpg, image/gif, image/webp" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; background: white;">
                <small style="color: #666; display: block; margin-top: 0.25rem;">Max file size: 5MB. Formats: JPG, PNG, GIF.</small>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Report</button>
                <a href="browse.php" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
