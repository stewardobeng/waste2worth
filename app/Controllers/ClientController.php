<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CollectorProfile;

class ClientController extends Controller
{
    private $collectorProfileModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
            $this->redirect('/login');
        }
        $this->collectorProfileModel = new CollectorProfile();
    }

    public function dashboard()
    {
        $this->render('client/dashboard');
    }

    public function discovery()
    {
        $this->render('client/discovery');
    }

    public function getNearbyCollectors()
    {
        $lat = $_GET['lat'] ?? 0;
        $lng = $_GET['lng'] ?? 0;
        $radius = $_GET['radius'] ?? 10;

        $collectors = $this->collectorProfileModel->getNearby($lat, $lng, $radius);
        $this->json($collectors);
    }
}
