<?php
/**
 * Define routes
*/
use Cart\Controller\Facebook\FacebookApi;
use Cart\Controller\Facebook\FacebookLogin;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;


function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

$app->get('/', ['\Cart\Controller\HomeController', 'index'])->setName('index');
$app->get('/search', ['\Cart\Controller\HomeController', 'search'])->setName('search');

$app->group('/api', function() use ($app) {
    $app->get('/callback', function(Request $request, Response $response) {
        $fb = new FacebookApi();

        if ($fb->doCallback($request, $response)) {
            return $response->withRedirect('/products/add/new');
        }
    });

    $app->post('/shop/upload', function(Request $request, Response $response) {
        if($request->isPost()) {
            if(!empty($request->getParam("hash")))
            {
                $uploadedFiles = $request->getUploadedFiles();
                $uploadedFile = $uploadedFiles['file'];
                $hasFiles = $request->getParam("hash");

                if ($uploadedFile->getError() === UPLOAD_ERR_OK)
                {
                    $directory = basename($_SERVER['DOCUMENT_ROOT'] . "/uploads");
                    $filesName = moveUploadedFile($directory, $uploadedFile);

                    echo $filesName;
                }
            } else {
                return;
            }
        }
    });
});

$app->get('/contact', ['\Cart\Controller\HomeController', 'contact'])->setName('contact');
$app->post('/contact', ['\Cart\Controller\HomeController', 'contact'])->setName('contact.post');

$app->get('/category/{slug}', ['\Cart\Controller\CategoryController', 'index'])->setName('cat.index');
$app->get('/category/{slug}/{sub}', ['\Cart\Controller\CategoryController', 'showsub'])->setName('cat.show');
$app->get('/products/{slug}', ['\Cart\Controller\ProductController', 'get'])->setName('product.get');
$app->post('/products/update/{slug}', ['\Cart\Controller\ProductController', 'add'])->setName('product.add');
$app->get('/products/wishlist/{slug}/{back}', ['\Cart\Controller\ProductController', 'wishlist'])->setName('product.fav');
$app->get('/products/add/new', ['\Cart\Controller\ProductController', 'addNew'])->setName('product.new');
$app->post('/products/add/new', ['\Cart\Controller\ProductController', 'addNew'])->setName('product.new.post');
$app->post('/products/modify', ['\Cart\Controller\ProductController', 'editNew'])->setName('product.modify.post');

// Other
$app->get('/faq', ['\Cart\Controller\HomeController', 'faq'])->setName('faq');
$app->get('/retur', ['\Cart\Controller\HomeController', 'retur'])->setName('retur');
$app->get('/delete/product/{id:[0-9]+}', ['\Cart\Controller\ProductController', 'delete'])->setName('del.prod');
$app->get('/modify/product/{id:[0-9]+}', ['\Cart\Controller\ProductController', 'modify'])->setName('edit.prod');


// DashBoard
$app->group('/admin', function() use ($app) {
    $app->get('/add/{method}', ['\Cart\Controller\Dashboard\AdminController', 'add'])->setName('admin.cat');
    $app->post('/add/{method}', ['\Cart\Controller\Dashboard\AdminController', 'add'])->setName('admin.cat.post');

    $app->get('/create/subcat', ['\Cart\Controller\Dashboard\AdminController', 'addSubCat'])->setName('admin.sub_cat');
    $app->post('/create/subcat', ['\Cart\Controller\Dashboard\AdminController', 'addSubCat'])->setName('admin.sub_cat.post');
    $app->get('/create/subcat/select', ['\Cart\Controller\Dashboard\AdminController', 'selectCat'])->setName('admin.sub_cat.select');

    $app->get('/orders', ['\Cart\Controller\Dashboard\AdminController', 'orders'])->setName('admin.orders');
    $app->get('/products', ['\Cart\Controller\Dashboard\AdminController', 'prods'])->setName('admin.products');
    $app->get('/users', ['\Cart\Controller\Dashboard\AdminController', 'users'])->setName('admin.users');
});

$app->group('/cart', function() use ($app) {
    $app->get('/basket', ['\Cart\Controller\CartController', 'index'])->setName('cart');
    $app->get('/add/{slug}/{quantity}', ['\Cart\Controller\CartController', 'add'])->setName('cart.add');
    $app->post('/update/{slug}', ['\Cart\Controller\CartController', 'update'])->setName('cart.update');
    $app->get('/order', ['\Cart\Controller\OrderController', 'index'])->setName('cart.order');
    $app->post('/order/complete', ['\Cart\Controller\OrderController', 'complete'])->setName('cart.order.post');
});

$app->group('/auth', function() use ($app) {
    $app->get('/login', ['\Cart\Controller\Auth\AuthController', 'login'])->setName('auth.login');
    $app->get('/register', ['\Cart\Controller\Auth\AuthController', 'register'])->setName('auth.register');

    $app->get('/logout', ['\Cart\Controller\Auth\AuthController', 'login'])->setName('auth.logout');

    // Posts
    $app->post('/register', ['\Cart\Controller\Auth\AuthController', 'registerPost'])->setName('auth.register.post');
    $app->post('/login', ['\Cart\Controller\Auth\AuthController', 'loginPost'])->setName('auth.login.post');

    $app->get('/account', ['\Cart\Controller\Auth\AuthController', 'index'])->setName('auth.account');
});

$app->group('/account', function() use ($app) {
    $app->get('/comenzii', ['\Cart\Controller\Auth\UserController', 'index'])->setName('user.cmd');
    $app->get('/comenzii/view/{id}/{key}', ['\Cart\Controller\Auth\UserController', 'view'])->setName('user.cmd.view');
    $app->get('/retur', ['\Cart\Controller\Auth\UserController', 'retur'])->setName('user.retur');
});