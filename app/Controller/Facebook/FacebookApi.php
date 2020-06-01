<?php
	/*
	 * Facebook API Class for auto post on wall, login, and other callback
	 * All right reserverd (c) ElectronicForum Group
	 * Licence GNU v2
	*/

	namespace Cart\Controller\Facebook;

	use Slim\Views\Twig;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Psr\Http\Message\ResponseInterface as Response;
	use Facebook\Facebook;
	use Facebook\FacebookRequest;
	use Facebook\Exceptions\FacebookResponseException;
	use Facebook\Exceptions\FacebookSDKException;
	use Cart\Support\Storage\SessionStorage;


	class FacebookApi
	{
		/* @var Facebook\Facebook */
		protected $fb_app;

		/* @var Cart\Support\Storage\sessionstorage */
		protected $session;

		const FB_API_KEY = '348296549388107'; // 348296549388107
		
		const FB_API_SECRECT = '28d065d94f56c39b52eabb410ac01b64'; // 

		public function __construct() {
			$this->fb_app = new Facebook([
				'app_id' 	 => self::FB_API_KEY,
				'app_secret' => self::FB_API_SECRECT,
				'default_graph_version' => 'v3.2',
				'cookie' => true
			]);

			$this->session = new SessionStorage();
		}

		public function doLink()
		{
			$helper = $this->fb_app->getRedirectLoginHelper();

			$permissions = ['email','manage_pages','publish_pages'];
			$loginUrl = $helper->getLoginUrl('https://shop-piesetv.ro/api/callback', $permissions);

			return $loginUrl;
		}

		public function doLogin(Request $request, Response $response, Twig $view)
		{
			$helper = $this->fb_app->getRedirectLoginHelper();

			$permissions = ['email','manage_pages','publish_pages'];
			$loginUrl = $helper->getLoginUrl('https://shop-piesetv.ro/api/callback', $permissions);

			return $view->render($response, 'fb_post.twig', ['url' => $loginUrl]);
		}

		public function doCallback()
		{
			$helper = $this->fb_app->getRedirectLoginHelper();
			$helper->getPersistentDataHandler()->set('state', $_GET['state']);
			
			$this->session->set('fb.state', $_GET['state']);

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

			$this->session->set('fb.accessToken', $response['data'][0]['access_token']);

			return true;
		}

		public function doShare($data = array())
		{
			$response = $this->fb_app->post('/shop.piesetv/feed/', $data, $this->session->get('fb.accessToken'));
		}
	}