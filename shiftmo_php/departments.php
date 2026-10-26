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
require_once 'DAO/DepartmentsDAO.php';

// ⑤ このページで表示するデータを用意します。
$departments = $_SESSION['departments'] ?? [];
$employees = $_SESSION['employees'] ?? [];
$page = $_GET['page'] ?? 'menu';
$selectedDept = $_GET['dept'] ?? '';

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>部署管理 - シフトモ</title>
    <link rel="stylesheet" href="CSS/departments.css?v=20261026-3">
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
        <a class="active" aria-current="page" href="departments.php">部署管理</a>
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
<h1>部署管理</h1>
<?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>
<?php if ($page == 'menu') { ?>
<div class="menu-grid"><a class="design-button" href="departments.php?page=add">部署管理登録</a><a class="design-button" href="departments.php?page=assign">従業員の部署設定</a><a class="design-button" href="departments.php?page=list">部署別の従業員確認</a></div>
<?php } elseif ($page == 'add') { ?>
<h2>部署管理登録</h2><form method="POST"><input type="hidden" name="action" value="add"><label>部署名<input name="name" required></label><div class="design-actions"><a class="design-button" href="departments.php">戻る</a><button>登録</button></div></form>
<h3>登録済みの部署</h3><table><tr><th>ID</th><th>部署名</th></tr><?php foreach ($departments as $number => $dept) { ?><tr><td><?php echo $number+1; ?></td><td><?php echo htmlspecialchars($dept); ?></td></tr><?php } ?></table>
<?php } elseif ($page == 'assign') { ?>
<h2>従業員の部署設定</h2><form method="POST"><label>従業員ID<select name="emp" id="emp-select">
<?php foreach ($employees as $employee) { ?><option value="<?php echo $employee['id']; ?>"><?php echo $employee['id'].' / '.htmlspecialchars($employee['name']); ?></option><?php } ?></select></label>
<label>所属部署<select name="dept"><?php foreach ($departments as $dept) { ?><option><?php echo htmlspecialchars($dept); ?></option><?php } ?></select></label>
<div class="design-actions"><a class="design-button" href="departments.php">戻る</a><button name="action" value="remove">削除</button><button name="action" value="save">保存</button></div></form>
<h3>現在の所属部署</h3><table><tr><th>従業員ID</th><th>名前</th><th>所属部署</th></tr><?php foreach ($employees as $employee) { ?><tr><td><?php echo $employee['id']; ?></td><td><?php echo htmlspecialchars($employee['name']); ?></td><td><?php echo htmlspecialchars($employee['dept']); ?></td></tr><?php } ?></table>
<?php } elseif ($page == 'list') { ?>
<h2>部署別の従業員確認</h2><form method="GET"><input type="hidden" name="page" value="list"><label>所属部署<select name="dept" onchange="this.form.submit()"><option value="">すべて</option><?php foreach ($departments as $dept) { ?><option value="<?php echo htmlspecialchars($dept); ?>" <?php if ($dept == $selectedDept) { echo 'selected'; } ?>><?php echo htmlspecialchars($dept); ?></option><?php } ?></select></label></form>
<div class="table-scroll"><table><tr><th>従業員ID</th><th>名前</th><th>所属部署</th></tr><?php foreach ($employees as $employee) { if ($selectedDept == '' || $selectedDept == $employee['dept']) { ?><tr><td><?php echo $employee['id']; ?></td><td><?php echo htmlspecialchars($employee['name']); ?></td><td><?php echo htmlspecialchars($employee['dept']); ?></td></tr><?php } } ?></table></div>
<p><a class="design-button" href="departments.php">戻る</a></p>
<?php } ?>
</section>
    </main>
</div>
</body>
</html>
