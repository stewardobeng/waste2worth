<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class NotificationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function sendInApp($userId, $type, $title, $message, $metadata = [])
    {
        $sql = "INSERT INTO notifications (user_id, type, title, message, metadata) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $type,
            $title,
            $message,
            json_encode($metadata)
        ]);
    }

    public function sendSMS($userId, $phone, $message)
    {
        // Placeholder for Twilio/SMS integration
        // In a real app, you'd use the Twilio SDK here.
        
        $sql = "INSERT INTO sms_logs (user_id, phone, message, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $phone, $message, 'sent']);
        
        return true;
    }
}
