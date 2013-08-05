<?php 

class UserController extends Ajde_User_Controller
{
	protected $_allowedActions = array(
		'logon',
		'logoff',
		'register',
		'keepalive'
	);
	protected $_logonRoute = 'user/logon/html';
	protected $includeDomain = false;
	
	public function beforeInvoke()
	{
		if (substr($_GET['_route'], 0, 5) == 'admin' || substr($_GET['returnto'], 0, 5) == 'admin' || (($user = $this->getLoggedInUser()) && $user->getUsergroup() != UserModel::USERGROUP_USERS)) {
			Ajde::app()->getDocument()->setLayout(new Ajde_Layout('belay'));
		}
		Ajde_Cache::getInstance()->disable();
		return parent::beforeInvoke();
	}
	
	// Default to profile
	public function view()
	{
		return $this->profile();
	}
	
	public function app()
	{
		$user = $this->getLoggedInUser();
		$this->getView()->assign('user', $user);		
		return $this->render();
	}
	
	public function keepaliveJson()
	{
		return array('success' => true);
	}
	
	// Profile
	public function profile()
	{
		$user = $this->getLoggedInUser();
		$this->setAction('profile');
		$user->refresh();
		$user->login();
		$this->getView()->assign('user', $user);
		
		return $this->render();
	}
    
    // Settings
	public function settingsHtml()
	{
		$user = $this->getLoggedInUser();
		$this->getView()->assign('user', $user);
		
		return $this->render();
	}
    
    public function settingsJson()
	{
		$user = $this->getLoggedInUser();
        
        if (!$user) {
            $return = array(
				'success' => false,
				'message' => __("Not logged in")
			);	
        }
		
		$returnto		= 'user/profile';
		
		$username		= Ajde::app()->getRequest()->getPostParam($user->usernameField);
		$password		= Ajde::app()->getRequest()->getPostParam('password');
		$passwordCheck	= Ajde::app()->getRequest()->getPostParam('passwordCheck');
		$email			= Ajde::app()->getRequest()->getPostParam('email', false);
		$fullname		= Ajde::app()->getRequest()->getPostParam('fullname', false);
		
		$return = array(false);
        
		if (empty($username)) {
			$return = array(
				'success' => false,
				'message' => __("Please provide a ".$user->usernameField)
			);	
		} else if (!$user->canChangeUsernameTo($username)) {
			$return = array(
				'success' => false,
				'message' => __(ucfirst($user->usernameField) . " already exist")
			);	
		} else if ($password && $password !== $passwordCheck) {
			$return = array(
				'success' => false,
				'message' => __("Passwords do not match")
			);
		} else if (empty($email)) {
			$return = array(
				'success' => false,
				'message' => __("Please provide an e-mail address")
			);
		} else if (Ajde_Component_String::validEmail($email) === false) {
			$return = array(			
				'success' => false,
				'message' => __('Please provide a valid e-mail address')
			);
		} else if (!$user->canChangeEmailTo($email)) {
			$return = array(
				'success' => false,
				'message' => __("A user with this e-mail address already exist")
			);	
		} else if (empty($fullname)) {
			$return = array(
				'success' => false,
				'message' => __("Please provide a full name")
			);			
		} else {
            $user->set($user->usernameField, $username);
			$user->set('email', $email);
			$user->set('fullname', $fullname);
            if ($password) {
                $hash = $user->createHash($password);                
                $user->set($user->passwordField, $hash);
            }
            if ($user->save()) {
                Ajde_Session_Flash::alert(__('Your settings have been saved'));
                $return = array(
                    'success' => true,
                    'returnto' => $returnto
                );
			} else {
				$return = array(
					'success' => false,
					'message' => __("Something went wrong")
				);	
			}			
		}
		return $return;
	}
	
	// Logon
	public function logonHtml()
	{
		if (($user = $this->getLoggedInUser())) {
			if (($returnto = Ajde::app()->getRequest()->getParam('returnto', false))) {
				return $this->redirect($returnto);
			}
			$this->setAction('relogon');
			$message = Ajde::app()->getRequest()->getParam('message', '');
			$this->getView()->assign('message', $message);
			$this->getView()->assign('user', $user);
			return $this->render();
		} else {
			$user = new UserModel();
			$this->setAction('logon');
//			$message = Ajde::app()->getRequest()->getParam('message', 'Please login');
			$this->getView()->assign('message', $message);
			$this->getView()->assign('user', $user);
			$this->getView()->assign('returnto', Ajde::app()->getRequest()->getParam('returnto', $_SERVER['REDIRECT_STATUS'] == 200 ? 'user' : false));
			return $this->render();
		}		
	}
		
