<?php
// SQLite connection for NOVEL NEST
$db_file = __DIR__ . '/../db/nn_library.sqlite';
try {
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// If no books, show a message in the table
$books_count = $db->query("SELECT COUNT(*) FROM books")->fetchColumn();
if ($books_count == 0) {
    // Generate and insert 1000 sample books
    $authors = $db->query("SELECT id FROM authors")->fetchAll(PDO::FETCH_COLUMN);
    if (!$authors) {
        // Insert a default author if none exists
        $db->exec("INSERT INTO authors (name) VALUES ('Default Author')");
        $authors = [$db->lastInsertId()];
    }
    $types = ['Novel', 'Drama', 'Poetry', 'Science', 'History', 'Biography', 'Children', 'Comics'];
    $stmt = $db->prepare("INSERT INTO books (title, type, author_id, price, stock, description) VALUES (?, ?, ?, ?, ?, ?)");
    for ($i = 1; $i <= 1000; $i++) {
        $title = "Sample Book #$i";
        $type = $types[array_rand($types)];
        $author_id = $authors[array_rand($authors)];
        $price = rand(5, 50) + (rand(0, 99) / 100);
        $stock = rand(1, 20);
        $desc = "This is a sample description for book #$i.";
        $stmt->execute([$title, $type, $author_id, $price, $stock, $desc]);
    }
}
?>
