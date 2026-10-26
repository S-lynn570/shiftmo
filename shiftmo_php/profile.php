<?php
// ① セッションを開始します。
session_start();

// ② ログインしていない場合はログイン画面へ戻ります。
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ④ この画面専用のDAOを読み込みます。
require_once 'DAO/ProfileDAO.php';

// ⑤ このページで表示するデータを用意します。
$profile = $_SESSION['user'];
foreach ($_SESSION['employees'] ?? [] as $employee) {
    if ($employee['id'] == $_SESSION['user']['id']) {
        $profile = $employee;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>従業員情報 - シフトモ</title>
    <link rel="stylesheet" href="CSS/profile.css?v=20261026-3">
    <!-- 共通の見た目を最後に読み込みます。 -->
    <link rel="stylesheet" href="CSS/design.css?v=20261026-3">
</head>
<body>
<header class="topbar">
    <a class="logo" href="home.php">シフトモ</a>
    <span><?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
    <a href="logout.php">ログアウト</a>
</header>
<div class="layout">
    <nav class="sidebar">
        <!-- 店長と従業員でメニューを分けます。 -->
        <?php if ($_SESSION['user']['role'] == 'MANAGER') { ?>
        <a href="home.php">ホーム</a>
        <a href="attendance.php">出退勤・休憩登録</a>
        <a href="shifts.php">シフト表示</a>
        <a href="manage_shifts.php">シフト管理</a>
        <a href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="messages.php">メッセージ通知</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="departments.php">部署管理</a>
        <a href="employees.php">従業員管理</a>
        <a class="active" aria-current="page" href="profile.php">従業員情報</a>
        <?php } else { ?>
        <a href="home.php">ホーム</a>
        <a href="attendance.php">出退勤・休憩登録</a>
        <a href="shifts.php">シフト表示</a>
        <a href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="late.php">遅刻・連絡</a>
        <a class="active" aria-current="page" href="profile.php">従業員情報</a>
        <?php } ?>
    </nav>
    <main class="main-content">
        <section class="panel">

            <h1>従業員情報</h1>
            <div class="table-scroll"><table><tr><th>従業員ID</th><td><?php echo $profile['id']; ?></td></tr>
            <tr><th>氏名</th><td><?php echo htmlspecialchars($profile['name']); ?></td></tr>
            <tr><th>部署</th><td><?php echo htmlspecialchars($profile['dept']); ?></td></tr>
            <tr><th>メール</th><td><?php echo htmlspecialchars($profile['email']); ?></td></tr>
            <tr><th>電話番号</th><td><?php echo htmlspecialchars($profile['phone']); ?></td></tr></table></div>

        </section>
    </main>
</div>
</body>
</html>
