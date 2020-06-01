<?php

namespace Cart\Controller\Auth;

use Slim\Router;
use Slim\Views\Twig;
use Cart\Models\Product;
use Cart\Models\Customer;
use Cart\Models\Orders;
use Illuminate\Database\Capsule\Manager as DB;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UserController {
    protected $view;
    protected $product;
    protected $customer;

    public function __construct(Twig $view, Product $product, Customer $customer)
    {
        $this->view     = $view;
        $this->product  = $product;
        $this->customer = $customer;
    }

    public function index(Request $request, Response $response, Orders $orderData)
    {
        $data = $orderData->where('customer_id', $this->customer->getId()['id'])->get();

        if (!empty($data)):
            foreach($data as $items):
            $produse['produs_' . $items->id] = DB::table('orders_products')->orderBy('order_id')
                ->join('products', 'orders_products.product_id', '=', 'products.id')
                ->where('order_id', $items->id)
                ->get();
            endforeach;
        endif;

        return $this->view->render($response, "user/comenzii.twig", [
            "items"     => $data,
            "produse"   => (isset($produse)) ?: []
        ]);
    }

    public function view(Request $request, Response $response)
    {
        //return $this->view->render($response, "user/comenzii.twig");
    }

    public function retur(Request $request, Response $response)
    {
        //return $this->view->render($response, "user/comenzii.twig");
    }
}