<?php

namespace Core;

class View
{
    private static string $viewsPath = __DIR__ . '/../app/Views/';

    public static function render(string $view, array $data = [], ?string $layout = 'layouts.app')
    {
        $viewPath = self::$viewsPath . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: $viewPath");
        }

        // Extract data for view
        extract($data);
        
        // Start output buffering
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // If layout is specified, include it
        if ($layout) {
            $layoutPath = self::$viewsPath . str_replace('.', '/', $layout) . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
}