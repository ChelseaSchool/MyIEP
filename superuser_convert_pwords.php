<?php

/** @file
 * @brief 	 Page for converting stored passwords to valid hashed.
 * @copyright 	2014 Chelsea School 
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @todo		
 * 1. Filter input
 * 2. Real escape string
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0;  //assistant administrators


/**
 * Path for IPP required files.
 */

$system_message = $system_message;

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/supporting_functions.php');

header('Pragma: no-cache'); //don't cache this page!

if(isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if(!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
} else {
    if(!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
}
//************* SESSION active past here **************************

//check permission levels
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE****************
$permission_level=getPermissionLevel($_SESSION['egps_username']);
//check permission levels
if($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

/** @fn getUser()
 * @detail
 * This function should query the database table `users` for all users and prepare the results for tabulated report.
 * @return NULL|resource
 */
function getUsers() {
	global $error_message,$iLimit,$iCur,$bShowNav,$system_message;
	if(!connectIPPDB()) {
		$system_message = $system_message . $error_message;  //just to remember we need this
		IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
	}
	$query = "SELECT * FROM `users` ORDER BY login_name ASC";
		

	
	$result = mysql_query($query);
	if(!$result) {
		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
		return NULL;
	}
	return $result;
}

$sqlUsers=getUsers();
if(!$sqlUsers) {
	$system_message = $system_message . $error_message;
	IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="About MyIEP">
    <meta name="author" content="Rik Goldman" >
    <link rel="shortcut icon" href="./assets/ico/favicon.ico">
    <title>Bulk Password Hasher - There is no undo!</title>
	<!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./css/jumbotron.css" rel="stylesheet">

    
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
<script type="text/javascript" src="js/jquery-2.1.0.min.js"></script>
<script>
$(document).ready(function()
		{
		$("#user-list").hide();
		$("#reveal-accounts").click(function() {
				$("#user-list").toggle();
		});
});
</script>


<?php print_bootstrap_head(); ?>
  </head>
  <body>
  <!-- Bootstrap Navbar first -->
      <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="main.php">MyIEP</a>
        </div><!-- end navbar header -->
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="main.php">Home</a></li>
            <li><a href="help.php">Help</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="index.php">Logout</a></li></ul>
             
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Navigation <b class="caret"></b></a>
              <ul class="dropdown-menu">
               
                <li><a href="./manage_student.php">Students</a></li>
                <li class="divider"></li>
                <li><a href="change_ipp_password.php">Reset Password</a></li>
                <!-- <li><a href="superuser_add_goals.php">Goals Database</a></li>-->
                <li><a href="./student_archive.php">Archive</a></li>
                <li><a href="./user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              
              </ul>
            </li>
          </ul>
         </div><!--/.nav-collapse -->
       
      </div>
    </div>
<!-- End Bootstrap Navbar -->
    
<!-- Begin Jumbotron -->
<div class="jumbotron">
      <div class="container">
        <h1>Mass Password Hasher<small>&nbsp; There is no undo!</small></h1> 
          <a class="btn btn-lg btn-primary" href="superuser_tools.php" role="button">Return to Admin Tools &raquo;</a>
         <h2>Logged in as: <small><?php echo $_SESSION['egps_username']; ?> : Restricted</small></h2>
      </div><!-- /container -->
</div> <!-- /jumbotron -->

<div class="container">
<p>This page should self destruct: It should be used <em>once</em> and disposed of.</p>
<p>Pressing <em>Start</em> will query all users in the `user` table for:
<ul>
<li>Username</li>
<li>Plain Text password</li>
</ul>
<h2>Step 1: <small>Reveal Privileged Info You Do Not want to See</small></h2>
<p><button id="reveal-accounts" role="button" class="btn btn-lg btn-default" value="">Toggle All the Things you Can't Un-see (!)</button></p>

<form name="full-user-list" id="user-list" class="form">
<table class="table table-striped table-hover" id="full-user-table">
<tr><th>login_name</th><th>unencrypted_password</th><tr>
<!-- PHP to produce rows of data with loop -->

<?php
	while ($user=mysql_fetch_array($sqlUsers)) {
                            $tablerow = <<<EOF
                            <div id={$user['login_name']}>
                            <tr class="tablerow">
                            	<td>{$user['login_name']}</td>
                                <td>{$user['unencrypted_password']}</td>                            	
                            </tr></div>
EOF;

                            echo $tablerow;
}
?>

</table>

</form>

</div>
<?php print_bootstrap_js();?>  
</body>