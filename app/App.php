<?php
/**
 * App For Shop Page
 */

namespace Cart;

use DI\ContainerBuilder;
use DI\Bridge\Slim\App as DIBridge;

class App extends DIBridge
{
    protected function configureContainer(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'settings.responseChunkSize' => 4096,
            'settings.outputBuffering' => 'append',
            'settings.determineRouteBeforeAppMiddleware' => false,
            'settings.displayErrorDetails' => true,
            'settings.uploadPath'   => __DIR__ . '/uploads'
        ]);

        $builder->addDefinitions(__DIR__ . '/container.php');
        $container = $builder->build();
    }
}