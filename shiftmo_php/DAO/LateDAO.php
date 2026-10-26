<?php
// 遅刻・欠勤の連絡を登録します。
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kind = $_POST['kind'] ?? '';
    $detail = trim($_POST['detail'] ?? '');
    if ($kind != '' && $detail != '') {
        $_SESSION['late'][] = ['user'=>$_SESSION['user']['id'], 'kind'=>$kind, 'detail'=>$detail, 'date'=>date('Y-m-d H:i')];
        $message = '連絡を送信しました。';
    }
}

// 将来のデータベース処理例（現在は使いません）
// $pdo = new PDO("mysql:host=localhost;dbname=shiftmo;charset=utf8mb4", "root", "");
