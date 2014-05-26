<?php

/** @file
 * @brief 	display currently logged in users
 * @copyright 	2014 Chelsea School 
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 */  
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0;  //only super administrator



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message="";

define('IPP_PATH','./');

/* eGPS required files. */
require_once 'etc/init.php';
require_once 'include/db.php';
require_once 'include/auth.php';
require_once 'include/log.php';
require_once 'include/user_functions.php';
require_once 'include/supporting_functions.php';

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
 if (isset($_POST['delete']) && isset($_POST['session'])) {
 	$session_id=$_POST['session'];
 	$query = "DELETE from logged_in WHERE session_id='$session_id'";
	 if (!mysql_query($query)) {
 		$system_message = "Delete Failed: $query<br>" . mysql_error() . "<br>";
	}
 }
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="About MyIEP">
    <meta name="author" content="Rik Goldman" >
    <link rel="shortcut icon" href="./assets/ico/favicon.ico">
    <TITLE><?php echo $page_title; ?></TITLE>
   <!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./css/jumbotron.css" rel="stylesheet">
	<style type="text/css">body { padding-bottom: 70px; }</style>
    
     

    
	<link rel=stylesheet type=text/css href=./css/jquery-ui-1.10.4.custom.css>

   <script>
function toggle ()
{
	$("div#logged").toggle ("explode", 100)
}
</script>
  

<?php print_bootstrap_head(); ?>    
</HEAD>
    <BODY>
    <?php echo print_general_navbar(); ?>
    <?php print_lesser_jumbotron("View Logged In", $permission_level); ?>;
  <div class="container">      
       
<?php if ($system_message){
	echo "<p>" . $system_message . "</p>";
} 
?>




<?php 

$query = "SELECT * FROM logged_in";
$result = mysql_query($query);
	if (!$result) die ("Database access failed: " . mysql_error());
$rows = mysql_num_rows($result);
?>
<h2>Connected Users: <small><?php echo $rows . " sessions are active.";?></small></h2>
<p><?php echo "<button class=\"btn btn-lg btn-primary\" onclick=\"toggle ()\" role=\"button\">Toggle Details &raquo;</button>"; ?></p>
<div id="logged" hidden="hidden">
<?php 
echo "<form action=\"superuser_view_logged_in.php\" method=\"post\">";
echo "<table class=\"table table-hover table-striped\"><tr><th>uid</th><th>username</th><th>session_id</th><th>Last IP</th><th>Time</th><th>Action</th></tr>";
for ($j = 0; $j < $rows ; ++$j)
{
	$row = mysql_fetch_row($result);
	$session=$row[2];
	print <<<EOF
	
	<tr>
		<td>{$row[0]}</td>
		<td>{$row[1]}</td>
	 	<td><input type="hidden" name="session" value="$session">{$row[2]} <input type="hidden" name="delete" value="True"></td>
	 	<td>{$row[3]}</td>
	 	<td>{$row[4]}</td>
	 	
		<td><button type="submit" value="delete" class="btn btn-danger">Delete Record</button>
		</td>
	</tr>
EOF;
	
}
echo "</table></form>";
?></div>


<?php 	
//get_logged_users(NULL);
?>
	

</div>
        <?php print_complete_footer(); ?>
        
        <?php print_bootstrap_js(); ?>
    </BODY>
</HTML>