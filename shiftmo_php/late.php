<?php
// ① セッションを開始します。
session_start();

// ② ログインしていない場合はログイン画面へ戻ります。
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ④ この画面専用のDAOを読み込みます。
require_once 'DAO/LateDAO.php';

// ⑤ このページで表示するデータを用意します。
$lateList = $_SESSION['late'] ?? [];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>遅刻・連絡 - シフトモ</title>
    <link rel="stylesheet" href="CSS/late.css?v=20261026-3">
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
        <a class="active" aria-current="page" href="late.php">遅刻・連絡</a>
        <a href="departments.php">部署管理</a>
        <a href="employees.php">従業員管理</a>
        <a href="profile.php">従業員情報</a>
        <?php } else { ?>
        <a href="home.php">ホーム</a>
        <a href="attendance.php">出退勤・休憩登録</a>
        <a href="shifts.php">シフト表示</a>
        <a href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a class="active" aria-current="page" href="late.php">遅刻・連絡</a>
        <a href="profile.php">従業員情報</a>
        <?php } ?>
    </nav>
    <main class="main-content">
        <section class="panel">

            <h1>遅刻・連絡</h1>
            <?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>
            <form method="POST"><label>連絡区分</label><select name="kind"><option>電車遅延</option><option>遅刻</option><option>その他</option></select><label>到着予定・理由</label><textarea name="detail" required></textarea><p><button>連絡を送信</button></p></form>
            <h2>連絡履歴</h2><table><tr><th>日時</th><th>区分</th><th>内容</th></tr>
            <?php foreach ($lateList as $item) { ?>
                <?php if ($_SESSION['user']['role'] == 'MANAGER' || $item['user'] == $_SESSION['user']['id']) { ?>
                    <tr><td><?php echo htmlspecialchars($item['date']); ?></td><td><?php echo htmlspecialchars($item['kind']); ?></td><td><?php echo htmlspecialchars($item['detail']); ?></td></tr>
                <?php } ?>
            <?php } ?></table>

        </section>
    </main>
</div>
</body>
</html>
