<?php

namespace Cart\Controller\Dashboard;

use Slim\Router;
use Slim\Views\Twig;
use Cart\Models\Customer;
use Cart\Models\Product;
use Cart\Models\Category;
use Cart\Models\SubCat;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as DB;

class AdminController
{
    protected $view;
    protected $customer;
    protected $product;
    protected $category;
    protected $router;

    protected $index;

    public function __construct(Router $router, Twig $view, Customer $customer, Product $product, Category $category)
    {
        $this->view     = $view;
        $this->customer = $customer;
        $this->product  = $product;
        $this->category = $category;
        $this->router   = $router;
    }

    public function selectCat(Request $request, Response $response, Twig $view, Category $category, SubCat $subCat)
    {
        $result1 = DB::table('sub_category')->orderBy('parent')->get();
        $result = DB::table('category')->orderBy('pos')->get();

        return $this->view->render($response, 'selectcat.twig', [
            'subcat' => $subCat,
            'result' => [
                'cat' => $result,
                'subCat' => $result1
            ]
        ]);
    }

    public function addSubCat(Request $request, Response $response, Twig $view, Category $category, SubCat $subCat)
    {
        if (!$this->customer->logged() && !$this->customer->admin()) {
            return $response->withRedirect($this->router->pathFor('index'));
        }

        if($request->isPost()) {
            $data = $request->getParams();
            if(isset($data['send'])) {
                if( !empty($data['sub_cat_name']) )
                {
                    $id = DB::table('sub_category')
                    ->insertGetId(array(
                        "name" => $data['sub_cat_name'],
                        "parent" => $data['cat_id'],
                        "slug" => strtolower(urlencode($data['sub_cat_name'])),
                        "pos" => (!empty($data['pos'])) ? '1' : $data['pos'] + 1
                    ));
                
                    return $response->withRedirect( $this->router->pathFor('cat.index', [ slug => $data['cat_slug'] ]) );
                }
            }
        }

        if($request->isGet())
        {
            $cat = $request->getQueryParams()['category'];
            
            $result1 = DB::table('sub_category')->where('parent', $cat)->orderBy('pos')->get();
            $result = DB::table('category')->where('id', $cat)->get();

            return $this->view->render($response, 'addsubcategory.twig', [
                'result' => [
                    'cat' => $result[0],
                    'subCat' => $result1
                ]
            ]);
        }
    }

    public function add(Request $request, Response $response, Twig $view, Category $category)
    {
        if (!$this->customer->logged() && !$this->customer->admin()) {
            return $response->withRedirect($this->router->pathFor('index'));
        }

        if($request->isPost()) {
            $data = $request->getParams();

            if(isset($data['send'])) {
                if(!empty($data['cat_name']) && !empty($data['cat_pos']))
                {
                    $slug = $category->setSlug($data['cat_name']);
                    $id = DB::table('category')
                        ->insertGetId(array(
                            "name" => $data['cat_name'],
                            "slug" => $slug,
                            "pos" => (int)$data['pos'],
                            "created_at" => \Carbon\Carbon::now(),
                            "update_at" => \Carbon\Carbon::now(),
                        ));
                    
                        return $response->withRedirect( $this->router->pathFor('cat.index', [ slug => $slug ]) );
                } else {
                    return $response->withRedirect( $this->router->pathFor('admin.cat', [ method => 'cat' ]) );
                }
            } else {
                return $response->withRedirect($this->router->pathFor('index'));
            }
        }

        if($request->isGet())
        {
            $result = DB::table('category')->orderBy('pos')->get();

            return $this->view->render($response, 'addcat.twig', [
                'result' => [
                    'cat' => $result
                ]
            ]);
        }
    }

    public function orders(Request $request, Response $response)
    {

    }

    public function prods(Request $request, Response $response)
    {

    }

    public function users(Request $request, Response $response)
    {

    }
}