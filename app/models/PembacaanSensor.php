<?php
/**
 * PembacaanSensor Model
 * Menangani log pembacaan sensor
 * @package app/models
 */
class PembacaanSensor extends Model {
    protected $table = 'pembacaan_sensor';

    /**
     * Create new reading
     * @param array $data
     * @return int|false
     */
    public function createReading(array $data) {
        try {
            // Determine status based on sensor thresholds
            $sensor = $this->db->prepare("SELECT nilai_min, nilai_max FROM sensor_pengairan WHERE id = ?");
            $sensor->execute([$data['sensor_id']]);
            $sensorData = $sensor->fetch();

            $status = 'Normal';
            if ($sensorData) {
                if ($sensorData['nilai_min'] !== null && $data['nilai'] < $sensorData['nilai_min']) {
                    $status = 'Rendah';
                } elseif ($sensorData['nilai_max'] !== null && $data['nilai'] > $sensorData['nilai_max']) {
                    $status = 'Tinggi';
                }
            }

            $data['status_pembacaan'] = $status;
            $data['waktu_baca'] = $data['waktu_baca'] ?? date('Y-m-d H:i:s');

            $readingId = $this->create($data);

            // Update sensor last reading time
            if ($readingId) {
                $sensorModel = new SensorPengairan();
                $sensorModel->updateLastReading($data['sensor_id']);
            }

            return $readingId;
        } catch (Exception $e) {
            error_log("Error creating sensor reading: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get readings for sensor
     * @param int $sensorId
     * @param int $limit
     * @return array
     */
    public function getReadingsForSensor(int $sensorId, int $limit = 100): array {
        $sql = "SELECT * FROM pembacaan_sensor 
                WHERE sensor_id = ? 
                ORDER BY waktu_baca DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sensorId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get latest readings for all active sensors
     * @return array
     */
    public function getLatestReadingsForAll(): array {
        $sql = "SELECT ps.*, s.nama as sensor_nama, s.tipe_sensor, s.kode_sensor
                FROM pembacaan_sensor ps
                INNER JOIN (
                    SELECT sensor_id, MAX(waktu_baca) as max_waktu
                    FROM pembacaan_sensor
                    GROUP BY sensor_id
                ) latest ON ps.sensor_id = latest.sensor_id AND ps.waktu_baca = latest.max_waktu
                INNER JOIN sensor_pengairan s ON ps.sensor_id = s.id
                WHERE s.status = 'Aktif'
                ORDER BY ps.waktu_baca DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

