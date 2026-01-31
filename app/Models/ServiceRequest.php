<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ServiceRequest extends Model
{
    public function create($data)
    {
        $sql = "INSERT INTO service_requests (client_id, collector_id, requested_waste_types, description, pickup_address, latitude, longitude, desired_pickup_time, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['client_id'],
            $data['collector_id'],
            json_encode($data['waste_types']),
            $data['description'],
            $data['address'],
            $data['latitude'],
            $data['longitude'],
            $data['pickup_time'],
            'pending'
        ]);
        return $this->db->lastInsertId();
    }

    public function findByCollector($collectorId, $status = null)
    {
        $sql = "SELECT sr.*, u.email as client_email FROM service_requests sr 
                JOIN users u ON u.user_id = sr.client_id
                WHERE sr.collector_id = ?";
        $params = [$collectorId];
        
        if ($status) {
            $sql .= " AND sr.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY sr.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus($requestId, $status)
    {
        $stmt = $this->db->prepare("UPDATE service_requests SET status = ? WHERE request_id = ?");
        return $stmt->execute([$status, $requestId]);
    }
}
