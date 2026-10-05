<?php
header('Content-Type: application/json');
require 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($method === 'GET') {
    $result = $conn->query("SELECT id, title, content, created_at FROM notes ORDER BY updated_at DESC");
    $notes = [];
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
    echo json_encode($notes);
    exit;
}

if ($method === 'POST') {
    if ($action === 'create') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $stmt = $conn->prepare("INSERT INTO notes (title, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $content);
        $stmt->execute();
        echo json_encode(["success" => true, "id" => $stmt->insert_id]);
        exit;
    }
    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $id);
        $stmt->execute();
        echo json_encode(["success" => true]);
        exit;
    }
    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["success" => true]);
        exit;
    }
}

echo json_encode(["error" => "Invalid request"]);
?>
