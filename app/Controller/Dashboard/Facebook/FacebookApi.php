<?php
/**
 *
 *
 *
 *
*/

namespace Cart\Controller\Facebook;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Cart\Support\Storage\SessionStorage;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class FacebookApi
{
	public $fb_app;
	protected $storage;
	protected $view;

	public function __construct()
	{
		$this->fb_app = new Facebook([
			'app_id' => '348296549388107',
			'app_secret' => '28d065d94f56c39b52eabb410ac01b64',
			'default_graph_version' => 'v2.9',
		]);
		$this->storage = new SessionStorage();
	}

	public function doLogin(Request $request, Response $response, Twig $view)
	{

		$helper = $this->fb_app->getRedirectLoginHelper();
		$permissions = ['manage_pages','publish_pages'];
		$loginUrl = $helper->getLoginUrl('https://shop-piesetv.ro/api/callback', $permissions);

		if($this->storage->get('fb_state'))
		{
			// return $response->withRedirect('/');
		}

		return $view->render($response, 'fb_post.twig', [
			'url'=>$loginUrl
		]);
	}

	public function doCallback(Request $request, Response $response)
	{
		$helper = $this->fb_app->getRedirectLoginHelper();
		$get = $_GET['state'];
		$this->storage->set('fb_state', $get);

		try {
		 	$accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		 	echo 'Graph returned an error: ' . $e->getMessage();
		 	exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  	echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}

		if (! isset($accessToken)) {
		  	echo 'No OAuth data could be obtained from the signed request. User has not authorized your app yet.';
		  	exit;
		}

		try {
		  	$response = $this->fb_app->get('me/accounts', $accessToken->getValue());
		  	$response = $response->getDecodedBody();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  	echo 'Graph returned an error: ' . $e->getMessage();
		  	exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  	echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  	exit;
		}

		$this->storage->set('fb_token', $response['data'][0]['access_token']);
		echo $this->storage->get('fb_token');
		echo "<a href='/'>Return to index</a>";
	}

	public function doPost($data = array())
	{
		$res = $this->fb_app->post('109381267092135/feed/', $data, $this->storage->get('fb_token'));
	}
}