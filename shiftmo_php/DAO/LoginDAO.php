<?php
// ログイン画面で使う固定データです。
// 今はデータベースに接続しません。
if (!isset($_SESSION['employees'])) {
    $_SESSION['employees'] = [
        ['id'=>1, 'name'=>'店長 太郎', 'email'=>'manager@example.com', 'password'=>'password123', 'role'=>'MANAGER', 'dept'=>'ホール', 'phone'=>'090-0000-0001'],
        ['id'=>2, 'name'=>'日本 電子', 'email'=>'staff@example.com', 'password'=>'password123', 'role'=>'STAFF', 'dept'=>'ホール', 'phone'=>'090-0000-0002'],
        ['id'=>3, 'name'=>'山田 花子', 'email'=>'yamada@example.com', 'password'=>'password123', 'role'=>'STAFF', 'dept'=>'ホール', 'phone'=>'090-0000-0003'],
        ['id'=>4, 'name'=>'岡田 真一', 'email'=>'okada@example.com', 'password'=>'password123', 'role'=>'STAFF', 'dept'=>'キッチン', 'phone'=>'090-0000-0004']
    ];
    $_SESSION['departments'] = ['ホール', 'キッチン'];
    $_SESSION['messages'] = [['date'=>date('Y-m-d H:i'), 'title'=>'ようこそ', 'body'=>'シフトモのデモ版へようこそ。', 'target'=>'ALL']];
    $_SESSION['attendance'] = [];
    $_SESSION['requests'] = [];
    $_SESSION['changes'] = [];
    $_SESSION['late'] = [];
    $_SESSION['shifts'] = [];
    // 初期状態では公開シフトはありません。
    // 希望登録 → シフト編集 → 公開 の順に操作します。
    $_SESSION['requests'][] = ['id'=>'sample_1', 'user'=>2, 'date'=>date('Y-m-').'25', 'time'=>'07:00-12:00', 'status'=>'申請中'];
    $_SESSION['requests'][] = ['id'=>'sample_2', 'user'=>3, 'date'=>date('Y-m-').'26', 'time'=>'12:00-17:00', 'status'=>'申請中'];
}
// 将来のSQL例（今は使用しません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
