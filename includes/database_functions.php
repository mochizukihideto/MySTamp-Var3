<?php
// includes/database_functions.php

// データベース接続を確保
require_once 'db_connection.php';

/**
 * ユーザーIDからユーザー情報を取得する関数
 * @param mysqli $conn データベース接続オブジェクト
 * @param int $user_id ユーザーID
 * @return array|null ユーザー情報の連想配列、見つからない場合はnull
 */
function get_user_by_id($conn, $user_id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * スタンプを削除する関数
 * @param int $stamp_id 削除するスタンプのID
 * @return bool 削除が成功したかどうか
 */
function deleteStamp($stamp_id) {
    global $conn;
    
    // トランザクション開始
    $conn->begin_transaction();

    try {
        // データベースからスタンプ情報を取得
        $sql = "SELECT image_path FROM stamps WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $stamp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stamp = $result->fetch_assoc();

        // データベースからスタンプを削除
        $sql = "DELETE FROM stamps WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $stamp_id);
        $stmt->execute();

        // 対応する画像ファイルを削除
        if ($stamp && file_exists($_SERVER['DOCUMENT_ROOT'] . $stamp['image_path'])) {
            if (!unlink($_SERVER['DOCUMENT_ROOT'] . $stamp['image_path'])) {
                throw new Exception("画像ファイルの削除に失敗しました。");
            }
        }

        // トランザクションをコミット
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // エラーが発生した場合、トランザクションをロールバック
        $conn->rollback();
        error_log("スタンプ削除エラー: " . $e->getMessage());
        return false;
    }
}

/**
 * ユーザーのスタンプを取得する関数
 * @param mysqli $conn データベース接続オブジェクト
 * @param int $user_id ユーザーID
 * @return array ユーザーのスタンプ情報の配列
 */
function get_user_stamps($conn, $user_id) {
    $sql = "SELECT * FROM stamps WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * スタンプ使用を更新または挿入する関数
 * @param mysqli $conn データベース接続オブジェクト
 * @param int $user_id ユーザーID
 * @param int $stamp_id スタンプID
 * @param string $date 使用日
 * @return bool 操作が成功したかどうか
 */
function update_stamp_usage($conn, $user_id, $stamp_id, $date) {
    $sql = "INSERT INTO stamp_usage (user_id, stamp_id, usage_date) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE usage_date = VALUES(usage_date)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $stamp_id, $date);
    return $stmt->execute();
}
function record_stamp_usage($conn, $user_id, $stamp_id) {
    $date = date('Y-m-d');
    $sql = "INSERT INTO stamp_usage (user_id, stamp_id, usage_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $stamp_id, $date);
    return $stmt->execute();
}

// 必要に応じて、他の関数をここに追加してください

?>