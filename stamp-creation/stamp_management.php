<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/database_functions.php';

// ユーザーがログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: ../registration/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// スタンプ削除のリクエストを処理
if (isset($_POST['delete_stamp'])) {
    $stamp_id = $_POST['stamp_id'];
    if (deleteStamp($stamp_id)) {
        $message = "スタンプが正常に削除されました。";
    } else {
        $error = "スタンプの削除中にエラーが発生しました。";
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタンプ管理</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>スタンプ管理</h1>
        <nav>
            <ul>
                <li><a href="index.php">スタンプ作成</a></li>
                <li><a href="../index.php">ホーム</a></li>
                <li><a href="../registration/logout.php">ログアウト</a></li>
            </ul>
        </nav>

        <?php
        // メッセージがあれば表示
        if (isset($message)) {
            echo "<p class='success'>$message</p>";
        }
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }

        // スタンプ一覧の表示
        $sql = "SELECT * FROM stamps WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div class='stamp-list'>";
            while ($stamp = $result->fetch_assoc()) {
                echo "<div class='stamp-item'>";
                echo "<img src='" . htmlspecialchars($stamp['image_path']) . "' alt='Stamp' width='100'>";
                echo "<p>" . htmlspecialchars($stamp['hobby']) . "</p>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='stamp_id' value='" . $stamp['id'] . "'>";
                echo "<input type='submit' name='delete_stamp' value='削除' onclick='return confirm(\"このスタンプを削除しますか？\");'>";
                echo "</form>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>スタンプがありません。</p>";
        }
        ?>
    </div>
</body>
</html>