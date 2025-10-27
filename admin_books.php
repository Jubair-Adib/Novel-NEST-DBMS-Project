<?php
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin.php');
    exit;
}
$msg = '';

// Handle Add Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = trim($_POST['title']);
    $type = $_POST['type'] === 'new' ? trim($_POST['new_type']) : ($_POST['type'] === 'others' ? 'Others' : $_POST['type']);
    $author_id = intval($_POST['author_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $desc = trim($_POST['description']);
    $cover = '';
    if (!empty($_FILES['cover']['name'])) {
        $filename = basename($_FILES['cover']['name']);
        $target = 'assets/Covers/' . $filename;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $target)) {
            $cover = $target;
        }
    }
    $pdf_type = isset($_POST['pdf_available']) ? 'yes' : 'no';
    if ($title && $type && $author_id && $price > 0 && $stock >= 0) {
        $stmt = $db->prepare("INSERT INTO books (title, type, author_id, description, price, stock, cover, pdf_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $type, $author_id, $desc, $price, $stock, $cover, $pdf_type]);
        $book_id = $db->lastInsertId();
        foreach ($_POST['branch_ids'] as $branch_id) {
            $amount = isset($_POST['branch_stock'][$branch_id]) ? intval($_POST['branch_stock'][$branch_id]) : 0;
            $db->prepare("INSERT INTO book_branches (book_id, branch_id, amount) VALUES (?, ?, ?)")
                ->execute([$book_id, $branch_id, $amount]);
        }
        $msg = "Book added!";
    } else {
        $msg = "Please fill all required fields.";
    }
}

// Handle Delete Selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $ids = $_POST['book_ids'] ?? [];
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("DELETE FROM books WHERE id IN ($in)");
        $stmt->execute($ids);
        $msg = "Selected books deleted!";
    }
}

// Handle Delete All
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $db->exec("DELETE FROM books");
    $msg = "All books deleted!";
}

// Handle Reset (delete all and reload sample data if available)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_books'])) {
    $db->exec("DELETE FROM books");
    // Optionally reload sample data here
    $msg = "Books reset!";
}

// Handle Update Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $edit_id = intval($_POST['edit_id']);
    $title = trim($_POST['title']);
    $type = $_POST['type'] === 'new' ? trim($_POST['new_type']) : ($_POST['type'] === 'others' ? 'Others' : $_POST['type']);
    $author_id = intval($_POST['author_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $desc = trim($_POST['description']);
    $cover = '';
    if (!empty($_FILES['cover']['name'])) {
        $filename = basename($_FILES['cover']['name']);
        $target = 'assets/Covers/' . $filename;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $target)) {
            $cover = $target;
        }
    } else {
        // Keep old cover if not updated
        $stmt = $db->prepare("SELECT cover FROM books WHERE id = ?");
        $stmt->execute([$edit_id]);
        $cover = $stmt->fetchColumn();
    }
    $pdf_type = isset($_POST['pdf_available']) ? 'yes' : 'no';
    if ($title && $type && $author_id && $price > 0 && $stock >= 0) {
        $stmt = $db->prepare("UPDATE books SET title=?, type=?, author_id=?, description=?, price=?, stock=?, cover=?, pdf_type=? WHERE id=?");
        $stmt->execute([$title, $type, $author_id, $desc, $price, $stock, $cover, $pdf_type, $edit_id]);
        // Update branch stocks
        $db->prepare("DELETE FROM book_branches WHERE book_id=?")->execute([$edit_id]);
        foreach ($_POST['branch_stock'] as $branch_id => $amount) {
            $db->prepare("INSERT INTO book_branches (book_id, branch_id, amount) VALUES (?, ?, ?)")
                ->execute([$edit_id, $branch_id, intval($amount)]);
        }
        $msg = "Book updated!";
    } else {
        $msg = "Please fill all required fields.";
    }
}

