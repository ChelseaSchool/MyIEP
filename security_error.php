<?php

/** @file
 * @brief 	display security error
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @remark
 * #. This page seems to have been abandoned - perhaps for a better method.		
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;    //anybody



/**
 * Path for IPP required files.
 */

define('IPP_PATH','./');
require_once IPP_PATH . 'include/supporting_functions.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/supporting_functions.php';

header('Pragma: no-cache'); //don't cache this page!


?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META charset=UTF-8>
    <TITLE><?php echo $page_title; ?></TITLE>
<?php print_bootstrap_head()?>
</HEAD>
    <BODY>
<?php 
print_general_navbar();
?>
<header><?php if ($system_message) { echo $system_message;} ?></header>
<div class="container">
<div class="row">
 <p>&nbsp;</p>
 <div class="alert alert-block alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a>
      <strong>Insufficient Permissions</strong>: You are trying to access a page that you do not seem to have the permissions to access.     
 </div>                        
</div>                      
</div>                      
<footer><?php print_complete_footer(); ?></footer> 
                  
                 
           
            
      
 
<?php print_bootstrap_js(); ?>   
    </BODY>
</HTML>
