<?php
namespace Cart\Controller;

use Slim\Views\Twig;
use Cart\Models\Product;
use Cart\Models\Category;
use Cart\Models\Customer;
use Cart\Checkout\Basket;
use Cart\Checkout\Wishlist;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Support\Storage\CacheStorage as Cache;
use Illuminate\Database\Capsule\Manager as DB;
use Cart\Controller\Mail\MailController;

class HomeController
{
    public function index(
        Request $request,
        Response $response,
        Twig $view,
        Customer $customer,
        Product $product,
        Basket $basket,
        Wishlist $wishlist,
        Category $category,
        StorageInterface $storage
    )
    {
        if(!$storage->get('wishlist')) {
            $storage->unset('wishlist');
        }
        $storage->unset('add_error');

        $category   = $category->orderBy('pos', 'asc')->get();
        $last       = $product->orderBy('id', 'desc')->first();

        $template = $view->render($response, 'home.twig', [
            'cats' => $category,
            'last_product'  => $last
        ]);

        return $template;
    }

    public function faq(Request $request, Response $response, Twig $view)
    {
        return $view->render($response, 'faq.twig');
    }

    public function retur(Request $request, Response $response, Twig $view)
    {
        return $view->render($response, 'retur.twig');
    }

    public function search(Request $request, Response $response, Twig $view, Product $product)
    {
        $q = $request->getParam('query');

        $product = $product
            ->where('title', 'LIKE', '%'. $q .'%')
            ->get();

        $template = $view->render($response, 'search.twig', [
            'searchItems' => $product
        ]);

        return $template;
    }

    public function newsproducts(
        Request $request,
        Response $response,
        Twig $view,
        MailController $mailler
    )
    {
        $users      = DB::table('customers')->get();
        $products   = DB::table('products')->whereDay('created_at', date("d"))->limit(10)->get();

        foreach ($users as $user) {
            $mailler->sendMailNews($user->email, $products);
        }

        die();
    }

    public function contact(
        Request $request,
        Response $response,
        Twig $view,
        MailController $mailler
    )
    {
        if($request->isPost()) {
            $fields = $request->getParams();
            $errors = array();
            $post = false;

            foreach ($fields as $item) {
                if(isset($item) && !empty($item)) {
                    //
                } else {
                    $errors[$item] = "Unul sau mai multe campuri sunt goale, trebuie completate.";
                }
            }

            if(empty($errors)) {
                $message = "Nume & Prenume: {$fields['userName']}<br />
                Email Contact: {$fields['userMail']}<br />
                Mesaj:". htmlspecialchars($fields['msgText']);
                $mailler->contact([
                    'mail' => $fields['userMail'],
                    'message' => $message
                ]);
                $post = true;
            }

            return $view->render($response, "contact.twig", [
                'errors' => $errors,
                'post' => $post,
            ]);
        }

        if($request->isGet()) {
            return $view->render($response, "contact.twig", [
                'page_title' => 'Contact',
            ]);
        }
    }
}