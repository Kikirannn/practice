<?php

require_once __DIR__ . '/../config/database.php';
function logStatusChange($report_id, $status_awal, $status_akhir, $technician_id = null, $catatan = null)
{
    try {
        $pdo = getDBConnection();

        $sql = "INSERT INTO tracking_progress 
                (report_id, technician_id, status_awal, status_akhir, catatan, timestamp) 
                VALUES (:report_id, :technician_id, :status_awal, :status_akhir, :catatan, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':report_id' => $report_id,
            ':technician_id' => $technician_id,
            ':status_awal' => $status_awal,
            ':status_akhir' => $status_akhir,
            ':catatan' => $catatan
        ]);

        return true;
    } catch (PDOException $e) {
        error_log("Error logging status change: " . $e->getMessage());
        return false;
    }
}


function getTrackingHistory($report_id)
{
    try {
        $pdo = getDBConnection();

        $sql = "SELECT tp.*, u.nama_lengkap as teknisi_nama 
                FROM tracking_progress tp
                LEFT JOIN users u ON tp.technician_id = u.user_id
                WHERE tp.report_id = :report_id
                ORDER BY tp.timestamp DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':report_id' => $report_id]);

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting tracking history: " . $e->getMessage());
        return [];
    }
}

function validateStatusChange($current_status, $new_status)
{
    $allowedTransitions = [
        'open' => ['process', 'reject'],
        'process' => ['done'],
        'done' => [],
        'reject' => []
    ];

    if (!isset($allowedTransitions[$current_status])) {
        return false;
    }

    return in_array($new_status, $allowedTransitions[$current_status]);
}
?>