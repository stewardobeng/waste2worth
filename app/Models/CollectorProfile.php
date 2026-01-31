<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class CollectorProfile extends Model
{
    public function getNearby($lat, $lng, $radius = 5)
    {
        // Use ST_Distance_Sphere if MySQL 5.7+
        $sql = "SELECT u.user_id, cp.display_name, cp.waste_types, cp.latitude, cp.longitude, cp.availability_status, cp.rating_avg,
                (6371 * acos(cos(radians(?)) * cos(radians(cp.latitude)) * cos(radians(cp.longitude) - radians(?)) + sin(radians(?)) * sin(radians(cp.latitude)))) AS distance
                FROM collector_profiles cp
                JOIN users u ON u.user_id = cp.collector_id
                WHERE u.status = 'active'
                HAVING distance < ?
                ORDER BY distance ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lat, $lng, $lat, $radius]);
        return $stmt->fetchAll();
    }

    public function findByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM collector_profiles WHERE collector_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function update($userId, $data)
    {
        $sql = "INSERT INTO collector_profiles (collector_id, display_name, bio, latitude, longitude, service_radius_km, waste_types, availability_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                display_name = VALUES(display_name),
                bio = VALUES(bio),
                latitude = VALUES(latitude),
                longitude = VALUES(longitude),
                service_radius_km = VALUES(service_radius_km),
                waste_types = VALUES(waste_types),
                availability_status = VALUES(availability_status)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $data['display_name'],
            $data['bio'],
            $data['latitude'],
            $data['longitude'],
            $data['service_radius_km'],
            json_encode($data['waste_types']),
            $data['availability_status']
        ]);
    }
}
