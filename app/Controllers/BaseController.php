<?php

namespace App\Controllers;

use Core\View;

abstract class BaseController 
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts.app')
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}