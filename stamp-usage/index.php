<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/db_connection.php';
require_once '../includes/database_functions.php';

// デバッグ情報
error_log("Session data: " . print_r($_SESSION, true));
error_log("User ID: " . ($_SESSION['user_id'] ?? 'Not set'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    error_log("Session not active");
}

// ユーザーがログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: ../registration/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($conn, $user_id);
if (!$user) {
    error_log("User not found for ID: $user_id");
    // エラー処理（例：エラーページへのリダイレクト）
    header("Location: ../error.php");
    exit();
}

$stamps = get_user_stamps($conn, $user_id);
if ($stamps === false) {
    error_log("Failed to get stamps for user ID: $user_id");
    // エラー処理
    $stamps = []; // 空の配列を設定して続行
}

$new_stamp_created = false;
$new_stamp_id = null;
if (isset($_SESSION['new_stamp_created']) && $_SESSION['new_stamp_created']) {
    $new_stamp_created = true;
    $new_stamp_id = $_SESSION['new_stamp_id'];
    unset($_SESSION['new_stamp_created']);
    unset($_SESSION['new_stamp_id']);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタンプ使用ページ - <?php echo htmlspecialchars($user['name']); ?>さん</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header-right">
        <a href="../my-calendar/index.php">My Calendar</a>
    </div>

    <h1><?php echo htmlspecialchars($user['name']); ?>さん、今日はどれを頑張りましたか？</h1>
    
    <?php if ($new_stamp_created): ?>
        <div class="alert alert-success">新しいスタンプが作成されました！</div>
    <?php endif; ?>

    <div id="stamps-container">
        <?php 
        $stamps = array_reverse($stamps);
        foreach ($stamps as $stamp): 
            $full_path = $_SERVER['DOCUMENT_ROOT'] . '/lesson-management-system' . $stamp['image_path'];
        ?>
            <div class="stamp <?php echo ($stamp['id'] == $new_stamp_id) ? 'new-stamp' : ''; ?>" data-stamp-id="<?php echo $stamp['id']; ?>">
                <img src="<?php echo htmlspecialchars('/lesson-management-system' . $stamp['image_path']); ?>" alt="<?php echo htmlspecialchars($stamp['hobby']); ?>">
                <div class="stamp-info">
                    <p><?php echo htmlspecialchars($stamp['hobby']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="use-stamp.js"></script>
</body>
</html>