<?php
// ① セッションを開始します。
session_start();

// ② ログインしていない場合はログイン画面へ戻ります。
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ④ この画面専用のDAOを読み込みます。
require_once 'DAO/ShiftsDAO.php';

// ⑤ このページで表示するデータを用意します。
$shifts = $_SESSION['shifts'] ?? [];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>シフト表示 - シフトモ</title>
    <link rel="stylesheet" href="CSS/shifts.css?v=20261026-3">
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
        <a class="active" aria-current="page" href="shifts.php">シフト表示</a>
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
        <a href="attendance.php">出退勤・休憩登録</a>
        <a class="active" aria-current="page" href="shifts.php">シフト表示</a>
        <a href="requests.php">シフト希望登録</a>
        <a href="changes.php">変更申請</a>
        <a href="late.php">遅刻・連絡</a>
        <a href="profile.php">従業員情報</a>
        <?php } ?>
    </nav>
    <main class="main-content">
        <section class="panel">

            <h1>シフト表示</h1>
            <div class="view-tabs">
                <a href="shifts.php?view=month&date=<?php echo $date; ?>" class="<?php if ($view == 'month') echo 'active'; ?>">月表示</a>
                <a href="shifts.php?view=week&date=<?php echo $date; ?>" class="<?php if ($view == 'week') echo 'active'; ?>">週表示</a>
                <a href="shifts.php?view=day&date=<?php echo $date; ?>" class="<?php if ($view == 'day') echo 'active'; ?>">日表示</a>
            </div>
            <div class="month-control">
                <a href="shifts.php?view=<?php echo $view; ?>&date=<?php echo $previous; ?>">◀ 前へ</a>
                <strong>
                    <?php if ($view == 'month') { ?>
                        <?php echo date('Y年m月', $firstDay); ?>
                    <?php } elseif ($view == 'week') { ?>
                        <?php echo date('Y/m/d', $weekStart); ?> ～ <?php echo date('m/d', strtotime('+6 days', $weekStart)); ?>
                    <?php } else { ?>
                        <?php echo date('Y年m月d日', strtotime($date)); ?>
                    <?php } ?>
                </strong>
                <a href="shifts.php?view=<?php echo $view; ?>&date=<?php echo $next; ?>">次へ ▶</a>
            </div>

            <?php if ($view == 'month') { ?>
                <!-- 月表示：7列のカレンダー -->
                <div class="calendar-scroll"><div class="calendar">
                    <div class="weekday sunday">日</div><div class="weekday">月</div>
                    <div class="weekday">火</div><div class="weekday">水</div>
                    <div class="weekday">木</div><div class="weekday">金</div>
                    <div class="weekday saturday">土</div>
                    <?php for ($i = 0; $i < $startWeekday; $i++) { ?>
                        <div class="calendar-day empty"></div>
                    <?php } ?>
                    <?php for ($day = 1; $day <= $lastDay; $day++) { ?>
                        <?php $displayDate = $month . '-' . sprintf('%02d', $day); ?>
                        <div class="calendar-day">
                            <div class="day-number"><?php echo $day; ?></div>
                            <?php foreach ($shifts as $shift) { ?>
                                <?php if ($shift['date'] == $displayDate && $shift['status'] == '公開') { ?>
                                    <?php if ($_SESSION['user']['role'] == 'MANAGER' || $shift['emp'] == $_SESSION['user']['id']) { ?>
                                        <?php foreach ($_SESSION['employees'] ?? [] as $employee) { ?>
                                            <?php if ($employee['id'] == $shift['emp']) { ?>
                                                <div class="shift-item">
                                                    <?php echo htmlspecialchars($employee['name']); ?><br>
                                                    <?php echo htmlspecialchars($shift['time']); ?>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div></div>
            <?php } elseif ($view == 'week') { ?>
                <!-- 週表示：曜日ごとにシフトを表示します。 -->
                <div class="week-calendar">
                    <?php for ($i = 0; $i < 7; $i++) { ?>
                    <?php $displayDate = date('Y-m-d', strtotime('+' . $i . ' days', $weekStart)); ?>
                    <div class="week-day"><strong><?php echo date('n/j (D)', strtotime($displayDate)); ?></strong>
                        <?php foreach ($shifts as $shift) { ?>
                        <?php if ($shift['date'] == $displayDate && $shift['status'] == '公開' && ($_SESSION['user']['role'] == 'MANAGER' || $shift['emp'] == $_SESSION['user']['id'])) { ?>
                        <div class="shift-item"><?php foreach ($_SESSION['employees'] ?? [] as $employee) { if ($employee['id'] == $shift['emp']) echo htmlspecialchars($employee['name']); } ?><br><?php echo htmlspecialchars($shift['time']); ?></div>
                        <?php } ?>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <!-- 日表示：時間軸と横棒でシフトを表示します。 -->
                <div class="day-timeline"><div class="time-scale">
                <span>7:00</span><span>10:00</span><span>13:00</span><span>16:00</span><span>19:00</span><span>22:00</span>
                </div>
                <?php foreach ($shifts as $shift) { ?>
                <?php if ($shift['date'] == $date && $shift['status'] == '公開' && ($_SESSION['user']['role'] == 'MANAGER' || $shift['emp'] == $_SESSION['user']['id'])) { ?>
                    <?php $parts = explode('-', $shift['time']); $start = (int)substr($parts[0],0,2); $end = (int)substr($parts[1],0,2); ?>
                    <div class="timeline-row"><div class="timeline-name"><?php foreach ($_SESSION['employees'] ?? [] as $employee) { if ($employee['id'] == $shift['emp']) echo htmlspecialchars($employee['name']); } ?></div>
                    <div class="timeline-track"><div class="timeline-bar" style="left:<?php echo ($start - 7) / 15 * 100; ?>%;width:<?php echo ($end - $start) / 15 * 100; ?>%;"><?php echo htmlspecialchars($shift['time']); ?></div></div></div>
                <?php } ?>
                <?php } ?>
                </div>
            <?php } ?>
        </section>
    </main>
</div>
</body>
</html>
