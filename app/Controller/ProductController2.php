<?php
/**
 * Product Controller
 */
namespace Cart\Controller;

use Slim\Router;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;

// Application Required
use Cart\Models\Product;
use Cart\Checkout\Basket;
use Cart\Checkout\Wishlist;
use Cart\Models\Customer;
use Cart\Models\Category;
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Controller\Facebook\FacebookApi as FB;



class ProductController
{
    protected $product;

    protected $customer;
    
    protected $category;

    public function __construct(StorageInterface $storage, Product $product, Customer $customer, Category $category)
    {
        $this->storage = $storage;
        $this->product = $product;
        $this->cutomer = $customer;
        $this->category = $category;
    }

    public function array_search_partial($arr, $keyword) {
        foreach($arr as $index => $string) {
            if (strpos($string, $keyword) !== FALSE)
                return $index;
        }
    }

    public function get($slug, Request $request, Response $response, Twig $view, Basket $basket, Router $router, Product $product)
    {
        $products = $product->where('slug', $slug)->first();

        var_dump($request->getParams());
        if (!$products)
        {
            return $response->withRedirect($router->pathFor('index'));
        }

        return $view->render($response, 'products/product.twig', [
            'product' => $products,
        ]);
    }

    public function add($slug, $quantity, Request $request, Response $response, Twig $view, Router $router, Product $product)
    {

    }

    public function wishlist($slug, $back, Request $request, Response $response, Twig $view, Router $router, Product $product, Wishlist $wishlist)
    {
        $product = $this->product->where('slug', $slug)->first();

        try {
            if ($wishlist->add($product)) {
                return $response->withRedirect($router->pathFor('product.get', [slug => $back]));
            }

           return $response->withRedirect($router->pathFor('product.get', [slug => $back]));
        } catch(QuantityExceededException $e) {
            echo $e->message;
        }
    }

