<?php

namespace Cart\Controller;

use Slim\Router;
use Slim\Views\Twig;
use Cart\Checkout\Basket;
use Cart\Models\Product;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CartController
{
    protected $basket;

    protected $product;

    public function __construct(Basket $basket, Product $product)
    {
        $this->basket = $basket;
        $this->product = $product;
    }

    public function index(Request $request, Response $response, Twig $view)
    {
        $this->basket->refresh();
        return $view->render($response, 'cart/index.twig');
    }

    public function add($slug, $quantity, Request $request, Response $response, Router $router)
    {
        $product = $this->product->where('slug', $slug)->first();

        if (!$product)
        {
            return $response->withRedirect($router->pathFor('index'));
        }

        try {
           $this->basket->add($product, $quantity);

           return $response->withRedirect($router->pathFor('cart'));
        } catch(QuantityExceededException $e) {
            echo $e->message;
        }
    }

    public function update($slug, Request $request, Response $response, Router $router)
    {
        $product = $this->product->where('slug', $slug)->first();

        if (!$product)
        {
            return $response->withRedirect($router->pathFor('index'));
        }

        try {
            $this->basket->update($product, $request->getParam('quantity'));
            return $response->withRedirect($router->pathFor('cart'));
        } catch(QuantityExceededException $e) {
            echo $e->message;
        }
    }

    public function delete()
    {

    }
}