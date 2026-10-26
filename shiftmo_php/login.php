<?php
session_start();

// ① ログイン用の固定データを読み込みます。
require_once 'DAO/LoginDAO.php';

// ② 入力されたメールとパスワードを確認します。
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    foreach ($_SESSION['employees'] as $employee) {
        if ($employee['email'] == $email && $employee['password'] == $password) {
            session_regenerate_id(true);
            $_SESSION['user'] = $employee;
            header('Location: home.php');
            exit;
        }
    }
    $error = 'メールアドレスまたはパスワードが違います。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - シフトモ</title>
    <link rel="stylesheet" href="CSS/login.css?v=20261026-3">
    <!-- 共通の見た目を最後に読み込みます。 -->
    <link rel="stylesheet" href="CSS/design.css?v=20261026-3">
</head>
<body class="login-page">
    <div class="login-card">
        <h1>シフトモ</h1>
        <p>Smart Shift Management System</p>
        <?php if ($error != '') { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        <form method="POST" action="login.php">
            <label>メールアドレス</label>
            <input type="email" name="email" required placeholder="manager@example.com">
            <label>パスワード</label>
            <input type="password" name="password" required>
            <button type="submit">ログイン</button>
        </form>
        <p><a href="reset.php">パスワードをお忘れですか？</a></p>
        <p class="hint">店長: manager@example.com / password123</p>
        <p class="hint">従業員: staff@example.com / password123</p>
    </div>
</body>
</html>
