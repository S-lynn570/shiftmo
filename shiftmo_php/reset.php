<?php
// ① セッションを開始します。
session_start();

// ② デモ用の従業員データを読み込みます。
require_once 'DAO/LoginDAO.php';

// ③ パスワード再設定の処理を読み込みます。
require_once 'DAO/ResetDAO.php';

// ④ 現在の画面を決めます。
$step = $_SESSION['reset_step'] ?? 1;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード再設定 - シフトモ</title>
    <link rel="stylesheet" href="CSS/reset.css?v=20261026-3">
    <!-- 共通の見た目を最後に読み込みます。 -->
    <link rel="stylesheet" href="CSS/design.css?v=20261026-3">
</head>
<body class="login-page">
    <div class="login-card">
        <h1>パスワード再設定</h1>
        <p>順番に入力してください。</p>

        <?php if ($error != '') { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <?php if ($success != '') { ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php } else { ?>

            <?php if ($step == 1) { ?>
                <!-- ステップ1：メールアドレス -->
                <h2>① メールアドレス確認</h2>
                <form method="POST" action="reset.php">
                    <input type="hidden" name="action" value="email">
                    <label for="email">メールアドレス</label>
                    <input id="email" type="email" name="email" required>
                    <button type="submit">次へ</button>
                </form>
            <?php } ?>

            <?php if ($step == 2) { ?>
                <!-- ステップ2：確認コード -->
                <h2>② 確認コード</h2>
                <p>確認先：<?php echo htmlspecialchars($_SESSION['reset_email']); ?></p>
                <p class="hint">デモ用確認コード：8429（メール送信は行いません）</p>
                <form method="POST" action="reset.php">
                    <input type="hidden" name="action" value="code">
                    <label for="code">確認コード</label>
                    <input id="code" type="text" name="code" required>
                    <button type="submit">確認する</button>
                </form>
            <?php } ?>

            <?php if ($step == 3) { ?>
                <!-- ステップ3：新しいパスワード -->
                <h2>③ 新しいパスワード</h2>
                <form method="POST" action="reset.php">
                    <input type="hidden" name="action" value="password">
                    <label for="password">新しいパスワード（6文字以上）</label>
                    <input id="password" type="password" name="password" minlength="6" required>
                    <label for="confirm">新しいパスワード（確認）</label>
                    <input id="confirm" type="password" name="confirm" minlength="6" required>
                    <button type="submit">パスワードを変更</button>
                </form>
            <?php } ?>

            <?php if ($step != 1) { ?>
                <form method="POST" action="reset.php" class="restart-form">
                    <input type="hidden" name="action" value="restart">
                    <button type="submit" class="secondary">最初からやり直す</button>
                </form>
            <?php } ?>
        <?php } ?>

        <p><a href="login.php">ログイン画面に戻る</a></p>
    </div>
</body>
</html>
