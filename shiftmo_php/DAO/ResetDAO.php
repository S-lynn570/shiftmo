<?php
// パスワード再設定の処理です。
// デモ用のため、確認コードは画面に表示します。
// メールは送信しません。

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // ① メールアドレスを確認する
    if ($action == 'email') {
        $email = trim($_POST['email'] ?? '');
        $found = false;

        foreach ($_SESSION['employees'] as $employee) {
            if ($employee['email'] == $email) {
                $found = true;
            }
        }

        if ($found) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_code'] = '8429';
            $_SESSION['reset_step'] = 2;
        } else {
            $error = '登録されたメールアドレスを入力してください。';
        }
    }

    // ② 確認コードを確認する
    if ($action == 'code') {
        $code = trim($_POST['code'] ?? '');

        if (($_SESSION['reset_step'] ?? 1) != 2) {
            $error = 'メールアドレスの確認から始めてください。';
        } elseif ($code == $_SESSION['reset_code']) {
            $_SESSION['reset_step'] = 3;
        } else {
            $error = '確認コードが違います。';
        }
    }

    // ③ 新しいパスワードを登録する
    if ($action == 'password') {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (($_SESSION['reset_step'] ?? 1) != 3) {
            $error = '確認コードを先に入力してください。';
        } elseif (strlen($password) < 6) {
            $error = 'パスワードは6文字以上で入力してください。';
        } elseif ($password != $confirm) {
            $error = '確認用パスワードが一致しません。';
        } else {
            foreach ($_SESSION['employees'] as $key => $employee) {
                if ($employee['email'] == $_SESSION['reset_email']) {
                    $_SESSION['employees'][$key]['password'] = $password;
                }
            }

            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_code']);
            $_SESSION['reset_step'] = 1;
            $success = 'パスワードを変更しました。新しいパスワードでログインしてください。';
        }
    }

    // 最初からやり直す
    if ($action == 'restart') {
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_code']);
        $_SESSION['reset_step'] = 1;
    }
}

// 将来のデータベース接続例（今は使用しません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
