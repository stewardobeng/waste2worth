<?php

namespace App\Core;

class Controller
{
    protected function render($view, $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . "/../../views/$view.php";
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("View $view not found.");
        }
    }

    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url)
    {
        header("Location: " . $_ENV['APP_URL'] . $url);
        exit;
    }
}
