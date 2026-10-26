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
$page = 'list';
require_once 'DAO/EmployeesDAO.php';

// ⑤ このページで表示するデータを用意します。


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>従業員一覧 - シフトモ</title>
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

            <h1>従業員一覧</h1>

            <?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>
            <?php if ($error != '') { ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php } ?>

            <form method="GET" action="employee_list.php" class="employee-search">
                <label for="employee-search">従業員ID・名前で検索</label>
                <div class="employee-search-row">
                    <input id="employee-search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ID、名前を入力してください">
                    <button type="submit">検索</button>
                    <a class="design-button" href="employee_list.php">すべて表示</a>
                </div>
            </form>
            <div class="table-scroll">
                <table>
                    <tr><th>従業員ID</th><th>氏名</th><th>部署</th><th>操作</th></tr>
                    <?php if (count($employees) == 0) { ?>
                    <tr><td colspan="4">該当する従業員はいません。</td></tr>
                    <?php } ?>
                    <?php foreach ($employees as $employee) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)$employee['id']); ?></td>
                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                        <td><?php echo htmlspecialchars($employee['dept'] ?: '未所属'); ?></td>
                        <td>
                            <div class="employee-row-actions">
                                <a class="design-button" href="employee_edit.php?id=<?php echo (int)$employee['id']; ?>">編集</a>
                                <form method="POST" action="employee_list.php" onsubmit="return confirm('この従業員を削除しますか？');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$employee['id']; ?>">
                                    <button type="submit" class="cancel-button">削除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="design-actions"><a class="design-button" href="employees.php">戻る</a></div>
            <?php if ($createdId != null) { ?>
            <script>
            // 登録できた従業員IDを一度だけ表示します。
            alert('従業員を登録しました。従業員ID：' + <?php echo (int)$createdId; ?>);
            </script>
            <?php } ?>

        </section>
    </main>
</div>
</body>
</html>
