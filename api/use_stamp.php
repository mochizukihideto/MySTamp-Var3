<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

function returnError($message)
{
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

try {
    session_start();
    require_once '../includes/db_connection.php';
    require_once '../includes/database_functions.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        returnError('Invalid request method');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $stamp_id = $input['stamp_id'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$stamp_id || !$user_id) {
        returnError('Missing stamp_id or user_id');
    }

    $current_date = date('Y-m-d');

    // デフォルト値を設定
    $frequency_type = $input['frequency_type'] ?? 'daily';
    $frequency_count = $input['frequency_count'] ?? 1;
    $duration = $input['duration'] ?? 30;
    $intermediate_goal_type = $input['intermediate_goal_type'] ?? 'week';
    $intermediate_goal_count = $input['intermediate_goal_count'] ?? 1;

    $conn->begin_transaction();

    $query = "INSERT INTO stamp_usage (user_id, stamp_id, start_date, frequency_type, frequency_count, duration, intermediate_goal_type, intermediate_goal_count) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $result = $stmt->bind_param("iissiisi", $user_id, $stamp_id, $current_date, $frequency_type, $frequency_count, $duration, $intermediate_goal_type, $intermediate_goal_count);

    if (!$result) {
        throw new Exception("Binding parameters failed: " . $stmt->error);
    }

    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Stamp used successfully']);
    } else {
        $conn->rollback();
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    returnError($e->getMessage());
}