// Search
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE books.title LIKE ? OR authors.name LIKE ? OR branches.name LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Fetch authors and branches for dropdowns
$authors = $db->query("SELECT id, name FROM authors ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$branches = $db->query("SELECT id, name FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch types for dropdown
$type_stmt = $db->query("SELECT DISTINCT type FROM books WHERE type IS NOT NULL AND type != '' ORDER BY type ASC");
$all_types = $type_stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch books for list
$books = $db->query("SELECT books.*, authors.name AS author_name
    FROM books
    LEFT JOIN authors ON books.author_id = authors.id
    ORDER BY books.title ASC")->fetchAll(PDO::FETCH_ASSOC);

foreach ($books as &$b) {
    $branch_stmt = $db->prepare("SELECT branches.name FROM book_branches LEFT JOIN branches ON book_branches.branch_id = branches.id WHERE book_branches.book_id = ?");
    $branch_stmt->execute([$b['id']]);
    $b['branch_names'] = implode(', ', array_column($branch_stmt->fetchAll(PDO::FETCH_ASSOC), 'name'));
}
unset($b);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Book Management - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; font-family:sans-serif; margin:0; }
        header, footer { background:#222; color:#fff; padding:24px 0; text-align:center; }
        .admin-box { max-width:1000px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        h2 { text-align:center; color:#ff3366; margin-bottom:24px; }
        form { margin-bottom:32px; background:#f9f9fc; padding:16px; border-radius:12px; }
        input, select, textarea { width:100%; margin-bottom:10px; padding:8px; border-radius:8px; border:1px solid #ccc; }
        button, .login-btn { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; }
        .msg { color:#080; text-align:center; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:center; }
        th { background:#eee; }
        .actions a, .actions button { margin:0 6px; }
        .action-bar { display:flex; flex-wrap:wrap; justify-content:center; gap:12px; margin-top:24px; }
        .search-bar { display:flex; gap:8px; margin-bottom:24px; }
        .search-bar input { flex:1; }
        .cover-img { max-width:60px; max-height:80px; border-radius:6px; }
        @media (max-width:700px) {
            .admin-box { padding:10px; }
            form { padding:8px; }
            th, td { font-size:0.95rem; }
            .action-bar { flex-direction:column; gap:8px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>NOVEL NEST Admin &mdash; Book Management</h1>
    </header>
    <div class="admin-box">
        <h2>Add New Book</h2>
        <?php if ($msg) echo '<div class="msg">'.$msg.'</div>'; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Book Title" required>
            <select name="type" id="type-dropdown" required onchange="toggleTypeInput(this)">
                <option value="">Select Type</option>
                <?php foreach ($all_types as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                <?php endforeach; ?>
                <option value="others">Others</option>
                <option value="new">+ Add New Type</option>
            </select>
            <input type="text" name="new_type" id="new-type-input" placeholder="Enter new type" style="display:none;">
            <select name="author_id" required>
                <option value="">Select Author</option>
                <?php foreach ($authors as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="branch_ids[]" multiple required>
                <?php foreach ($branches as $br): ?>
                    <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php foreach ($branches as $br): ?>
                <div>
                    <label><?= htmlspecialchars($br['name']) ?> Stock:</label>
                    <input type="number" name="branch_stock[<?= $br['id'] ?>]" value="0" min="0">
                </div>
            <?php endforeach; ?>
            <input type="number" name="price" step="0.01" placeholder="Price" required>
            <input type="number" name="stock" placeholder="Stock" required>
            <textarea name="description" placeholder="Description"></textarea>
            <input type="file" name="cover" accept="image/*">
            <label><input type="checkbox" name="pdf_available" value="1"> PDF Available</label>
            <button type="submit" name="add_book">Add Book</button>
        </form>
        <form method="get" class="search-bar">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by book, author, or branch...">
            <button type="submit" class="login-btn">Search</button>
        </form>
        <form method="post">
            <div class="action-bar">
                <button type="submit" name="delete_selected" class="login-btn" onclick="return confirm('Delete selected books?')">Delete Selected</button>
                <button type="submit" name="delete_all" class="login-btn" onclick="return confirm('Delete ALL books? This cannot be undone!')">Delete All</button>
                <button type="submit" name="reset_books" class="login-btn" onclick="return confirm('Reset books? This will delete all and reload sample data if available!')">Reset Books</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="document.querySelectorAll('.book-checkbox').forEach(cb=>cb.checked=this.checked);"></th>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Author</th>
                        <th>Branch</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($books as $b): ?>
                    <tr>
                        <td><input type="checkbox" class="book-checkbox" name="book_ids[]" value="<?= $b['id'] ?>"></td>
                        <td>
                            <?php if ($b['cover']): ?>
                                <img src="<?= htmlspecialchars($b['cover']) ?>" alt="cover" class="cover-img">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($b['title']) ?></td>
                        <td><?= htmlspecialchars($b['type']) ?></td>
                        <td><?= htmlspecialchars($b['author_name']) ?></td>
                        <td><?= htmlspecialchars($b['branch_names']) ?></td>
                        <td><?= htmlspecialchars($b['price']) ?></td>
                        <td><?= htmlspecialchars($b['stock']) ?></td>
                        <td><?= htmlspecialchars($b['description']) ?></td>
                        <td><a href="admin_books.php?edit=<?= $b['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        <?php
        if (isset($_GET['edit'])) {
            $edit_id = intval($_GET['edit']);
            $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);

            // Fetch branch stocks for this book
            $branch_stock_stmt = $db->prepare("SELECT branch_id, amount FROM book_branches WHERE book_id = ?");
            $branch_stock_stmt->execute([$edit_id]);
            $branch_stocks = [];
            foreach ($branch_stock_stmt->fetchAll(PDO::FETCH_ASSOC) as $bs) {
                $branch_stocks[$bs['branch_id']] = $bs['amount'];
            }
            ?>
            <h2>Edit Book</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($edit_book['title']) ?>" required>
                <select name="type" id="type-dropdown-edit" required onchange="toggleTypeInputEdit(this)">
                    <option value="">Select Type</option>
                    <?php foreach ($all_types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $edit_book['type'] === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                    <?php endforeach; ?>
                    <option value="others" <?= $edit_book['type'] === 'Others' ? 'selected' : '' ?>>Others</option>
                    <option value="new">+ Add New Type</option>
                </select>
                <input type="text" name="new_type" id="new-type-input-edit" placeholder="Enter new type" style="display:none;">
                <select name="author_id" required>
                    <option value="">Select Author</option>
                    <?php foreach ($authors as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $edit_book['author_id'] == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php foreach ($branches as $br): ?>
                    <div>
                        <label><?= htmlspecialchars($br['name']) ?> Stock:</label>
                        <input type="number" name="branch_stock[<?= $br['id'] ?>]" value="<?= isset($branch_stocks[$br['id']]) ? (int)$branch_stocks[$br['id']] : 0 ?>" min="0">
                    </div>
                <?php endforeach; ?>
                <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($edit_book['price']) ?>" required>
                <input type="number" name="stock" value="<?= htmlspecialchars($edit_book['stock']) ?>" required>
                <textarea name="description"><?= htmlspecialchars($edit_book['description']) ?></textarea>
                <input type="file" name="cover" accept="image/*">
                <label><input type="checkbox" name="pdf_available" value="1" <?= $edit_book['pdf_type'] === 'yes' ? 'checked' : '' ?>> PDF Available</label>
                <button type="submit" name="update_book">Update Book</button>
            </form>
            <script>
            function toggleTypeInputEdit(sel) {
                document.getElementById('new-type-input-edit').style.display = sel.value === 'new' ? 'block' : 'none';
            }
            </script>
        <?php } ?>
    </div>
    <footer>
        &copy; <?= date('Y') ?> NOVEL NEST. All rights reserved.
    </footer>
    <script>
        function toggleTypeInput(sel) {
            document.getElementById('new-type-input').style.display = sel.value === 'new' ? 'block' : 'none';
        }
    </script>
</body>
</html>