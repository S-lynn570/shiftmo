<?php
// 部署管理：登録・従業員の部署変更・部署別表示
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action == 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name != '' && !in_array($name, $_SESSION['departments'])) {
            $_SESSION['departments'][] = $name;
            $message = '部署を登録しました。';
        } else { $message = '部署名を確認してください。'; }
    }
    if ($action == 'save' || $action == 'remove') {
        $id = (int)($_POST['emp'] ?? 0);
        $dept = $_POST['dept'] ?? '';
        foreach ($_SESSION['employees'] as $number => $employee) {
            if ($employee['id'] == $id) {
                if ($action == 'remove') { $_SESSION['employees'][$number]['dept'] = ''; $message = '所属部署を解除しました。'; }
                if ($action == 'save' && in_array($dept, $_SESSION['departments'])) {
                    $_SESSION['employees'][$number]['dept'] = $dept;
                    $message = '所属部署を保存しました。';
                }
            }
        }
    }
}
// 将来のDB接続例（今は使用しません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
