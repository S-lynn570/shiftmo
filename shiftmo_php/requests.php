<?php
// ① セッションを開始します。
session_start();

// ② ログインしていない場合はログイン画面へ戻ります。
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ④ この画面専用のDAOを読み込みます。
require_once 'DAO/RequestsDAO.php';

// ⑤ このページで表示するデータを用意します。
$requests = $_SESSION['requests'] ?? [];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>シフト希望登録 - シフトモ</title>
    <link rel="stylesheet" href="CSS/requests.css?v=20261026-3">
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
        <a class="active" aria-current="page" href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="messages.php">メッセージ通知</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="departments.php">部署管理</a>
        <a href="employees.php">従業員管理</a>
        <a href="profile.php">従業員情報</a>
        <?php } else { ?>
        <a href="home.php">ホーム</a>
        <a href="attendance.php">出退勤・休憩登録</a>
        <a href="shifts.php">シフト表示</a>
        <a class="active" aria-current="page" href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="profile.php">従業員情報</a>
        <?php } ?>
    </nav>
    <main class="main-content">
        <section class="panel">

            <h1>シフト希望登録</h1>
            <?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>
            <form method="POST"><input type="hidden" name="action" value="add"><label>希望日</label><input type="date" name="date" required><label>希望時間</label><select name="time"><option>07:00-12:00</option><option>12:00-17:00</option><option>17:00-22:00</option></select><p><button>希望を登録</button></p></form>
            <h2>希望一覧</h2><table><tr><th>名前</th><th>希望日</th><th>時間</th><th>状態</th><th>操作</th></tr>
            <?php foreach ($requests as $request) { ?>
                <?php if ($request['user'] == $_SESSION['user']['id']) { ?>
                    <tr><td><?php echo htmlspecialchars($_SESSION['user']['name']); ?></td><td><?php echo htmlspecialchars($request['date']); ?></td><td><?php echo htmlspecialchars($request['time']); ?></td><td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td>
                        <?php if ($request['user'] == $_SESSION['user']['id'] && $request['status'] == '申請中') { ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($request['id'] ?? ''); ?>">
                            <button type="submit" class="cancel-button">取消</button>
                        </form>
                        <?php } ?>
                    </td></tr>
                <?php } ?>
            <?php } ?></table>

        </section>
    </main>
</div>
</body>
</html>
