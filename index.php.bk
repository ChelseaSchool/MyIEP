<?php
/*
 * This is the splash page: apache usually configured to serve index.? by default
 * 1. Checks to see if this is first run (install procedure then); if not
 * 2. starts launch.php in new browser window without navigation; let's refactor this in later sprint. Not a contemporary workflow and throws our product owners off
 * 3. code on 28 is commented out. We need to understand the rational. Looks like artifact from dev process
 * 4. update copyright to match recommended of license
 */
 
/**
 * index.php -- Displays the main frameset
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 *
 *
 * Redirects to the login page.
 *
 *
 */
define('IPP_PATH','./');

//check if we are running install wizard
if(!is_file(IPP_PATH . "etc/init.php")){
	require_once(IPP_PATH . 'install/index.php');
	exit();
}

require_once(IPP_PATH . "etc/init.php");

require_once(IPP_PATH . 'include/auth.php');

//force logout...
logout();
//header('Location: src/login.php');
header('Location: src/launch.php');
?>
<html></html>
