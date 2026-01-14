<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Services/AuthService.php';

AuthService::checkAuth();
$user_id = $_SESSION['user_id'];
$message = "";

// Delete logic for Contact Base
if (isset($_GET['delete_list'])) {
    $id = $_GET['delete_list'];
    try {
        // Delete associated numbers first to be safe
        $pdo->prepare("DELETE FROM marketing_user_number WHERE list_id = ? AND user_id = ?")->execute([$id, $user_id]);
        // Delete the list
        $stmt = $pdo->prepare("DELETE FROM contact_lists WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        header("Location: contacts.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['toast'] = ['message' => "❌ Cannot delete this list because it is currently used by a Campaign.", 'type' => "error"];
        } else {
            $_SESSION['toast'] = ['message' => "❌ Error deleting list: " . $e->getMessage(), 'type' => "error"];
        }
        header("Location: contacts.php");
        exit;
    }
}

// 1. Handle New List Creation
if (isset($_POST['create_list'])) {
    $name = trim($_POST['list_name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO contact_lists (list_name, user_id) VALUES (?, ?)");
        $stmt->execute([$name, $user_id]);
        $_SESSION['toast'] = ['message' => "✅ List created successfully!", 'type' => "success"];
    }
    header("Location: contacts.php");
    exit;
}

// 2. Handle CSV Import
if (isset($_POST['import_csv'])) {
    $list_id = $_POST['target_list_id'];
    if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        $file = fopen($_FILES['csv_file']['tmp_name'], "r");
        $stmt = $pdo->prepare("INSERT INTO marketing_user_number (phone_number, list_id, user_id, is_sent) VALUES (?, ?, ?, 0)");

        while (($data = fgetcsv($file, 1000, ",")) !== false) {
            if (!isset($data[0]))
                continue;
            $phone = trim($data[0]);

            // Auto-format: Prepend 880 if needed
            if (strpos($phone, '0') === 0) {
                // Starts with 0 (e.g. 017...), remove 0 and add 880
                $phone = '880' . substr($phone, 1);
            } elseif (strlen($phone) == 10) {
                // 10 digits (e.g. 17...), add 880
                $phone = '880' . $phone;
            }

            $stmt->execute([$phone, $list_id, $user_id]);
        }
        fclose($file);
        $_SESSION['toast'] = ['message' => "✅ Numbers imported to list!", 'type' => "success"];
    }
    header("Location: contacts.php");
    exit;
}

// 3. Fetch Lists for Dropdowns and Display
$listStmt = $pdo->prepare("SELECT * FROM contact_lists WHERE user_id = ?");
$listStmt->execute([$user_id]);
$myLists = $listStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Contacts | Marketing Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>📱 Contact Bases & Import</h1>

        <div class="card">
            <h2>1. Create a New Customer Base</h2>
            <form method="POST" class="row">
                <input type="hidden" name="create_list" value="1">
                <input type="text" name="list_name" placeholder="e.g., HSC 2026 Batch" required class="file-input"
                    style="border: 1px solid #ddd;">
                <button type="submit" name="create_list" class="btn">Create List</button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>2. Upload Numbers to a Base</h2>
            <form method="POST" enctype="multipart/form-data" class="row">
                <input type="hidden" name="import_csv" value="1">
                <select name="target_list_id" required style="padding: 10px; border-radius: 10px;">
                    <option value="">-- Select Customer Base --</option>
                    <?php foreach ($myLists as $list): ?>
                        <option value="<?php echo $list['id']; ?>"><?php echo htmlspecialchars($list['list_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="file" name="csv_file" accept=".csv" required class="file-input">
                <!-- <button type="button" onclick="window.location.reload();" class="btn"
                    style="background-color: #95a5a6; margin-left: 10px;">Refresh List</button> -->
                <button type="submit" name="import_csv" class="btn secondary">Upload CSV</button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>Your Contact Bases</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>List Name</th>
                            <th>Total Numbers</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myLists as $list):
                            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM marketing_user_number WHERE list_id = ?");
                            $countStmt->execute([$list['id']]);
                            $count = $countStmt->fetchColumn();
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($list['list_name']); ?></td>
                                <td><?php echo $count; ?></td>
                                <td><?php echo $list['created_at']; ?></td>
                                <td><a href="#" class="btn"
                                        style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.8rem;"
                                        onclick="event.preventDefault(); Popup.confirm('Are you sure you want to delete this list and all its numbers?', () => window.location.href='?delete_list=<?php echo $list['id']; ?>', 'Confirm Deletion')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row" style="margin-top: 20px;">
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>
    </div>


    <!-- Popup Assets -->
    <link rel="stylesheet" href="assets/popup.css">
    <script src="assets/popup.js"></script>
    <?php if (isset($_SESSION['toast'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Popup.toast('<?php echo $_SESSION['toast']['message']; ?>', '<?php echo $_SESSION['toast']['type']; ?>');
            });
        </script>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>
    <!-- Custom Scripts -->
    <script src="assets/form.js"></script>
</body>

</html>