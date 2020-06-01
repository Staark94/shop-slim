<?php
namespace Cart\Controller\Auth;

use Slim\Router;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Cart\Models\Customer;
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Support\Storage\CacheStorage as Cache;
use Illuminate\Database\Capsule\Manager as DB;
use Cart\Controller\Mail\MailController;


class AuthController
{
    public function index(Request $request, Response $response, Twig $view, Customer $user, Router $router)
    {
        return $view->render($response, '/user/profile.twig', [
            'user' => $user,
        ]);
    }

    public function login(Request $request, Response $response, Twig $view, Customer $user, Router $router, StorageInterface $storage)
    {
        $path = $request->getUri()->getPath();
        $path = "/" . trim($path, "/");

        if (!in_array($path, $user::$allowNotAuth)) {
            if($user->logged() && strcasecmp($path, "/auth/logout") == 0) {
                setcookie("username", "", time() - (86400 * 30), '/');
                setcookie("logged", "", time() - (86400 * 30), '/');

                return $response->withRedirect($router->pathFor('index'));
            } else {
                return $response->withRedirect($router->pathFor('auth.login'));
            }
        }

        return $view->render($response, '/user/login.twig', [
            'errors' => $storage->get('login_errors')
        ]);
    }

    public function register(Request $request, Response $response, Twig $view, Customer $user, Router $router)
    {
        if($user->logged()) {
            return $response->withRedirect($router->pathFor('index'));
        }

        return $view->render($response, '/user/register.twig');
    }

    public function loginPost(
        Request $request,
        Response $response, 
        Twig $view, 
        Customer $users, 
        Router $router, 
        StorageInterface $storage
    )
    {
        $email = $request->getParam('email');
        $password = $request->getParam('password');
        $storage->unset('login_errors');

        $user = $users
            ->select('email', 'password', 'name')
            ->where('email', $email)
            ->get()
            ->toArray();

        if(empty($user)) {
            $storage->setArray('login_errors', [
                'user' => 'Utilizatorul cu acest email nu a fost gasit in baza de date.'
            ]);

            return $response->withRedirect($router->pathFor('auth.login'));
        }

        if(!empty($user)) {
            if(isset($_COOKIE['logged']))
            {
                $storage->setArray('login_errors', [
                    'session' => 'Exista deja o sessiune pe acest user, verificati cu atentie.'
                ]);

                return $response->withRedirect($router->pathFor('auth.login'));
            }

            if (password_verify($password, $user[0]['password'])) {
                setcookie("username", $user[0]['name'], time() + (86400 * 30), '/'); // 1 day SAVE session login
                setcookie("logged", 1, time() + (86400 * 30), '/'); // 1 day SAVE session login
                $storage->unset('login_errors');
                return $response->withRedirect($router->pathFor('index'));
            } else {
                $storage->setArray('login_errors', [
                    'wrong' => 'Parola introdusa nu este valida sau nu a fost resetata.'
                ]);

                return $response->withRedirect($router->pathFor('auth.login'));
            }
        }
    }

    public function registerPost(Request $request, Response $response, Twig $view, Customer $users, Router $router)
    {
        $errors = [];
        $check_name = $users->userExists($request->getParam('name'));
        $check_mail = $users->mailExists($request->getParam('email'));
        
        
        if (!$check_name) $errors['name'] = true;
        if (!$check_mail) $errors['mail'] = true;
        if (empty($request->getParam('password')))  $errors['password'] = true;
        if (empty($request->getParam('address1')))  $errors['address1'] = true;
        if (empty($request->getParam('address2')))  $errors['address2'] = true;

        if(empty($errors))
        {
            $users = Customer::create([
                'name' => $request->getParam('name'),
                'email' => $request->getParam('email'),
                'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
                'localitate' => $request->getParam('address1'),
                'judet' => $request->getParam('address2')
            ]);

            return $response->withRedirect($router->pathFor('index'));
        } else {
            return $response->withRedirect($router->pathFor('auth.register'));
        }
    }
}