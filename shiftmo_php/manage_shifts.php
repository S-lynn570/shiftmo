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
require_once 'DAO/ManageShiftsDAO.php';

// ⑤ このページで表示するデータを用意します。
$page = $_GET['page'] ?? 'menu';
$pageTitles = ['menu' => 'シフト管理', 'edit' => 'シフト編集', 'publish' => 'シフト公開', 'adjust' => 'シフト調整依頼一覧'];
if (!isset($pageTitles[$page])) { $page = 'menu'; }
$shifts = $_SESSION['shifts'] ?? [];
$employees = $_SESSION['employees'] ?? [];
$requests = $_SESSION['requests'] ?? [];
$changes = $_SESSION['changes'] ?? [];

// 編集フォームの初期値です。
$selectedEmp = $_SESSION['user']['id'];
$selectedDate = date('Y-m-d');
$selectedTime = '07:00-12:00';
$selectedShiftId = '';
$selectedChangeId = '';
$selectedRequestId = '';

// 希望一覧の「シフト確定」から来た場合です。
foreach ($requests as $request) {
    if (($request['id'] ?? '') == ($_GET['request'] ?? '') && ($request['status'] ?? '') == '申請中') {
        $selectedEmp = ($request['user'] ?? '');
        $selectedDate = ($request['date'] ?? '');
        $selectedTime = ($request['time'] ?? '');
        $selectedRequestId = ($request['id'] ?? '');
    }
}

// 公開画面の「編集」から来た場合です。
foreach ($shifts as $shift) {
    if (($shift['id'] ?? '') == ($_GET['shift'] ?? '')) {
        $selectedEmp = ($shift['emp'] ?? '');
        $selectedDate = ($shift['date'] ?? '');
        $selectedTime = ($shift['time'] ?? '');
        $selectedShiftId = ($shift['id'] ?? '');
    }
}

