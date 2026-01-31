<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ServiceRequest;
use App\Models\CollectorProfile;

class CollectorController extends Controller
{
    private $serviceRequestModel;
    private $collectorProfileModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
            $this->redirect('/login');
        }
        $this->serviceRequestModel = new ServiceRequest();
        $this->collectorProfileModel = new CollectorProfile();
    }

    public function dashboard()
    {
        $requests = $this->serviceRequestModel->findByCollector($_SESSION['user_id']);
        $profile = $this->collectorProfileModel->findByUserId($_SESSION['user_id']);
        
        $this->render('collector/dashboard', [
            'requests' => $requests,
            'profile' => $profile
        ]);
    }

    public function updateStatus()
    {
        $requestId = $_POST['request_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if ($requestId && $status) {
            $this->serviceRequestModel->updateStatus($requestId, $status);
        }
        $this->redirect('/collector/dashboard');
    }

    public function updateProfile()
    {
        $data = [
            'display_name' => $_POST['display_name'],
            'bio' => $_POST['bio'],
            'latitude' => $_POST['latitude'],
            'longitude' => $_POST['longitude'],
            'service_radius_km' => $_POST['service_radius_km'],
            'waste_types' => $_POST['waste_types'] ?? [],
            'availability_status' => $_POST['availability_status']
        ];

        $this->collectorProfileModel->update($_SESSION['user_id'], $data);
        $this->redirect('/collector/dashboard');
    }
}
