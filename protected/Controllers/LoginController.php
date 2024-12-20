<?php

class LoginController extends MainPageController {

	public $allow_access = true;

	
	/*
	 * -------------------------------------------
	 * LOGIN PAGE
	 * -------------------------------------------
	 *
	 */
	public function index() {	
		
		if (auth()->isLoggedIn()) {
			//$this->redirect(array('module' => auth()->getRecord()->module_name));
			$this->redirect(array('module' => auth()->VouchCookie()->default_module));
		}
		// Check session for errors to be displayed
		session()->checkFlashMessages();
				
		// Check for a global company email extension
		$company = $this->loadModel('Company')->getEmailExt();

		if ($company) {
			smarty()->assign('site_email', $company->global_email_ext);
		} else {
			smarty()->assign('site_email', "");
		}
		
		smarty()->assign('title', 'Login');

		 // LOGIN FORM HAS BEEN SUBMITTED
		 //		 
		 		 
		if (input()->is('post')) {
			
			// Verify that the email field is not blank
			if (input()->email != '') {
			
				// Check the email for the '@' symbol
				if (strstr(input()->email, '@')) {
					$username = input()->email;
				} elseif ($company->global_email_ext) {  // Check the db for the global company email extension, if it exists add it here
					$username = input()->email . $company->global_email_ext;
				} else { // if there is no '@' symbol and no global email extension use aptitudecare.com
					$username = input()->email . '@aptitudecare.com';
				}
			} else {
				$error_messages[] = 'Enter your username';
			}

			
			if (input()->password != '') {
				$password = input()->password;
			} else {
				$error_messages[] = 'Enter your password';
			}
			
			
			// If error messages, then set messages and redirect back to login page
			if (!empty($error_messages)) {
				session()->setFlash($error_messages, 'error');
				$this->redirect(input()->path);
			}			
			
			// If the username and password are correctly entered, validate the user
			if (auth()->login($username, $password)) {
				// redirect to users' default home page
				//$this->redirect(array('module' => session()->getSessionRecord('default_module')));
				/*
				//old for when auth was local.
				$user = auth()->getRecord();
				die(var_dump($user));
				if ($user->temp_password) {
					$this->redirect(array('module' => 'Dietary', 'page' => 'users', 'action' => 'reset_password', 'id' => $user->public_id));
				} elseif ($user->module_name == "Admission") {
					$this->redirect(array('module' => 'Admission', 'user' => $user->public_id));
				} else {
					$this->redirect(array('module' => $user->module_name));
				}*/
				$user = auth()->getRecord();
				$vc = auth()->VouchCookie();
				
				//manual and hackish set modules to session, we will use this in admissions to get module switching to make sense.
				if(!isset($_SESSION[APP_NAME]['modules'])) {
					$modules = $this->loadModel('Module')->fetchUserModules(auth()->getPublicId());
					$_SESSION[APP_NAME]['modules'] = array();
					foreach($modules as $k => $v)
					{
						$_SESSION[APP_NAME]['modules'][] = $v->name;
					}
					#die(print_r($modules, true));
				}

				//updated to pull default module from vouch token, goes there just to be redirected back elsewhere.
				if ($user->temp_password) {
					$this->redirect(array('module' => 'Dietary', 'page' => 'users', 'action' => 'reset_password', 'id' => $user->public_id));
				} elseif ($vc->default_module == "Admission") {

					$this->redirect(array('module' => 'Admission', 'user' => $user->public_id));
				} else {
					$this->redirect(array('module' => $vc->default_module));
				}
				
				
			} else { // send them back to the login page with an error
				session()->setFlash(array('Could not authenticate the user'), 'error');
				$this->redirect(input()->path);
			}
					
		} 
				

	}	

	//used for HH
	public function admission_login() {	
		//	Check db for username and public_id
		$user = $this->loadModel('User', input()->id);
		$verified = false;
		$_username = $user->email;
		//	Strip everything after @ from email address
		$string = explode('@', input()->username);
		// Check for a global company email extension
		$emailExt = $this->loadModel('Company')->getEmailExt();	
		
		if (!empty ($emailExt)) {
			$username = array(input()->username, $string[0] . $emailExt->global_email_ext);
			foreach ($username as $uname) {
				if ($uname == $_username) {
					if (auth()->login($user->email, $user->password)) {
						$user = auth()->getRecord();
						if (input()->module != '') {
							$module = input()->module;
						} else {
							$module = "HomeHealth";
						}
						
						$this->redirect(array('module' => $module));

					} else {
						$this->redirect(array('page' => 'login'));
					}
				// if the user is using a different email extension check login
				} else {
					if (auth()->login($user->email, $user->password)) {
						$user = auth()->getRecord();
						$this->redirect(array('module' => input()->module));
					} else {
						$this->redirect(array('page' => 'login'));
					}
				}
			}
		} elseif ($_username = input()->username) {
			$this->redirect();
		} else {
			$this->redirect(array('page' => 'login'));
		}
		exit;
	}

	public function admission_logout() {
		auth()->logout();
		//$this->redirect(array('page' => 'login', 'action' => 'index'));
		$this->redirect();
	}
	
	public function logout() {
		auth()->logout();
		//$this->redirect(array('page' => 'login', 'action' => 'index'));
		$this->redirect();
	}
	

	public function timeout() {
		smarty()->assign('title', "Session Timeout");
		auth()->logout();
		
	}

	public function keepalive() {
		if (isset ($_SESSION['id'])) {
			$_SESSION['id'] = $_SESSION['id'];
		}	
	}
	
}