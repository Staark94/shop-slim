<?php
    session_start();
    ini_set('memory_limit', '-1');
    date_default_timezone_set('Europe/Bucharest');


    use Cart\App;
    require_once __DIR__ . '/vendor/autoload.php';

    // Create Application
    $app = new App;

    // Required files and functions
    require_once __DIR__ . '/app/database.php';
    require_once __DIR__ . '/app/routes.php';

    
    // Start run application
    $app->run();