// 調整依頼の「承認」から来た場合です。
foreach ($changes as $change) {
    if (($change['id'] ?? '') == ($_GET['change'] ?? '') && ($change['status'] ?? '') == '申請中') {
        foreach ($shifts as $shift) {
            if (($shift['id'] ?? '') == ($change['shift_id'] ?? '') && ($shift['emp'] ?? '') == ($change['user'] ?? '')) {
                $selectedEmp = ($change['user'] ?? '');
                $selectedDate = ($change['new_date'] ?? '');
                $selectedTime = ($change['new_time'] ?? '');
                $selectedShiftId = ($shift['id'] ?? '');
                $selectedChangeId = ($change['id'] ?? '');
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitles[$page]); ?> - シフトモ</title>
    <link rel="stylesheet" href="CSS/manage_shifts.css?v=20261026-3">
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
        <a href="manage_shifts.php" aria-current="page">シフト管理</a>
        <a href="requests.php">シフト希望登録</a>
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
        <a href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="profile.php">従業員情報</a>
        <?php } ?>
    </nav>
    <main class="main-content">
        <section class="panel">
<?php if ($page != 'menu') { ?>
<nav class="breadcrumbs" aria-label="現在の位置"><a class="active" aria-current="page" href="manage_shifts.php">シフト管理</a><span aria-hidden="true"> / </span><span><?php echo htmlspecialchars($pageTitles[$page]); ?></span></nav>
<?php } ?>
<h1><?php echo htmlspecialchars($pageTitles[$page]); ?></h1>

<?php if ($message != '') { ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php } ?>

<?php if ($page == 'menu') { ?>
    <div class="menu-grid">
        <a class="design-button" href="requests.php">シフト希望登録</a>
        <a class="design-button" href="manage_shifts.php?page=edit">シフト編集</a>
        <a class="design-button" href="manage_shifts.php?page=publish">シフト公開</a>
        <a class="design-button" href="manage_shifts.php?page=adjust">シフト調整依頼一覧</a>
    </div>
<?php } ?>

<?php if ($page == 'edit') { ?>
    <p>希望がない従業員も、名前を選んでシフトを追加できます。</p>
    <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="shift_id" value="<?php echo htmlspecialchars($selectedShiftId); ?>">
        <input type="hidden" name="change_id" value="<?php echo htmlspecialchars($selectedChangeId); ?>">
        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($selectedRequestId); ?>">
        <div class="design-row">
            <label>名前
                <select name="emp" required>
                    <?php foreach ($employees as $employee) { ?>
                        <option value="<?php echo ($employee['id'] ?? ''); ?>" <?php if ($selectedEmp == ($employee['id'] ?? '')) { echo 'selected'; } ?>><?php echo htmlspecialchars(($employee['name'] ?? '')); ?></option>
                    <?php } ?>
                </select>
            </label>
            <label>日付 <input type="date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>" required></label>
            <label>時間帯
                <select name="time">
                    <?php foreach (['07:00-12:00', '12:00-17:00', '17:00-22:00'] as $time) { ?>
                        <option value="<?php echo $time; ?>" <?php if ($selectedTime == $time) { echo 'selected'; } ?>><?php echo $time; ?></option>
                    <?php } ?>
                </select>
            </label>
        </div>
        <div class="design-actions"><a class="design-button" href="manage_shifts.php">戻る</a><button type="submit">シフト確定・保存</button></div>
    </form>

    <h3>提出された希望登録申請一覧</h3>
    <div class="table-scroll"><table>
        <tr><th>名前</th><th>日付</th><th>時間帯</th><th>削除</th><th>シフト確定</th></tr>
        <?php foreach ($requests as $request) { ?>
            <?php if (($request['status'] ?? '') == '申請中') { ?>
                <tr>
                    <td><?php foreach ($employees as $employee) { if (($employee['id'] ?? '') == ($request['user'] ?? '')) { echo htmlspecialchars(($employee['name'] ?? '')); } } ?></td>
                    <td><?php echo htmlspecialchars(($request['date'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars(($request['time'] ?? '')); ?></td>
                    <td><form method="POST"><input type="hidden" name="action" value="delete_request"><input type="hidden" name="request_id" value="<?php echo htmlspecialchars(($request['id'] ?? '')); ?>"><button type="submit">削除</button></form></td>
                    <td><a class="design-button" href="manage_shifts.php?page=edit&amp;request=<?php echo urlencode(($request['id'] ?? '')); ?>">シフト確定</a></td>
                </tr>
            <?php } ?>
        <?php } ?>
    </table></div>

    <h3>作成したシフト一覧</h3>
    <div class="table-scroll"><table>
        <tr><th>名前</th><th>日付</th><th>勤務時間</th><th>編集</th><th>削除</th></tr>
        <?php foreach ($shifts as $shift) { ?>
            <tr>
                <td><?php foreach ($employees as $employee) { if (($employee['id'] ?? '') == ($shift['emp'] ?? '')) { echo htmlspecialchars(($employee['name'] ?? '')); } } ?></td>
                <td><?php echo htmlspecialchars(($shift['date'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars(($shift['time'] ?? '')); ?></td>
                <td><a href="manage_shifts.php?page=edit&amp;shift=<?php echo urlencode(($shift['id'] ?? '')); ?>">編集</a></td>
                <td><form method="POST"><input type="hidden" name="action" value="delete_shift"><input type="hidden" name="shift_id" value="<?php echo htmlspecialchars(($shift['id'] ?? '')); ?>"><button type="submit">削除</button></form></td>
            </tr>
        <?php } ?>
    </table></div>
<?php } ?>

<?php if ($page == 'publish') { ?>
    <p>シフトを確認してください。修正する場合は「編集」を押してください。</p>
    <div class="table-scroll"><table>
        <tr><th>名前</th><th>日付</th><th>勤務時間</th><th>編集</th></tr>
        <?php foreach ($shifts as $shift) { ?>
            <tr>
                <td><?php foreach ($employees as $employee) { if (($employee['id'] ?? '') == ($shift['emp'] ?? '')) { echo htmlspecialchars(($employee['name'] ?? '')); } } ?></td>
                <td><?php echo htmlspecialchars(($shift['date'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars(($shift['time'] ?? '')); ?></td>
                <td><a class="design-button" href="manage_shifts.php?page=edit&amp;shift=<?php echo urlencode(($shift['id'] ?? '')); ?>">編集</a></td>
            </tr>
        <?php } ?>
    </table></div>
    <form method="POST" class="design-actions">
        <a class="design-button" href="manage_shifts.php">戻る</a>
        <button type="submit" name="action" value="publish" <?php if (count($shifts) == 0) { echo 'disabled'; } ?>>公開</button>
    </form>
<?php } ?>

<?php if ($page == 'adjust') { ?>
    <p>「承認」を押すとシフト編集画面に進みます。保存したあとにシフトを再公開してください。</p>
    <div class="adjust-table-scroll">
        <table class="adjust-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>休暇日</th>
                    <th>対象日・変更前</th>
                    <th>変更内容</th>
                    <th>状態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($changes) == 0) { ?>
                <tr><td colspan="6">現在、調整依頼はありません。</td></tr>
            <?php } ?>
            <?php foreach ($changes as $change) { ?>
                <?php
                // 従業員名を調べます。
                $changeName = '不明';
                foreach ($employees as $employee) {
                    if (($employee['id'] ?? '') == ($change['user'] ?? '')) {
                        $changeName = $employee['name'] ?? '不明';
                    }
                }

                // 変更前のシフトを調べます。
                $beforeDate = '';
                $beforeTime = '';
                $hasShift = false;
                foreach ($shifts as $shift) {
                    if (($shift['id'] ?? '') == ($change['shift_id'] ?? '') && ($shift['emp'] ?? '') == ($change['user'] ?? '')) {
                        $beforeDate = $shift['date'] ?? '';
                        $beforeTime = $shift['time'] ?? '';
                        $hasShift = true;
                        break;
                    }
                }
                $changeId = (string)($change['id'] ?? '');
                $pending = ($change['status'] ?? '') == '申請中';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($changeName); ?></td>
                    <td><?php echo htmlspecialchars($change['leave_date'] ?? $beforeDate ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars(trim($beforeDate . ' ' . $beforeTime) ?: '対象シフトなし'); ?></td>
                    <td><?php echo htmlspecialchars(trim(($change['new_date'] ?? '') . ' ' . ($change['new_time'] ?? '')) ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($change['status'] ?? '—'); ?></td>
                    <td>
                        <div class="adjust-buttons">
                            <?php if ($pending && $hasShift && $changeId != '') { ?>
                                <a class="adjust-button" href="manage_shifts.php?page=edit&amp;change=<?php echo urlencode($changeId); ?>">承認</a>
                            <?php } elseif ($pending) { ?>
                                <span class="adjust-disabled">承認不可</span>
                            <?php } ?>
                            <?php if ($pending && $changeId != '') { ?>
                                <form method="POST" class="adjust-form">
                                    <input type="hidden" name="change_id" value="<?php echo htmlspecialchars($changeId); ?>">
                                    <button class="adjust-button" type="submit" name="action" value="reject">拒否</button>
                                </form>
                            <?php } ?>
                            <?php if (!$pending) { ?>—<?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <p><a class="design-button" href="manage_shifts.php">戻る</a></p>
<?php } ?>
</section>
    </main>
</div>
</body>
</html>
