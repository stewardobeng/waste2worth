<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $role = $_SESSION['role'];

        switch ($role) {
            case 'collector':
                $this->redirect('/collector/dashboard');
                break;
            case 'client':
                $this->redirect('/client/dashboard');
                break;
            case 'admin':
                $this->redirect('/admin/dashboard');
                break;
            default:
                $this->redirect('/login');
                break;
        }
    }
}
