<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/db_connect.php';

$pdo = getDbConnection();

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    $searchQuery = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT id, name, price, image 
        FROM products 
        WHERE name LIKE :query 
        OR description LIKE :query
        AND status = 'active'
        ORDER BY name
        LIMIT 10
    ");
    $stmt->bindParam(':query', $searchQuery);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}