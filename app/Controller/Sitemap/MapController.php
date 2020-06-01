<?php

	namespace Cart\Controller\Sitemap;

	// Usage
	use Slim\Router;
	use Slim\Views\Twig;
	use Cart\Models\Customer;
	use Cart\Models\Product;
	use Cart\Models\Category;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Psr\Http\Message\ResponseInterface as Response;
	use Illuminate\Database\Capsule\Manager as DB;

	class MapController {
		private $sitemap;
		private $priority;
	}