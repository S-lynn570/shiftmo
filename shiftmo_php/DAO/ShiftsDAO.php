<?php
// ① 表示方法を選びます（月・週・日）。
$view = $_GET['view'] ?? 'month';
if (!in_array($view, ['month', 'week', 'day'])) {
    $view = 'month';
}

// ② 基準日を決めます。
$date = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || date('Y-m-d', strtotime($date)) != $date) {
    $date = date('Y-m-d');
}

// ③ 月表示で使う日付です。
$month = substr($date, 0, 7);
$firstDay = strtotime($month . '-01');
$lastDay = (int)date('t', $firstDay);
$startWeekday = (int)date('w', $firstDay);

// ④ 前後の期間を決めます。
if ($view == 'month') {
    $previous = date('Y-m-d', strtotime('-1 month', $firstDay));
    $next = date('Y-m-d', strtotime('+1 month', $firstDay));
} elseif ($view == 'week') {
    $weekStart = strtotime($date . ' -' . date('w', strtotime($date)) . ' days');
    $previous = date('Y-m-d', strtotime('-7 days', $weekStart));
    $next = date('Y-m-d', strtotime('+7 days', $weekStart));
} else {
    $previous = date('Y-m-d', strtotime('-1 day', strtotime($date)));
    $next = date('Y-m-d', strtotime('+1 day', strtotime($date)));
}

// 将来のデータベース処理例（現在は使いません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
