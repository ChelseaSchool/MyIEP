<?php
/** @file
 *  @brief configurations file
 *  
 *  1. Has site settings in one place
 *  2. stores URL and URIs as constants
 *  3. Sets how errors will be handled
 *  
 */

//Errors are emailed here
$contact_error_email = 'rgoldman@chelseaschool.edu';

//Determine local or live
$host = substr($_SERVER['HTTP_HOST'], 0, 5);

if (in_array($host, array('local', '127.0', '129.1')))
{
		$local= TRUE;
}
else 
{
	$local=FALSE;
}	

if ($local)
{
	$debug = TRUE;
	
	define('BASE_URI', '/var/www/MyIEP');
	define('BASE_URL', 'http://localhost/MyIEP/');
	define('DB', '/var/www/MyIEP/etc/db.php');

} else {
	define('BASE_URI','/MyIEP/');
	define('BASE_URL', 'https://teamchelsea.net/MyIEP/');
	define('DB','/MyIEP/etc/db.php');
}
/* The most important setting
 * The debug variable is used to set error management
 * to debug a specific page, add this to the index page etc
 */

/*
 * if ($p == 'thismodule') $debug=TRUE;
require('./includes/config.inc.php');

*To debug the entire site do
$debug=TRUE

*before this next conditional
*/

if (!isset($debug))
{
	$debug=FALSE;
	
}

function my_error_handler($e_number,$e_message, $e_file,$e_line, $e_vars) {
	global $debug, $email;
	$my_message = "An error occured in script '$e_file' on line $e_line: '$e_message";
	$my_message.=print_r($e_vars, 1);
	
	if ($debug)
	{
		if (isset($evars)) {
		echo '<div class="container"><div class="error alert alert-warning">' . $my_message . '</div></div>';
		}
		else NULL;
	}
	else {
		error_log($message,1,$contact_error_email);
	}
	
}
set_error_handler($my_error_handler);



