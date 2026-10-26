<?php
// メッセージを登録します。
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $target = $_POST['target'] ?? 'ALL';

    // ① 入力を確認します。
    if ($title == '' || $body == '') {
        $error = '題名と内容を入力してください。';
    } else {
        // ② 個人宛てなら、登録済み従業員を探します。
        $email = '';
        $targetName = '全員';
        if ($target != 'ALL') {
            foreach ($_SESSION['employees'] ?? [] as $employee) {
                if ((string)$employee['id'] == (string)$target) {
                    $email = $employee['email'];
                    $targetName = $employee['name'];
                    break;
                }
            }
            if ($email == '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = '送信先のメールアドレスが見つかりません。';
            }
        }

        // ③ お知らせをセッションに保存します。
        if ($error == '') {
            $_SESSION['messages'][] = [
                'date' => date('Y-m-d H:i'),
                'title' => $title,
                'body' => $body,
                'target' => $target,
                'target_name' => $targetName,
                'email' => $email
            ];
            $message = $target == 'ALL' ? '全員へのお知らせを登録しました。' : $targetName . 'さん宛てに通知を登録しました。';

            // ④ メール送信（初期状態は無効です）。
            // XAMPPでSMTPを設定してから true に変更してください。
            $sendRealMail = false;
            if ($target != 'ALL') {
                if ($sendRealMail) {
                    $subject = '=?UTF-8?B?' . base64_encode($title) . '?=';
                    $headers = 'Content-Type: text/plain; charset=UTF-8';
                    if (mail($email, $subject, $body, $headers)) {
                        $message .= ' メールも送信しました。';
                    } else {
                        $message .= ' メール送信は失敗しました。SMTP設定を確認してください。';
                    }
                } else {
                    $message .= ' メールはデモモードのため未送信です。';
                }
            }
        }
    }
}

// 将来のデータベース処理例（現在は使いません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
