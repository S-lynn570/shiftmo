<?php
// ① セッションを開始します。
session_start();

// ② ログインしていない場合はログイン画面へ戻ります。
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ④ この画面専用のDAOを読み込みます。
require_once 'DAO/HomeDAO.php';

// ⑤ このページで表示するデータを用意します。
$notices = [];
foreach (array_reverse($_SESSION['messages'] ?? []) as $notice) {
    // 最初のデモ用メッセージは表示しません。
    if (($notice['title'] ?? '') == 'ようこそ' && ($notice['body'] ?? '') == 'シフトモのデモ版へようこそ。') {
        continue;
    }
    // 全員通知か、自分宛ての通知だけを表示します。
    if (($notice['target'] ?? 'ALL') == 'ALL'
        || (string)$notice['target'] == (string)$_SESSION['user']['id']) {
        $notices[] = $notice;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ホーム - シフトモ</title>
    <link rel="stylesheet" href="CSS/home.css?v=20261026-3">
    <!-- 共通の見た目を最後に読み込みます。 -->
    <link rel="stylesheet" href="CSS/design.css?v=20261026-3">
</head>
<body>
<header class="topbar">
    <a class="logo" href="home.php">シフトモ</a>
    <div class="user-clock">
        <div class="user-name">ようこそ、<?php echo htmlspecialchars($_SESSION['user']['name']); ?>さん</div>
    </div>
    <a href="logout.php">ログアウト</a>
</header>
<div class="layout">
    <nav class="sidebar">
        <!-- 店長と従業員でメニューを分けます。 -->
        <?php if ($_SESSION['user']['role'] == 'MANAGER') { ?>
        <a class="active" aria-current="page" href="home.php">ホーム</a>
        <a href="attendance.php">出退勤・休憩登録</a>
        <a href="shifts.php">シフト表示</a>
        <a href="manage_shifts.php">シフト管理</a>
        <a href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="messages.php">メッセージ通知</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="departments.php">部署管理</a>
        <a href="employees.php">従業員管理</a>
        <a href="profile.php">従業員情報</a>
        <?php } else { ?>
        <a class="active" aria-current="page" href="home.php">ホーム</a>
        <a href="attendance.php">出退勤・休憩登録</a>
        <a href="shifts.php">シフト表示</a>
        <a href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="profile.php">従業員情報</a>
        <?php } ?>
    </nav>
    <main class="main-content">
        <section class="panel">

            <div class="home-clock"><span>現在日時</span><time class="current-time" id="currentTime">--:--:--</time></div>
            <h2>お知らせ</h2>
            <?php if (count($notices) == 0) { ?>
                <p class="empty-notice">現在、お知らせはありません。</p>
            <?php } ?>
            <?php foreach ($notices as $notice) { ?>
                <div class="notice">
                    <p class="muted"><?php echo htmlspecialchars($notice['date']); ?></p>
                    <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($notice['body'])); ?></p>
                </div>
            <?php } ?>

        </section>
    </main>
</div>
<script>
// パソコンの現在時刻を表示します。
function showTime() {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hour = String(now.getHours()).padStart(2, '0');
    var minute = String(now.getMinutes()).padStart(2, '0');
    var second = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('currentTime').textContent = year + '-' + month + '-' + day + '  ' + hour + ':' + minute + ':' + second;
}
showTime();
setInterval(showTime, 1000);
</script>
</body>
</html>
