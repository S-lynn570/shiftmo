<?php
// シフト管理の登録処理です。
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // ① シフト編集：希望がない従業員も追加できます。
    if ($action == 'save') {
        $emp = (int)($_POST['emp'] ?? 0);
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $shiftId = $_POST['shift_id'] ?? '';
        $changeId = $_POST['change_id'] ?? '';
        $requestId = $_POST['request_id'] ?? '';
        $validEmp = false;
        foreach ($_SESSION['employees'] ?? [] as $employee) {
            if (($employee['id'] ?? '') == $emp) { $validEmp = true; }
        }

        if ($validEmp && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            && in_array($time, ['07:00-12:00', '12:00-17:00', '17:00-22:00'])) {
            $found = false;
            // 指定されたシフトを編集します。
            foreach ($_SESSION['shifts'] as $key => $shift) {
                if ($shiftId != '' && ($shift['id'] ?? '') == $shiftId && ($shift['emp'] ?? '') == $emp) {
                    $_SESSION['shifts'][$key]['date'] = $date;
                    $_SESSION['shifts'][$key]['time'] = $time;
                    $_SESSION['shifts'][$key]['status'] = '下書き';
                    $found = true;
                    break;
                }
            }
            // 同じ従業員・日付のシフトがあれば更新します。
            if (!$found && $shiftId == '') {
                foreach ($_SESSION['shifts'] as $key => $shift) {
                    if (($shift['emp'] ?? '') == $emp && ($shift['date'] ?? '') == $date) {
                        $_SESSION['shifts'][$key]['time'] = $time;
                        $_SESSION['shifts'][$key]['status'] = '下書き';
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $_SESSION['shifts'][] = [
                    'id' => uniqid('shift_'), 'emp' => $emp,
                    'date' => $date, 'time' => $time, 'status' => '下書き'
                ];
            }
            // 希望を確定したとき、希望一覧から削除します。
            if ($requestId != '') {
                foreach ($_SESSION['requests'] as $key => $request) {
                    if (($request['id'] ?? '') == $requestId && ($request['user'] ?? '') == $emp) {
                        $_SESSION['requests'][$key]['status'] = '確定';
                    }
                }
            }
            // 調整依頼から来たときは、編集保存後に「承認済み」にします。
            if ($changeId != '') {
                foreach ($_SESSION['changes'] as $key => $change) {
                    if (($change['id'] ?? '') == $changeId && ($change['status'] ?? '') == '申請中'
                        && ($change['user'] ?? '') == $emp && ($change['shift_id'] ?? '') == $shiftId) {
                        $_SESSION['changes'][$key]['status'] = '承認済み（再公開待ち）';
                    }
                }
            }
            $message = 'シフトを保存しました。シフト公開画面から再公開してください。';
        } else {
            $message = '名前・日付・時間帯を確認してください。';
        }
    }

    // ② 希望一覧の「削除」ボタンです。
    if ($action == 'delete_request') {
        $id = $_POST['request_id'] ?? '';
        foreach ($_SESSION['requests'] as $key => $request) {
            if (($request['id'] ?? '') == $id && ($request['status'] ?? '') == '申請中') {
                unset($_SESSION['requests'][$key]);
                $message = '希望を一覧から削除しました。';
                break;
            }
        }
    }

    // ③ シフト一覧の「削除」ボタンです。
    if ($action == 'delete_shift') {
        $id = $_POST['shift_id'] ?? '';
        foreach ($_SESSION['shifts'] as $key => $shift) {
            if (($shift['id'] ?? '') == $id) {
                unset($_SESSION['shifts'][$key]);
                $message = 'シフトを削除しました。';
                break;
            }
        }
    }

    // ④ シフト公開：ここで初めて公開状態になります。
    if ($action == 'publish') {
        foreach ($_SESSION['shifts'] as $key => $shift) {
            $_SESSION['shifts'][$key]['status'] = '公開';
        }
        foreach ($_SESSION['changes'] as $key => $change) {
            if (($change['status'] ?? '') == '承認済み（再公開待ち）') {
                $_SESSION['changes'][$key]['status'] = '承認';
            }
        }
        $message = 'シフトを公開しました。';
    }

    // ⑤ 調整依頼の拒否：シフトは変更しません。
    if ($action == 'reject') {
        $id = $_POST['change_id'] ?? '';
        foreach ($_SESSION['changes'] as $key => $change) {
            if (($change['id'] ?? '') == $id && ($change['status'] ?? '') == '申請中') {
                $_SESSION['changes'][$key]['status'] = '拒否';
                $message = '調整依頼を拒否しました。';
                break;
            }
        }
    }
}

// 将来のデータベース接続例（現在は使用しません）
// $pdo = new PDO('mysql:host=localhost;dbname=shiftmo;charset=utf8mb4', 'root', '');
