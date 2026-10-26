<?php
// 公開された自分のシフトに対して変更を申請します。
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') == 'request') {
    $shiftId = $_POST['shift_id'] ?? '';
    $newDate = $_POST['new_date'] ?? '';
    $newTime = $_POST['new_time'] ?? '';
    $found = false;
    foreach ($_SESSION['shifts'] as $shift) {
        if ((string)$shift['id'] == $shiftId && $shift['emp'] == $_SESSION['user']['id'] && $shift['status'] == '公開') {
            $found = true;
        }
    }
    if ($found && preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate) && in_array($newTime, ['07:00-12:00','12:00-17:00','17:00-22:00'])) {
        $_SESSION['changes'][] = ['id'=>uniqid('chg_'), 'user'=>$_SESSION['user']['id'], 'shift_id'=>$shiftId, 'new_date'=>$newDate, 'new_time'=>$newTime, 'status'=>'申請中'];
        $message = 'シフト変更を申請しました。';
    } else { $message = '公開シフトと入力内容を確認してください。'; }
}
// 将来のDB接続例（今は使用しません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
