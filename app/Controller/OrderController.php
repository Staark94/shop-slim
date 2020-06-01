<?php

namespace Cart\Controller;

use Slim\Router;
use Slim\Views\Twig;
use Cart\Checkout\Basket;
use Cart\Checkout\Order;
use Cart\Models\Product;
use Cart\Models\Customer;
use Cart\Controller\Mail\MailController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as DB;

class OrderController
{
    protected $basket;

    protected $product;

    public function __construct(Basket $basket, Product $product)
    {
        $this->basket = $basket;
        $this->product = $product;
    }

    public function index(Request $request, Response $response, Twig $view, Customer $users, Router $router)
    {
        if(empty($this->basket->all())) {
            return $response->withRedirect($router->pathFor('index'));
        }

        $this->basket->refresh();
        return $view->render($response, 'cart/checkout.twig');
    }

    public function complete(Request $request, Response $response, Twig $view, Basket $cartout, Customer $users, Router $router)
    {
        $mailer = new MailController;
        $errors = false;
        $okField = "";
        $postData = $request->getParams();

        // Validate all fields
        foreach($postData as $items => $value) {
            if(isset($items) && empty($value)) {
                echo "Field {$items} nu este completat.<br /><br />";
                $errors = true;
            }
        }

        if($errors == true) {
            return $response->withRedirect($router->pathFor('cart.order'));
        } else {
            $hash = bin2hex(random_bytes(32));
            $lastUser = -1;
            $paid = 0;

            if(isset($postData) && !empty($postData['userNow']) && !empty($postData['emailNow']))
            {
                $lastUser = Customer::insertGetId([
                    'name' => $postData['userNow'],
                    'email' => $postData['emailNow'],
                    'password' => password_hash('randomPass', PASSWORD_DEFAULT),
                    'localitate' => '',
                    'judet' => $postData['state']
                ]);
            }
            
            if (!isset($postData['userNow']) && !isset($postData['emailNow'])) {
                $lastUser = $users->getId()['id'];
            }

            $addresseID = DB::table('addresses')->insertGetId([
                'addresse1'     => $postData['address'],
                'addresse2'     => $postData['telefon'],
                'city'          => $postData['state'],
                'postal_code'   => $postData['zip'],
                'created_at'    => date('Y-m-d H:i:s', time())
            ]);

            if (isset($postData['paymentMethod1'])) {
                $paid = 1; 
            } elseif (isset($postData['paymentMethod2'])) {
                $paid = 2;
            }

            $orderID = DB::table('orders')->insertGetId([
                'hash'          => $hash,
                'total'         => (int) $cartout->subTotal(),
                'address_id'    => $addresseID,
                'paid'          => $paid,
                'customer_id'   => $lastUser,
                'created_at'    => date('Y-m-d H:i:s', time()),
            ]);


            foreach($cartout->all() as $items)
            {
                $orders = DB::table('orders_products')
                    ->insertGetId([
                        'order_id'      => $orderID,
                        'product_id'    => $items['id'],
                        'quantity'      => $items['quantity']
                    ]);

                DB::table('products')
                    ->where('id', $items['id'])
                    ->decrement('stock', $items['quantity']);
            }

            echo "Comanda trimisa !";
            $mailer->sendCheckout($postData, $this->basket->all(), $hash);
            $mailer->sendMailUser($postData['email'], $postData, $this->basket->all(), $hash);

            if (isset($postData['paymentMethod1'])) {
                $result = $view->render($response, 'cart/complete.twig', [
                    'address'       => $postData,
                    'products'      => $cartout->all(),
                    'paymentMethod' => 1
                ]);
                
                $cartout->clear();
                $cartout->refresh();
                return $result;
            } elseif (isset($postData['paymentMethod2'])) {
                $result = $view->render($response, 'cart/complete.twig', [
                    'address'       => $postData,
                    'products'      => $cartout->all(),
                    'paymentMethod' => 2
                ]);

                $cartout->clear();
                $cartout->refresh();
                return $result;
            }
        }

        /*
        $hash = bin2hex(random_bytes(32));
        $mailer->sendCheckout($postData, $this->basket->all(), $hash);
        echo "Comanda trimisa !";
        $mailer->sendMailUser($postData['email'], $postData, $this->basket->all(), $hash);
        */
        die();
        /*if($errors == false) {
            if (isset($postData['paymentMethod1'])) {
                $result = $view->render($response, 'cart/complete.twig', [
                    'address'       => $postData,
                    'products'      => $this->basket->all(),
                    'paymentMethod' => 1
                ]);
                
                $this->basket->clear();
                $this->basket->refresh();
                return $result;
            } elseif (isset($postData['paymentMethod2'])) {
                $result = $view->render($response, 'cart/complete.twig', [
                    'address'       => $postData,
                    'products'      => $this->basket->all(),
                    'paymentMethod' => 2
                ]);

                $this->basket->clear();
                $this->basket->refresh();
                return $result;
            }
        }*/
    }
}