<?php
// 出退勤・休憩登録の処理です。
$message = '';

// 「登録」ボタンが押されたときだけ記録します。
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'] ?? '';
    $date = $_POST['date'] ?? '';
    $types = ['出勤', '退勤', '休憩開始', '休憩終了'];

    if (in_array($type, $types) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        // 日付はブラウザの対象日、時刻は登録ボタンを押した時刻です。
        $now = date('H:i:s');
        $_SESSION['attendance'][] = [
            'user' => $_SESSION['user']['id'],
            'type' => $type,
            'time' => $date . ' ' . $now
        ];
        $message = $type . 'を' . $now . 'に登録しました。';
    } else {
        $message = '区分を選択してください。';
    }
}

// 将来のデータベース接続例（現在は使用しません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
