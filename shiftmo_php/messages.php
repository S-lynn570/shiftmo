<?php
// ① セッションを開始します。
session_start();

// ② ログインしていない場合はログイン画面へ戻ります。
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ③ 店長だけが開ける画面です。
if ($_SESSION['user']['role'] != 'MANAGER') {
    header('Location: home.php');
    exit;
}

// ④ この画面専用のDAOを読み込みます。
require_once 'DAO/MessagesDAO.php';

// ⑤ このページで表示するデータを用意します。


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メッセージ通知 - シフトモ</title>
    <link rel="stylesheet" href="CSS/messages.css?v=20261026-3">
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
        <a class="active" aria-current="page" href="messages.php">メッセージ通知</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="departments.php">部署管理</a>
        <a href="employees.php">従業員管理</a>
        <a href="profile.php">従業員情報</a>
        <?php } else { ?>
        <a href="home.php">ホーム</a>
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

            <h1>メッセージ通知</h1>
            <?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>
            <?php if ($error != '') { ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php } ?>
            <h2>通知を作成する</h2>
            <form method="POST">
                <label>送信先</label>
                <select name="target">
                    <option value="ALL">全員に通知</option>
                    <?php foreach ($_SESSION['employees'] ?? [] as $employee) { ?>
                        <option value="<?php echo $employee['id']; ?>">
                            <?php echo htmlspecialchars($employee['name'] . '（' . $employee['email'] . '）'); ?>
                        </option>
                    <?php } ?>
                </select>
                <label>題名</label>
                <input name="title" required>
                <label>内容</label>
                <textarea name="body" required></textarea>
                <p><button type="submit">通知を登録</button></p>
                <p class="hint">個人通知は指定した従業員だけのホームに表示されます。メール送信はSMTP設定後に利用できます。</p>
            </form>

        </section>
    </main>
</div>
</body>
</html>
