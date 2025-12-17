<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/tracking.php';

requireRole('teknisi');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = (int) ($_POST['report_id'] ?? 0);
    $currentStatus = $_POST['current_status'] ?? '';
    $newStatus = $_POST['new_status'] ?? '';
    $catatan = sanitize($_POST['catatan'] ?? '');

    $userId = getCurrentUserId();

    if (empty($newStatus)) {
        $error = 'Status baru harus dipilih';
    } elseif (empty($catatan)) {
        $error = 'Catatan perbaikan harus diisi';
    } else {
        if (!validateStatusChange($currentStatus, $newStatus)) {
            $error = 'Perubahan status tidak valid. Status saat ini: ' . formatStatus($currentStatus);
        } else {
            try {
                $pdo = getDBConnection();
                $pdo->beginTransaction();

                $sql = "SELECT assigned_to FROM laporan WHERE report_id = :report_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':report_id' => $reportId]);
                $assignedTo = $stmt->fetchColumn();

                if ($assignedTo != $userId) {
                    throw new Exception('Anda tidak memiliki akses untuk mengupdate laporan ini');
                }

                $sql = "UPDATE laporan SET status = :new_status, updated_at = NOW() WHERE report_id = :report_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':new_status' => $newStatus,
                    ':report_id' => $reportId
                ]);

                logStatusChange($reportId, $currentStatus, $newStatus, $userId, $catatan);

                $pdo->commit();

                $success = 'Status berhasil diupdate';

                header("Location: tugas.php?detail=$reportId&success=1");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        }
    }
}

if ($error) {
    $_SESSION['error'] = $error;
    $reportId = $_POST['report_id'] ?? 0;
    redirect("/Learning1/views/teknisi/tugas.php?detail=$reportId");
}

redirect('/Learning1/views/teknisi/tugas.php');
?>