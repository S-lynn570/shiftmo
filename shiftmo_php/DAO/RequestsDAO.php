<?php
// 希望シフトの登録と取り消しを行います。
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? 'add';

    // ① 新しい希望を登録します。
    if ($action == 'add') {
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && in_array($time, ['07:00-12:00','12:00-17:00','17:00-22:00'])) {
            $_SESSION['requests'][] = [
                'id' => uniqid('req_', true),
                'user' => $_SESSION['user']['id'],
                'date' => $date,
                'time' => $time,
                'status' => '申請中'
            ];
            $message = '希望シフトを登録しました。';
        }
    }

    // ② 自分の申請中の希望だけ取り消せます。
    if ($action == 'cancel') {
        $requestId = $_POST['id'] ?? '';
        foreach ($_SESSION['requests'] as $key => $request) {
            if (($request['id'] ?? '') == $requestId
                && $request['user'] == $_SESSION['user']['id']
                && $request['status'] == '申請中') {
                unset($_SESSION['requests'][$key]);
                $message = '希望シフトを取り消しました。';
                break;
            }
        }
    }
}

// 将来のデータベース処理例（現在は使いません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
