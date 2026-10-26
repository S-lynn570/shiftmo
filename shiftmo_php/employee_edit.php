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
$page = 'edit';
require_once 'DAO/EmployeesDAO.php';

// ⑤ このページで表示するデータを用意します。


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>従業員編集 - シフトモ</title>
    <link rel="stylesheet" href="CSS/employees.css?v=20261026-3">
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
        <a class="active" aria-current="page" href="employees.php">従業員管理</a>
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

            <h1>従業員編集</h1>

            <?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>
            <?php if ($error != '') { ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php } ?>

            <?php if ($editEmployee != null) { ?>
            <p><strong>従業員ID：<?php echo htmlspecialchars((string)$editId); ?></strong></p>
            <form method="POST" action="employee_edit.php?id=<?php echo $editId; ?>">
                <input type="hidden" name="action" value="update">

                <label for="employee-name">従業員名</label>
                <input id="employee-name" name="name" value="<?php echo htmlspecialchars($form['name']); ?>" required>
                <label for="employee-phone">連絡先</label>
                <input id="employee-phone" type="tel" name="phone" value="<?php echo htmlspecialchars($form['phone']); ?>" required>
                <label for="employee-email">E-mail</label>
                <input id="employee-email" type="email" name="email" value="<?php echo htmlspecialchars($form['email']); ?>" required>
                <label for="employee-dept">部署</label>
                <select id="employee-dept" name="dept">
                    <option value="">未所属</option>
                    <?php foreach ($departments as $dept) { ?>
                    <option value="<?php echo htmlspecialchars($dept); ?>" <?php if ($form['dept'] == $dept) { echo 'selected'; } ?>><?php echo htmlspecialchars($dept); ?></option>
                    <?php } ?>
                </select>

                <div class="design-actions"><a class="design-button" href="employee_list.php">戻る</a><button type="submit">保存</button></div>
            </form>
            <?php } else { ?>
            <a class="design-button" href="employee_list.php">従業員一覧へ戻る</a>
            <?php } ?>

        </section>
    </main>
</div>
</body>
</html>
