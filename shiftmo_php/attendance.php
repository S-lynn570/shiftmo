<?php
// ① セッションを開始します。
session_start();

// ② ログインしていない場合はログイン画面へ戻ります。
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ④ この画面専用のDAOを読み込みます。
require_once 'DAO/AttendanceDAO.php';

// ⑤ このページで表示するデータを用意します。
$selectedDate = $_POST['date'] ?? date('Y-m-d');
$history = [];
foreach ($_SESSION['attendance'] ?? [] as $record) {
    if ($record['user'] == $_SESSION['user']['id']) {
        $history[] = $record;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出退勤・休憩登録 - シフトモ</title>
    <link rel="stylesheet" href="CSS/attendance.css?v=20261026-3">
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
        <a class="active" aria-current="page" href="attendance.php">出退勤・休憩登録</a>
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
        <a href="home.php">ホーム</a>
        <a class="active" aria-current="page" href="attendance.php">出退勤・休憩登録</a>
        <a href="shifts.php">シフト表示</a>
        <a href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="profile.php">従業員情報</a>
        <?php } ?>
    </nav>
    <main class="main-content">
        <section class="panel">
            <h1>出退勤・休憩登録</h1>
            
            <p class="welcome">ようこそ、<?php echo htmlspecialchars($_SESSION['user']['name']); ?>さん</p>
            <?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>
            <form method="POST">
                <div class="design-row">
                    <label>従業員名 <input value="<?php echo htmlspecialchars($_SESSION['user']['name']); ?>" readonly></label>
                    <label>対象日 <input type="date" id="local-date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>" readonly required></label>
                </div>
                <input type="hidden" name="type" id="attendance-type" value="">
                <div class="attendance-buttons" role="group" aria-label="登録区分">
                    <?php foreach (['出勤', '退勤', '休憩開始', '休憩終了'] as $type) { ?>
                    <button type="button" class="attendance-choice" data-type="<?php echo $type; ?>" aria-pressed="false"><?php echo $type; ?></button>
                    <?php } ?>
                </div>
                <table class="design-table">
                    <?php foreach (['出勤','退勤','休憩開始','休憩終了'] as $type) { ?>
                    <tr><th><?php echo $type; ?></th><td>
                    <?php foreach (array_reverse($history) as $record) { ?>
                        <?php if ($record['type'] == $type && substr($record['time'],0,10) == $selectedDate) { echo htmlspecialchars(substr($record['time'], 11, 8)); break; } ?>
                    <?php } ?>
                    </td></tr>
                    <?php } ?>
                </table>
                <div class="design-actions"><a class="design-button" href="home.php">戻る</a><button type="submit">登録</button></div>
            </form>
        </section>
    </main>
</div>
<script>
// 区分ボタンで選択し、登録ボタンで確定します。
var typeInput = document.getElementById('attendance-type');
var choices = document.querySelectorAll('.attendance-choice');
choices.forEach(function (button) {
    button.addEventListener('click', function () {
        typeInput.value = button.dataset.type;
        choices.forEach(function (choice) {
            choice.setAttribute('aria-pressed', choice === button ? 'true' : 'false');
        });
    });
});
typeInput.form.addEventListener('submit', function (event) {
    if (!typeInput.value) {
        event.preventDefault();
        alert('区分を選択してください。');
        choices[0].focus();
    }
});
// パソコンの現在の日付を対象日に表示します。
var today = new Date();
var year = today.getFullYear();
var month = String(today.getMonth() + 1).padStart(2, '0');
var day = String(today.getDate()).padStart(2, '0');
document.getElementById('local-date').value = year + '-' + month + '-' + day;
</script>
</body>
</html>
