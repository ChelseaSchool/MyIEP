<?php
/** @file
 * @brief 	login page
 *
 * Login form and about MyIEP summary.
 * @copyright 	2014 Chelsea School
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * 1. Double check security
 * 2. Use filters for input
 * 3. test sql injections
 * 4. add alert for failed login attempt
 */

//ini_set('display_errors',1);
//error_reporting(E_ALL);

if(!defined('IPP_PATH')) define('IPP_PATH','./');
//check if we are running install wizard
if(!is_file(IPP_PATH . "etc/init.php"))
{
    include_once IPP_PATH . 'install/index.php';
    exit();
}
/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/supporting_functions.php';
require_once IPP_PATH . 'include/config.inc.php';
logout();
header('Pragma: no-cache'); //don't cache this page!

if(isset($MESSAGE)) $MESSAGE = $MESSAGE; else $MESSAGE="";
if(isset($LOGIN_NAME)) $LOGIN_NAME = $LOGIN_NAME; else $LOGIN_NAME="";

?> 
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Rik Goldman" >
    <link rel="shortcut icon" href="./assets/ico/favicon.ico">
    <TITLE><?php echo $page_title; ?></TITLE>
    
    
        <!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->

    <link href="./css/jumbotron.css" rel="stylesheet">

    
    

</HEAD>
<BODY>
<!-- Jumbo Stock Nav --> 
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">MyIEP</a>
        </div>
        <div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form" action="test.php" method="post">
            <div class="form-group">
              <input name="LOGIN_NAME" autofocus required autocomplete="off" type="text" placeholder="User Name" name="LOGIN_NAME" value="<?php echo $LOGIN_NAME;?>" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input required autocomplete="off" type="password" placeholder="Password" class="form-control" name="PASSWORD" value="" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-success">Sign in</button>
            
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </div>    
    
       
        
 <!-- End Navbar -->
 
<div class="jumbotron">
<div class="container">


 <h1>About MyIEP</h1>
        <p>MyIEP (Version <?php echo $IPP_CURRENT_VERSION; ?>) was originally developed as IEP-IPP through the coordinated efforts of many people at Grasslands Public Schools.</p>
        <p>MyIEP is under development by students, faculty, and administrators at <a href="http://chelseaschool.edu">Chelsea School</a> in Hyattsville, MD.</p>
 <h1>Stuck?</h1>
        <p><a class="btn btn-primary btn-lg" href="new_credentials.php" role="button">Reset Password &raquo;</a></p>
</div><!-- end container -->
</div><!-- End Jumbotron -->
<div class="container">

<!-- Row 1 -->
<div class="row">
<!-- Left column -->
<div class="col-md-4">
<h1>What's New</h1>
<h3>Bug Fixes</h3>
<ul>
<li>Backslashes accumulated in progress reports</li>
<li>Strengths &amp; Weaknesses narratives truncated</li>
</ul>
<h3>Features</h3>
<ul>
<li>jQuery Date Picker</li>
<li>User Interface/User Experience Improvements</li>
	<ul>
	<li>Main Menu</li>
	<li>Goal View</li>
	<li>Edit Objectives</li>
	</ul>
</ul>
</div>
<!-- Middle column -->
<div class="col-md-4">
<h1>Credits</h1>
<h3>MyIEP</h3>
<p>Rik Goldman, Sabre Goldman, Jason Banks, Alex, Tristan, Micah, Paul, Kenny, Stephen, Jonathan, James, Bryan</p>
<h3>Legacy Code</h3>
<p>M. Nielson</p>
<p><a class="btn btn-default" href="https://github.com/ChelseaSchool/MyIEP" role="button">MyIEP Source Code &raquo;</a></p>
</div>
<!-- Right column -->
<div class="col-md-4">
<h1>Copyright</h1>
<h3>MyIEP</h3>
<p>&copy; 2014 Chelsea School</p>

<address><strong>Chelsea School</strong><br>
2970 Belcrest Center Drive - Suite 300<br>
Hyattsville, Maryland 20782</address>
<h3>Legacy Code</h3>
<p>&copy; 2004 Grasslands Regional Public Schools #6</p>
<p><a class="btn btn-default" href="http://chelseaschool.edu" role="button">Chelsea School &raquo;</a></p>
</div>
</div>
<!-- End Row -->
<!-- New Row -->
<div class="row">
<!--  Left Column -->
<div class="col-md-4">
<h1>License</h1>
<p>This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

<p>This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

<p>You should have received a copy of the GNU General Public License along with this program; if not, write to:</p>
 <address><strong>The Free Software Foundation, Inc.</strong><br>
 59 Temple Place, Suite 330, Boston, <abbr title="Massachussetes">MA</abbr>  02111-1307<br>
 USA</address>
 <p><a class="btn btn-default" href="http://www.gnu.org/licenses/gpl-2.0.html" role="button">GPLv2 &raquo;</a></p>
</div>
<!-- Middle Column, Second Row -->
<div class="col-md-4">
<h1>To Do</h1>
<ul>
<li>Refactor Code (create functions)</li>
<li>Integrate authentication into main.php</li>
<li>Complete bootstrap UI across all pages</li>
<li>Standardize variable and function names</li>
<li>Continute to Filter and Escape</li>
<li>Doxygen style commenting</li>
<li>HTML5 Standardize and Validate</li>
<li>HTML5 Form Enhancement &amp; Validate</li>
<li>Admin Documentation</li>
<li>Flexible Branding of PDF Report</li>
<li>Admin Branding Page</li>
</ul>
</div>
<!-- Second Row, Right Column -->
<div class="col-md-4">
<h1>Development Process</h1>
<p>Developed by students in Information Systems Management and Web Design &amp; Development courses at Chelsea School.
<ul>
<li>Developed using Scrum Framework (an Agile methodology)</li>
<li>HTML5 &amp; CSS3</li>
<li>Vagrant & Virtualbox Development Environment</li>
<li>Source Code Management (SCM) with Git and Github</li>
<li>Apache web-server administration</li>
<li>Data processing with PHP</li>
<li>Data storage with MySQL</li>
<li>Active web pages with jQuery and JavaScript</li>
<li>Responsive design (in progress) with Bootstrap</li>
</ul>
</div>
</div>
</div>
<hr>
<?php print_complete_footer(); ?>  
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-2.1.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>     
</BODY>
</HTML>

