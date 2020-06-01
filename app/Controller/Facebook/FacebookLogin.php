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
    
    class FacebookLogin
    {
		/* @var Facebook\Facebook */
		protected $fb_app;

		/* @var Cart\Support\Storage\sessionstorage */
		protected $session;

        /* @var FB_API_KEY */
        const FB_API_KEY = '2416776561710799';
        
        /* @var FB_API_KEY */
        const FB_API_SECRECT = '22d61ee728f7daccce58cfa11200a3f8';
        
		public function __construct() {
			$this->fb_app = new Facebook([
				'app_id' 	 => self::FB_API_KEY,
				'app_secret' => self::FB_API_SECRECT,
				'default_graph_version' => 'v3.2',
				'cookie' => true
            ]);
            //FacebookSession::setDefaultApplication(self::FB_API_KEY, self::FB_API_SECRECT);

			$this->session = new SessionStorage();
        }
        
		public function loginCallback()
		{
			$helper = $this->fb_app->getRedirectLoginHelper();

			$permissions = ['email','user_link','user_photos','user_gender'];
			$loginUrl = $helper->getLoginUrl('https://shop-piesetv.ro/api/test/callback', $permissions);

			return '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
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
                $accessToken = $helper->getAccessToken();
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
            }

            $oAuth2Client = $this->fb_app->getOAuth2Client();
            if (! $accessToken->isLongLived()) {
                
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
                    exit;
                }
                
                echo '<h3>Long-lived</h3>';
                var_dump($accessToken->getValue());
            }
            
            $this->session->set('fb.login', (string) $accessToken);
			return true;
		}
    }