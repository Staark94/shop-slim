<?php
namespace Cart\Controller;

use Slim\Views\Twig;
use Cart\Models\Category;
use Cart\Models\Product;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Cart\Support\Storage\CacheStorage as Cache;
use Illuminate\Database\Capsule\Manager as DB;

class CategoryController
{

    public function showsub($slug, $sub, Request $request, Response $response, Twig $view, Category $category) {
        $category = $category->where('slug', $slug)->first();
        $list = DB::table('sub_category')->where('slug', urlencode($sub))->first();

        // Pagination
        $page      = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
        $limit     = 10;
        $skip      = ($page - 1) * $limit;
        $count     = count(Product::where('sub_category', $list->id)->get());

        return $view->render($response, 'category/index.twig', [
            'pagination'    => [
                'needed'        => $count > $limit,
                'count'         => $count,
                'page'          => $page,
                'lastpage'      => (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit)),
                'limit'         => $limit,
            ],

            'cat' => $category,
            'list' => $list
        ]);
    }

    public function index($slug, Request $request, Response $response, Twig $view, Category $category)
    {
        $category = $category->where('slug', $slug)->first();
        $list = DB::table('sub_category')->where('parent', $category->id)->get();

        // Pagination
        $page      = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
        $limit     = 10;
        $skip      = ($page - 1) * $limit;
        $count     = count(Product::where('category', $category->id)->get());

        //Cache::set("cachefile", "sql", $category);

        return $view->render($response, 'category.twig', [
            'pagination'    => [
                'needed'        => $count > $limit,
                'count'         => $count,
                'page'          => $page,
                'lastpage'      => (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit)),
                'limit'         => $limit,
            ],

            'cat' => $category,
            'list' => $list
        ]);
    }
}