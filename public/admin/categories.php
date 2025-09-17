<?php
// Start output buffering to prevent header issues
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure session is started before any output
ensure_session_started();

// Require admin access
if (!is_logged_in() || current_user()['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit;
}

$pdo = get_db_connection();

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_category':
                $name = trim($_POST['name']);
                if ($name) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                        $stmt->execute([$name]);
                        set_flash_message('Category added successfully.', 'success');
                    } catch (PDOException $e) {
                        if ($e->getCode() == '23000') {
                            set_flash_message('Category name already exists.', 'error');
                        } else {
                            set_flash_message('Error adding category.', 'error');
                        }
                    }
                }
                break;
                
            case 'delete_category':
                $category_id = (int)$_POST['category_id'];
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                set_flash_message('Category deleted successfully.', 'success');
                break;
                
            case 'update_category':
                $category_id = (int)$_POST['category_id'];
                $name = trim($_POST['name']);
                if ($name) {
                    try {
                        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
                        $stmt->execute([$name, $category_id]);
                        set_flash_message('Category updated successfully.', 'success');
                    } catch (PDOException $e) {
                        if ($e->getCode() == '23000') {
                            set_flash_message('Category name already exists.', 'error');
                        } else {
                            set_flash_message('Error updating category.', 'error');
                        }
                    }
                }
                break;
        }
        header('Location: categories.php');
        exit;
    }
}

// Get categories with product counts
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories • Admin • The Forbidden Codex</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../../favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="nav-logo">
                    <h2 class="logo-text">The Forbidden Codex</h2>
                </div>
                <div class="nav-buttons">
                    <a href="index.php" class="btn btn-secondary">Dashboard</a>
                    <a href="../logout.php" class="btn btn-login">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="container">
            <?php display_flash_messages(); ?>
            
            <div class="admin-header">
                <h1>Manage Categories</h1>
                <p>Organize the mystical realms of knowledge</p>
            </div>

            <!-- Add New Category -->
            <div class="admin-card">
                <h2>Add New Category</h2>
                <form method="POST" class="category-form">
                    <input type="hidden" name="action" value="add_category">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Category name..." 
                               required class="form-input" maxlength="100">
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>

            <!-- Categories List -->
            <div class="admin-card">
                <div class="card-header">
                    <h2>Categories (<?php echo count($categories); ?> total)</h2>
                </div>
                
                <?php if (empty($categories)): ?>
                    <p class="empty-state">No categories found. Add your first category above.</p>
                <?php else: ?>
                    <div class="categories-grid">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-card">
                                <div class="category-info">
                                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                    <p><?php echo $category['product_count']; ?> product(s)</p>
                                </div>
                                <div class="category-actions">
                                    <button onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>')" 
                                            class="btn btn-secondary btn-sm">Edit</button>
                                    <?php if ($category['product_count'] == 0): ?>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Has products</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Category</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update_category">
                <input type="hidden" name="category_id" id="editCategoryId">
                <div class="form-group">
                    <label for="editCategoryName">Category Name:</label>
                    <input type="text" name="name" id="editCategoryName" 
                           required class="form-input" maxlength="100">
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    
    <script>
        function editCategory(id, name) {
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
