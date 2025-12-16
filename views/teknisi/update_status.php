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
        // Validate state machine
        if (!validateStatusChange($currentStatus, $newStatus)) {
            $error = 'Perubahan status tidak valid. Status saat ini: ' . formatStatus($currentStatus);
        } else {
            try {
                $pdo = getDBConnection();
                $pdo->beginTransaction();

                // Verify task is assigned to this technician
                $sql = "SELECT assigned_to FROM laporan WHERE report_id = :report_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':report_id' => $reportId]);
                $assignedTo = $stmt->fetchColumn();

                if ($assignedTo != $userId) {
                    throw new Exception('Anda tidak memiliki akses untuk mengupdate laporan ini');
                }

                // Update status
                $sql = "UPDATE laporan SET status = :new_status, updated_at = NOW() WHERE report_id = :report_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':new_status' => $newStatus,
                    ':report_id' => $reportId
                ]);

                // Log status change (manual trigger)
                logStatusChange($reportId, $currentStatus, $newStatus, $userId, $catatan);

                $pdo->commit();

                $success = 'Status berhasil diupdate';

                // Redirect back to task detail
                header("Location: tugas.php?detail=$reportId&success=1");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        }
    }
}

// If there's an error, redirect back with error message
if ($error) {
    $_SESSION['error'] = $error;
    $reportId = $_POST['report_id'] ?? 0;
    redirect("/Learning1/teknisi/tugas.php?detail=$reportId");
}

// If accessed directly without POST, redirect to tasks
redirect('/Learning1/views/teknisi/tugas.php');
?>
