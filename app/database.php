<?php
    use Illuminate\Database\Capsule\Manager as Capsule;

    $capsule = new Capsule();
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'shop_piesetv',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collection'    => 'utf8_unicode_ci',
        'prefix'    => ''
    ]);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();