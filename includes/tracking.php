<?php
// Tracking Progress - Manual Trigger via PHP

require_once __DIR__ . '/../config/database.php';

/**
 * Log perubahan status laporan ke tabel tracking_progress
 * Fungsi ini dipanggil manual setiap kali ada perubahan status (bukan SQL trigger)
 * 
 * @param int $report_id ID laporan
 * @param string $status_awal Status sebelum perubahan
 * @param string $status_akhir Status setelah perubahan
 * @param int|null $technician_id ID teknisi (opsional)
 * @param string|null $catatan Catatan perubahan (opsional)
 * @return bool True jika berhasil, false jika gagal
 */
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

/**
 * Get tracking history untuk suatu laporan
 * 
 * @param int $report_id ID laporan
 * @return array Array of tracking records
 */
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

/**
 * Validate state machine untuk perubahan status
 * 
 * @param string $current_status Status saat ini
 * @param string $new_status Status baru yang diinginkan
 * @return bool True jika valid, false jika tidak valid
 */
function validateStatusChange($current_status, $new_status)
{
    // Allowed transitions
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