    public function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);
    
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
    
        return $filename;
    }


    public function editNew(Request $request, Response $response, Twig $view, Router $router, Product $product, Customer $customer)
    {
        // Check customer session
        if(!$this->cutomer->logged()) {
            return $response->withRedirect($router->pathFor('index'));
        }

        $productGet = $product->where('id', '=', $request->getParam('f'))->first();
        $hiddenFields = [];


        /*if (empty($uploadedFile[0]->file))
        {
            $insertProduct = $product
                ->where('id', $request->getParam('f'))
                ->update([
                    'title' => $request->getParam('title'),
                    'description' => $request->getParam('description'),
                    'price' => $request->getParam('price'),
                    'image' => $productGet->image,
                    'galery' => $productGet->galery,
                    'stock' => $request->getParam('stock'),
                    'phone' => $request->getParam('telefon'),
                    'category' => $request->getParam('category'),
                    'slug' => $product->setSlug($request->getParam('title'))
                ]);

            return $response->withRedirect($router->pathFor('product.get', [
                'slug' => $product->setSlug($request->getParam('title')),
            ]));
        } else {
            if (count($uploadedFile)) {
                foreach ($uploadedFile as $files)
                {
                    if ($files->getError() === UPLOAD_ERR_OK)
                    {
                        $directory = basename($_SERVER['DOCUMENT_ROOT'] . "/uploads");
                        $filesName[] = $this->moveUploadedFile($directory, $files);
                    }
                }

                $insertProduct = $product
                    ->where('id', $request->getParam('f'))
                    ->update([
                        'title' => $request->getParam('title'),
                        'description' => $request->getParam('description'),
                        'price' => $request->getParam('price'),
                        'image' => $productGet->image,
                        'galery' => $productGet->galery . ',' . implode(',', $filesName),
                        'stock' => $request->getParam('stock'),
                        'phone' => $request->getParam('telefon'),
                        'category' => $request->getParam('category'),
                        'slug' => $product->setSlug($request->getParam('title'))
                    ]);

                return $response->withRedirect($router->pathFor('product.get', [
                    'slug' => $product->setSlug($request->getParam('title')),
                ]));

            } else {
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $directory = basename($_SERVER['DOCUMENT_ROOT'] . "/uploads");
                    $filename = $this->moveUploadedFile($directory, $uploadedFile);

                    $insertProduct = $product
                        ->where('id', $request->getParam('f'))
                        ->update([
                            'title' => $request->getParam('title'),
                            'description' => $request->getParam('description'),
                            'price' => $request->getParam('price'),
                            'image' => $productGet->image,
                            'galery' => $productGet->galery . ',' . $filename,
                            'stock' => $request->getParam('stock'),
                            'phone' => $request->getParam('telefon'),
                            'category' => $request->getParam('category'),
                            'slug' => $product->setSlug($request->getParam('title'))
                        ]);

                    return $response->withRedirect($router->pathFor('product.get', [
                        slug => $product->setSlug($request->getParam('title'))
                    ]));
                }
            }
        }*/
    }

    public function addNew(Request $request, Response $response, Twig $view, Router $router, Product $product, Customer $customer)
    {
        $fb = new FB();

        // Check customer session
        if(!$this->cutomer->logged()) {
            return $response->withRedirect($router->pathFor('index'));
        }

        if ($request->isPost()) {
            $errors = false;
            $hiddenFields = [];

            if ( empty($request->getParam('title')) ) {
                $errors = true;
                $this->storage->setArray('add_error', array(
                    'title' => array(
                        'msg' => "Eroare: Titlul nu a fost completat.\n"
                    )
                ));
            }

            if ( empty($request->getParam('description')) ) {
                $errors = true;
                $this->storage->setArray('add_error', array(
                    'desc' => array(
                        'msg' => "Eroare: Descrierea nu a fost completata.\n"
                    )
                ));
            }

            if ( empty($request->getParam('price')) ) {
                $errors = true;
                $this->storage->setArray('add_error', array(
                    'price' => array(
                        'msg' => "Eroare: Pretul produsului nu este completat"
                    )
                ));
            }

            if ( empty($request->getParam('category')) ) {
                $errors = true;
                $this->storage->setArray('add_error', array(
                    'category' => array(
                        'msg' => "Eroare: Va rugam sa selectati o categorie pentru produs"
                    )
                ));
            }

            /*if($errors == true) {
                return $response->withRedirect($router->pathFor('product.new'));
            }*/

            $patterns = $request->getParams();

            $datamSG = "";
            $datamSG = "array(\n";
            foreach ($patterns as $key => $value) {
                $datamSG .= "{$key} => {$value},\n";
            }
            $datamSG .= "\n);";

            $row = preg_match("/images_/is", var_export($patterns, true));
            if($row) {
                $matches = null;
                $returnValue = preg_match_all('/images_(\w+)/is', $datamSG, $matches, PREG_SET_ORDER, 0);
                print_r(implode(',', $matches));
            }

            if($errors == false) {
                    $insertProduct = $product->insertGetId([
                        'title' => $request->getParam('title'),
                        'description' => $request->getParam('description'),
                        'price' => $request->getParam('price'),
                        'image' => "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR . $matches[0][1],
                        'galery' => implode(',', $matches),
                        'stock' => $request->getParam('stock'),
                        'phone' => $request->getParam('telefon'),
                        'category' => $request->getParam('category'),
                        'slug' => $product->setSlug($request->getParam('title'))
                    ]);

                    $link = "https://shop-piesetv.ro/products/" . $product->setSlug($request->getParam('title'));

                    $messages = $request->getParam('title') .
                        "\n-------------------------------------\n
                            Pret: " . $request->getParam('price') . " Lei\n
                            Descriere: " . $request->getParam('description') .
                        "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR . $filesName[0];
                    $resMsg = str_replace(array("<p>", "</p>", "&nbsp;", "<br>"), " ", $messages);
                    $arr = array(
                        'message' => $resMsg,
                        'link' => $link,
                        'source' => "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR . $filesName[0]
                    );

                    if ($this->storage->get('fb.state'))
                    {
                        $fb->doShare($arr);
                    }

                    return $response->withRedirect($router->pathFor('product.get', [
                        slug => $product->setSlug($request->getParam('title')),
                        q => 1
                    ]));
            }
        }

        /*if ($request->isPost()) {
            $errors = false;
            $uploadedFiles = $request->getUploadedFiles();
            $uploadedFile = $uploadedFiles['images'];

            if($errors == false) {
                if (count($uploadedFile)) {
                	foreach ($uploadedFile as $files)
                	{
                		if ($files->getError() === UPLOAD_ERR_OK)
                		{
	                    	$directory = basename($_SERVER['DOCUMENT_ROOT'] . "/uploads");
	                    	$filesName[] = $this->moveUploadedFile($directory, $files);
                		}
                	}

                    $insertProduct = $product->insertGetId([
                        'title' => $request->getParam('title'),
                        'description' => $request->getParam('description'),
                        'price' => $request->getParam('price'),
                        'image' => "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR . $filesName[0],
                        'galery' => implode(',', $filesName),
                        'stock' => $request->getParam('stock'),
                        'phone' => $request->getParam('telefon'),
                        'category' => $request->getParam('category'),
                        'slug' => $product->setSlug($request->getParam('title'))
                    ]);

                    $link = "https://shop-piesetv.ro/products/" . $product->setSlug($request->getParam('title'));

                    $messages = $request->getParam('title') .
                        "\n-------------------------------------\n
                            Pret: " . $request->getParam('price') . " Lei\n
                            Descriere: " . $request->getParam('description') .
                        "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR . $filesName[0];
                    $resMsg = str_replace(array("<p>", "</p>", "&nbsp;", "<br>"), " ", $messages);
                    $arr = array(
                        'message' => $resMsg,
                        'link' => $link,
                        'source' => "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR . $filesName[0]
                    );

                    if ($this->storage->get('fb.state'))
                    {
                        $fb->doShare($arr);
                    }

                    return $response->withRedirect($router->pathFor('product.get', [
                        slug => $product->setSlug($request->getParam('title')),
                        q => 1
                    ]));

                } else {
	                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
	                    $directory = basename($_SERVER['DOCUMENT_ROOT'] . "/uploads");
	                    $filename = $this->moveUploadedFile($directory, $uploadedFile);

	                    $insertProduct = $product->insertGetId([
	                        'title' => $request->getParam('title'),
	                        'description' => $request->getParam('description'),
	                        'price' => $request->getParam('price'),
	                        'image' => "https://shop-piesetv.ro/" . $directory . DIRECTORY_SEPARATOR . $filename,
	                        'stock' => $request->getParam('stock'),
                            'phone' => $request->getParam('telefon'),
	                        'category' => $request->getParam('category'),
	                        'slug' => $product->setSlug($request->getParam('title'))
	                    ]);

                        return $response->withRedirect($router->pathFor('product.get', [
                            slug => $product->setSlug($request->getParam('title'))
                        ]));
	                }
                }
            }
        }*/

        if ($request->isGet()) {
            $category = $this->category->orderBy('id', 'asc')->get();
        
            $template = $view->render($response, '/products/add.twig', [
                'cat' => $category,
                'errors' => $this->storage->get('add_error'),
                'fbLogin' => $fb->doLink(),
            ]);

            $this->storage->unset('add_error');

            return $template;
        }
    }

    public function delete(Request $request, Response $response, Product $product, $args = [])
    {
    	$data = $request->getParams();

    	if(!empty($data)) {
    		$id = $data['f'];
    		$title = $data['t'];
    		$price = $data['p'];
    		$redirect = $data['redirect'];

    		$product->where('id', '=', $id)->delete();

    		return $response->withRedirect($redirect);
    	}
    }

    public function modify(Request $request, Response $response, Twig $view, Router $router, Product $product, Customer $customer, $args = array())
    {
        if ($customer->admin()) {
            $category = $this->category->orderBy('id', 'asc')->get();
            $products = $product->where('id', $request->getParam('f'))->first();

            $template = $view->render($response, '/products/modify.twig', [
                'cat' => $category,
                'mode' => 'modify',
                'product'   => $products
            ]);

            return $template;
        }
        else {
            return $response->withRedirect($router->pathFor('index'));
        }
    }
}