	public function logonJson()
	{
		$user = new UserModel();
		
		$username	= Ajde::app()->getRequest()->getPostParam($user->usernameField);
		$password	= Ajde::app()->getRequest()->getPostParam('password');
		$rememberme = Ajde::app()->getRequest()->hasPostParam('rememberme');
				
		$return = array(false);
		
		if (false !== $user->loadByCredentials($username, $password)) {
			$user->login();
            Ajde_Session_Flash::alert(sprintf(__('Welcome back %s'), $user->getFullname()));
			if ($rememberme === true) {
				$user->storeCookie($this->includeDomain);
			}
			$return = array('success' => true);
		} else {
			$return = array(
				'success' => false,
				'message' => __("We could not log you in with these credentials")
			);			
		}		
		return $return;
	}
	
	// Logoff
	public function logoff()
	{
		if (($user = $this->getLoggedInUser())) {
			$user->logout();
		}
		if (($returnto = Ajde::app()->getRequest()->getParam('returnto', false))) {
			$this->redirect($returnto);
		} elseif (substr_count(Ajde_Http_Request::getRefferer(), 'logoff') > 0 || !Ajde_Http_Request::getRefferer()) {
			$this->redirect('user');	
		} else {
			$this->redirect(Ajde_Http_Response::REDIRECT_REFFERER);
		}
	}
	
	public function switchUser()
	{
		if (($user = $this->getLoggedInUser())) {
			$user->logout();
			$this->_user = null;
		}
		return $this->logonHtml();
	}
	
	public function registerHtml()
	{
		$user = new UserModel();
		$this->getView()->assign('returnto', Ajde::app()->getRequest()->getParam('returnto', false));
		$this->getView()->assign('user', $user);
		return $this->render();
	}
	
	public function registerJson()
	{
		$user = new UserModel();
		
		$returnto		= Ajde::app()->getRequest()->getPostParam('returnto', false);
		
		$username		= Ajde::app()->getRequest()->getPostParam($user->usernameField);
		$password		= Ajde::app()->getRequest()->getPostParam('password');
		$passwordCheck	= Ajde::app()->getRequest()->getPostParam('passwordCheck');
		$email			= Ajde::app()->getRequest()->getPostParam('email', false);
		$fullname		= Ajde::app()->getRequest()->getPostParam('fullname', false);
		
		$return = array(false);
		
		$shadowUser = new UserModel();

		if (empty($username) || empty($password)) {
			$return = array(
				'success' => false,
				'message' => __("Please provide ".$user->usernameField." and password")
			);	
		} else if ($shadowUser->loadByField($shadowUser->usernameField, $username)) {
			$return = array(
				'success' => false,
				'message' => __(ucfirst($user->usernameField) . " already exist")
			);	
		} else if ($password !== $passwordCheck) {
			$return = array(
				'success' => false,
				'message' => __("Passwords do not match")
			);
		} else if (empty($email)) {
			$return = array(
				'success' => false,
				'message' => __("Please provide an e-mail address")
			);
		} else if (Ajde_Component_String::validEmail($email) === false) {
			$return = array(			
				'success' => false,
				'message' => __('Please provide a valid e-mail address')
			);
		} else if ($shadowUser->loadByField('email', $email)) {
			$return = array(
				'success' => false,
				'message' => __("A user with this e-mail address already exist")
			);	
		} else if (empty($fullname)) {
			$return = array(
				'success' => false,
				'message' => __("Please provide a full name")
			);			
		} else {
			$user->set('email', $email);
			$user->set('fullname', $fullname);
			if ($user->add($username, $password)) {
				$user->login();
				Ajde_Session_Flash::alert(sprintf(__('Welcome %s, you are now logged in'), $fullname));
				$return = array(
					'success' => true,
					'returnto' => $returnto
				);
			} else {
				$return = array(
					'success' => false,
					'message' => __("Something went wrong")
				);	
			}			
		}
		return $return;
	}
}
