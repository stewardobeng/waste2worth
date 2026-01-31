<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (role, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['role'],
            $data['email'],
            $data['phone'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['status'] ?? 'pending'
        ]);
        return $this->db->lastInsertId();
    }

    public function updateLastLogin($id)
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE user_id = ?");
        $stmt->execute([$id]);
    }
}
