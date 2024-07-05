<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/database_functions.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['stamp_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stamp_id = $_POST['stamp_id'];
$today = date("Y-m-d");

// スタンプ使用記録を更新または作成
$result = update_stamp_usage($conn, $user_id, $stamp_id, $today);

echo json_encode(['success' => $result]);