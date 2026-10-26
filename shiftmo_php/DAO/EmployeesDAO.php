<?php
// 従業員管理の処理です。データは今までと同じSessionに保存します。
$message = $_SESSION['employee_message'] ?? '';
unset($_SESSION['employee_message']);
$error = '';
$departments = $_SESSION['departments'] ?? [];
$form = ['name' => '', 'phone' => '', 'email' => '', 'dept' => ''];

// 新規登録の確認画面から戻ったときは、入力した内容を表示します。
if ($page == 'new') {
    $form = $_SESSION['employee_draft'] ?? $form;
}

// 編集する従業員をIDで探します。
$editId = (int)($_GET['id'] ?? 0);
$editEmployee = null;
if ($page == 'edit') {
    foreach ($_SESSION['employees'] as $employee) {
        if ($employee['id'] == $editId) {
            $editEmployee = $employee;
            $form = $employee;
            break;
        }
    }
    if ($editEmployee == null) {
        $error = '対象の従業員が見つかりません。';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // ① 入力された内容を確認します。この時点では新規登録しません。
    if (($page == 'new' && $action == 'prepare') || ($page == 'edit' && $action == 'update' && $editEmployee != null)) {
        $form = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'dept' => $_POST['dept'] ?? ''
        ];
        if ($form['name'] == '' || $form['phone'] == '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $error = '従業員名・連絡先・メールアドレスを確認してください。';
        } elseif ($form['dept'] != '' && !in_array($form['dept'], $departments)) {
            $error = '部署を選択し直してください。';
        }
        foreach ($_SESSION['employees'] as $employee) {
            if (strcasecmp($employee['email'], $form['email']) == 0 && ($page == 'new' || $employee['id'] != $editId)) {
                $error = 'このメールアドレスは、すでに登録されています。';
            }
        }
        if ($error == '' && $action == 'prepare') {
            $_SESSION['employee_draft'] = $form;
            header('Location: employee_confirm.php');
            exit;
        }
        // ② 編集では、ID・権限・パスワードを変えずに入力項目だけ保存します。
        if ($error == '' && $action == 'update') {
            foreach ($_SESSION['employees'] as $key => $employee) {
                if ($employee['id'] == $editId) {
                    $_SESSION['employees'][$key]['name'] = $form['name'];
                    $_SESSION['employees'][$key]['phone'] = $form['phone'];
                    $_SESSION['employees'][$key]['email'] = $form['email'];
                    $_SESSION['employees'][$key]['dept'] = $form['dept'];
                    if ($_SESSION['user']['id'] == $editId) {
                        $_SESSION['user'] = $_SESSION['employees'][$key];
                    }
                    break;
                }
            }
            $_SESSION['employee_message'] = '従業員情報を保存しました。';
            header('Location: employee_list.php');
            exit;
        }
    }

    // ③ 確認画面の「確認」を押したときに初めて登録します。
    if ($page == 'confirm' && $action == 'register') {
        $draft = $_SESSION['employee_draft'] ?? null;
        if ($draft == null) {
            $error = '入力内容がありません。新規登録画面から入力してください。';
        } else {
            // 確認画面を開いている間に、同じメールが登録されていないか確認します。
            foreach ($_SESSION['employees'] as $employee) {
                if (strcasecmp($employee['email'], $draft['email']) == 0) {
                    $error = 'このメールアドレスは、すでに登録されています。';
                }
            }
            if ($draft['dept'] != '' && !in_array($draft['dept'], $departments)) {
                $error = '部署が変更されています。戻って部署を選択し直してください。';
            }
            if ($error == '') {
                // 削除されたIDがあっても、登録済みのIDと重ならない番号にします。
                $newId = 1;
                foreach ($_SESSION['employees'] as $employee) {
                    if ($employee['id'] >= $newId) {
                        $newId = $employee['id'] + 1;
                    }
                }
                $_SESSION['employees'][] = [
                    'id' => $newId, 'name' => $draft['name'],
                    'phone' => $draft['phone'], 'email' => $draft['email'],
                    'dept' => $draft['dept'], 'role' => 'STAFF',
                    'password' => 'password123'
                ];
                unset($_SESSION['employee_draft']);
                $_SESSION['employee_created_id'] = $newId;
                $_SESSION['employee_message'] = '従業員を登録しました。';
                // 更新ボタンによる二重登録を防ぐため、一覧画面へ移動します。
                header('Location: employee_list.php');
                exit;
            }
        }
    }

    // ④ 一覧画面から従業員を削除します。
    if ($page == 'list' && $action == 'delete') {
        $deleteId = (int)($_POST['id'] ?? 0);
        $hasRecords = false;
        // 他の画面に記録がある従業員は、データが不明にならないよう削除しません。
        foreach ($_SESSION['shifts'] ?? [] as $record) {
            if (($record['emp'] ?? 0) == $deleteId) { $hasRecords = true; }
        }
        foreach (['attendance', 'requests', 'changes', 'late'] as $listName) {
            foreach ($_SESSION[$listName] ?? [] as $record) {
                if (($record['user'] ?? 0) == $deleteId) { $hasRecords = true; }
            }
        }
        foreach ($_SESSION['messages'] ?? [] as $record) {
            if ((string)($record['target'] ?? 'ALL') == (string)$deleteId) { $hasRecords = true; }
        }
        if ($deleteId == $_SESSION['user']['id']) {
            $error = 'ログイン中の従業員は削除できません。';
        } elseif ($hasRecords) {
            $error = 'シフト・勤怠・申請・通知などの記録がある従業員は削除できません。';
        } else {
            foreach ($_SESSION['employees'] as $key => $employee) {
                if ($employee['id'] == $deleteId) {
                    unset($_SESSION['employees'][$key]);
                    $_SESSION['employee_message'] = '従業員を削除しました。';
                    header('Location: employee_list.php');
                    exit;
                }
            }
            $error = '対象の従業員が見つかりません。';
        }
    }
}

// ⑤ 一覧の検索。IDまたは名前に入力文字が含まれる従業員を表示します。
$search = trim($_GET['search'] ?? '');
$employees = [];
foreach ($_SESSION['employees'] as $employee) {
    if ($search == '' || strpos((string)$employee['id'], $search) !== false || strpos($employee['name'], $search) !== false) {
        $employees[] = $employee;
    }
}

$draft = $_SESSION['employee_draft'] ?? null;
// ポップアップは登録直後の一覧画面で一度だけ表示します。
$createdId = null;
if ($page == 'list') {
    $createdId = $_SESSION['employee_created_id'] ?? null;
    unset($_SESSION['employee_created_id']);
}
