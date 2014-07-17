<?php 

/**
 *	Index
 *
 *	This is the main page for handling all requests.  The apache config file should be set for the URL to go straight to this page.
 *
 * 	PHP 5
 *
 *	This framework was developed by Aptitude specifically for the AptitudeCare suite of health care software solutions.
 *	
 *	@copyright  	Copyright 2014, Aptitude IT, LLC
 * 	@version  		AptitudeFramework version 1.0
 */
 
	if (!defined('DS')) {
		define('DS', DIRECTORY_SEPARATOR);
	}

/**
 *	If the apache config file is set correctly the root directory will not yet be defined.  Define it here.
 */

	if (!defined('ROOT')) {
		define('ROOT', dirname(dirname(dirname(dirname(__FILE__)))));
	}
	
	if (!defined('SITE_DIR')) {
		define('SITE_DIR', dirname(dirname(__FILE__)));
	}
	
/**
 *
 *	Define the path to the protected and public directories
 */

 	define('FRAMEWORK_DIR', ROOT . DS . 'framework');
	define('PUBLIC_DIR', SITE_DIR . DS . 'public');
	define('PROTECTED_DIR', SITE_DIR . DS . 'protected');


	// Use https, otherwise the site stylesheets and images will not load properly
	define('SITE_URL', 'https://' . $_SERVER['SERVER_NAME']);
	
/** 
 *
 * Include the bootstrap file in the protected directory and we're off!
 */

	require(FRAMEWORK_DIR . DS . 'bootstrap.php');

