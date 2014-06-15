<?php

/** @file
 * @brief 	display security error
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
