<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AdminController extends Controller
{
    private $userModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/login');
        }
        $this->userModel = new User();
    }

    public function dashboard()
    {
        // For now, just a placeholder. In reality, would fetch metrics.
        $this->render('admin/dashboard');
    }
}
