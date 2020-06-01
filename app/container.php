<?php

use function DI\get;
use Slim\Views\Twig;

use Cart\Checkout\Basket;
use Cart\Checkout\Order;
use Cart\Checkout\Wishlist;

use Slim\Views\TwigExtension;
use Interop\Container\ContainerInterface;

// Support
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Support\Storage\SessionStorage;

// Models
use Cart\Models\Product;
use Cart\Models\Category;
use Cart\Models\Customer;
use Cart\Models\Orders;

return [
    'router'    => get(Slim\Router::class),

    StorageInterface::class => function (ContainerInterface $c)
    {
        return new SessionStorage;
    },

    Twig::class => function (ContainerInterface $c)
    {
        $twig = new Twig(__DIR__ . '/../resources/views', [
            'cache' => false,
            'debug' => true,
        ]);

        $twig->addExtension(new TwigExtension($c->get('router'), $c->get('request')->getUri()));
        $twig->addExtension(new \Twig\Extension\DebugExtension());

        $twig->getEnvironment()->addGlobal('basket', $c->get(Basket::class));
        $twig->getEnvironment()->addGlobal('customer', $c->get(Customer::class));
        $twig->getEnvironment()->addGlobal('wishlist', $c->get(Wishlist::class));
        $twig->getEnvironment()->addGlobal('root_path', __DIR__ . '/uploads');
        $twig->getEnvironment()->addGlobal('sessions', $c->get(StorageInterface::class));

        return $twig;
    },

    Product::class => function (ContainerInterface $c)
    {
        return new Product;
    },

    Category::class => function (ContainerInterface $c)
    {
        return new Category;
    },

    Customer::class => function (ContainerInterface $c)
    {
        return new Customer;
    },

    Orders::class => function (ContainerInterface $c)
    {
        return new Orders;
    },

    Basket::class => function (ContainerInterface $c)
    {
        return new Basket(
            $c->get(SessionStorage::class),
            $c->get(Product::class)
        );
    },

    Wishlist::class => function (ContainerInterface $c)
    {
        return new Wishlist(
            $c->get(SessionStorage::class),
            $c->get(Product::class)
        );
    },

    Order::class => function (ContainerInterface $c)
    {
        return new Order(
            $c->get(SessionStorage::class),
            $c->get(Product::class)
        );
    }
];