<?php
// ① セッションを開始します。
session_start();

// ② ログインしていない場合はログイン画面へ戻ります。
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ④ この画面専用のDAOを読み込みます。
require_once 'DAO/ChangesDAO.php';

// ⑤ このページで表示するデータを用意します。
$changes = $_SESSION['changes'] ?? [];
$shifts = $_SESSION['shifts'] ?? [];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>シフト変更申請 - シフトモ</title>
    <link rel="stylesheet" href="CSS/changes.css?v=20261026-3">
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
        <a class="active" aria-current="page" href="changes.php">変更申請</a>
        <a href="messages.php">メッセージ通知</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="departments.php">部署管理</a>
        <a href="employees.php">従業員管理</a>
        <a href="profile.php">従業員情報</a>
        <?php } else { ?>
        <a href="home.php">ホーム</a>
        <a href="attendance.php">出退勤・休憩登録</a>
        <a href="shifts.php">シフト表示</a>
        <a href="requests.php">シフト希望登録</a>
        <a class="active" aria-current="page" href="changes.php">変更申請</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="profile.php">従業員情報</a>
        <?php } ?>
    </nav>
    <main class="main-content">
        <section class="panel">
<h1>シフト変更申請</h1>
<?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>
<h2>変更前（公開シフトから選択）</h2>
<form method="POST"><input type="hidden" name="action" value="request">
<label>自分の公開シフト<select name="shift_id" required>
<option value="">選択してください</option>
<?php foreach ($shifts as $shift) { if ($shift['emp'] == $_SESSION['user']['id'] && $shift['status'] == '公開') { ?><option value="<?php echo htmlspecialchars((string)$shift['id']); ?>"><?php echo htmlspecialchars($shift['date'].' / '.$shift['time']); ?></option><?php } } ?></select></label>
<h2>希望時間</h2><div class="design-row"><label>変更希望日<input type="date" name="new_date" required></label><label>時間帯<select name="new_time"><option>07:00-12:00</option><option>12:00-17:00</option><option>17:00-22:00</option></select></label></div>
<div class="design-actions"><a class="design-button" href="home.php">戻る</a><button>確定</button></div></form>
<h2>現在の変更申請一覧</h2><table><tr><th>名前</th><th>希望日</th><th>時間帯</th><th>状態</th></tr>
<?php foreach ($changes as $change) { if ($change['user'] == $_SESSION['user']['id']) { ?><tr><td><?php echo htmlspecialchars($_SESSION['user']['name']); ?></td><td><?php echo htmlspecialchars($change['new_date']); ?></td><td><?php echo htmlspecialchars($change['new_time']); ?></td><td><?php echo htmlspecialchars($change['status']); ?></td></tr><?php } } ?></table>
</section>
    </main>
</div>
</body>
</html>
