<?php
/** @file
 *  @brief Receipt for password reset
 *  @author rik goldman <rgoldman@chelseaschool.edu>
 *  @copyright 2014 Chelsea School
 *  @license GPLv2
 *  
 */ 
require_once 'include/supporting_functions.php';
//remove_credential_reset_lock(); //security override - delete for production

//$_SESSION = array();

if (session_name("Credential Reset")) {
	die("Please do not make repeated attempts to generate a new password. Click <a href=\"index.php\" here</a> to return to the main page.");
}
session_start();
session_id();
$sess_name = "Credential Reset";
session_name($sess_name);
//ini_set('session.gc.maxlifetime', 60*6*24);
//$_SESSION['string'] = implode(": " , $_POST[]);

header('Pragma: no-cache'); //don't cache this page!





$webapp = "https://teamchelsea.net/MyIEP/";

require_once 'etc/init.php';
//require_once 'include/db.php';
//require_once 'include/log.php';
//require_once(IPP_PATH . 'include/auth.php');

require_once 'include/supporting_functions.php';
//require_once 'include/page_troubleshoot.php';


if (!isset($_POST)) {
	die();
}

if (isset($_POST)) {
	$user = $_POST['uname'];
	$email = $_POST['email1'];
	$submission = $_POST['date'];
	$client_address = $_POST['client_address'];
	$browser = $_POST['user_agent'];
}





$connection=mysql_connect("localhost", $mysql_data_username, $mysql_data_password);
mysql_select_db($mysql_data_database);
$query = "SELECT * FROM support_member WHERE egps_username = '$user' AND email = '$email'";
$result = mysql_query($query);
if (!$result) die("No result");
$rows = mysql_num_rows($result);
if ($rows != 1) {
	die("Error: Could not match the information provided to a specific registered user. Please contact your system administrator.");
}

$match = mysql_fetch_array($result);
$egps_username = $match['egps_username'];

if ($match['egps_username'] != $user) {
	die("Error: Could not match the information provided to a registered user. Please contact a system administrator.");
	
}

$replacement = generate_password
($length=10,$characters='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()-_{}[]|:<>');

if (!isset ($replacement)) {
	die ("Error: Attempt to generate a random password failed. Please contact your system administrator.");
}

$sql = "UPDATE users SET unencrypted_password = '$replacement' WHERE users.login_name = '$user'";
$result = mysql_query($sql);
if (!$result) {
	die("Error: could not change password for the specified user. Please contact a system administrator.");
}








?>
<!DOCTYPE HTML>

<head>
<title>Receipt</title>
<?php print_bootstrap_head(); ?>
</head>
<body>
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
          <a class="navbar-brand" href="index.php">MyIEP</a>
          </div>
        <div class="navbar-collapse collapse">
          <!-- <form class="navbar-form navbar-right" role="form" action="<?php echo IPP_PATH . 'main.php'; ?>" method="post">
            <div class="form-group">
              <input name="LOGIN_NAME" autofocus required autocomplete="off" type="text" placeholder="User Name" name="LOGIN_NAME" value="<?php echo $LOGIN_NAME;?>" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input name=PASSWORD required autocomplete="off" type="password" placeholder="Password" class="form-control" name="PASSWORD" value="" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-success">Sign in</button>
            
          </form>-->
        </div><!--/.navbar-collapse -->
      </div>
    </div>    
    
       
        
 <!-- End Navbar -->

<div class="jumbotron">
<h1>Reciept: Password Reset Request</h1>
<h2>For User: <?php echo $user; ?>&nbsp;<small>at <?php echo $email; ?></small></h2>       
</div><!-- End Jumbotron -->




<div class="container">



<?php 
/* For troubleshooting
if (isset($_POST)) {
	$user = $_POST['uname'];
	$email = $_POST['email1'];
	$submission = $_POST['date'];
	$client_address = $_POST['client_address'];
	$browser = $_POST['user_agent'];
	echo "<p>{$user}\n";
	echo "<p>{$email}\n";
	echo "<p>New Password: {$replacement}\n";
	echo "<p>{$submission}\n";
	echo "<p>{$client_address}\n";
	echo "<p>{$browser}\n";
	}
*/
?>

<?php

function MyIEP_Log($szMsg='', $username="-UNKNOWN-", $level='ERROR',$student_id='') {
	//Error Handler
	switch($level) {
		case 'WARNING' :
		case 'INFORMATIONAL':
		case 'ERROR':
//$connection=mysql_connect("localhost", $mysql_data_username, $mysql_data_password);
//mysql_select_db($mysql_data_database);
			
			$connection=mysql_connect("localhost", $mysql_data_username, $mysql_data_password);
				if (!isset($connection)) echo "Connection Failure \n";
			mysql_select_db($mysql_data_database);
			$log_query = "INSERT INTO error_log (level,username,time,message,student_id) VALUES ('$level','$username',now(),'" . mysql_real_escape_string($szMsg) . "',";
			if($student_id=="") $log_query=$log_query . "NULL";
			$log_query = $log_query . ")";
			$log_result = mysql_query($log_query);
			if(!$log_result) {
				echo "log error: " . mysql_error() . "<BR>Query= " . $log_query . "<BR>";
			}
			else 
				{ 
					//echo "Request query successful.";
				}
			break;
	}
	return TRUE;
}

$log_message=<<<EOF
A new password was requested by $user/$email from $client_address and with $browser on $submission.
A new password was generated and set in the database.
EOF;

$log=MyIEP_Log($szMessage=$log_message,$username=$user,$level="INFORMATIONAL");

if (isset ($log)) {
	echo "<div class=\"alert alert-block alert-info\" id=\"logged\" >
<strong>Notification</strong>: A password reset request has been successfully submitted to system administrators.</div>";
}
else {
	echo "<div class=\"alert alert-block alert-danger\"><strong>Error</strong>: A password reset request has not been successfully submitted to system administrators.</div>";
	die();
}
if (isset($log)) {
$user_message=<<<EOF
<pre>Thank you for your request for a new password for $user/$email. A new password has been generated.
If an email can be sent from the system, this message will be sent to $email with your new password. 

The password will appear in the next line.

#Here's an Example#

Your new, temporary password: #123abc^ABC#

Next Steps:
1. Login with $user and your new password at $webapp.
2. On the main page after logging in, select "Change Password" to customize your password.
3. Logout and log back in with your self-determined password.

If you don't receive an email notification with your new password, please contact helpdesk at http://chelseaschool.freshdesk.com.

Thank You.</pre>
EOF;
}
?>
<?php 
$email_message = str_replace("#Here's an Example#", "", $user_message);
$email_message = str_replace("#123abc^ABC#", $replacement, $email_message);
?>
<?php
$send_message = mail($email, "MyIEP Password Reset", $email_message);

if ($send_message) {
	echo "<div class=\"alert alert-info\" id=\"email-queud\"><strong>Notification</strong>: This system has attempted to email your credentials to {$email}.</div>";
}
else {
	echo "<div class=\"alert alert-danger\" id=\"email-fail\"><strong>Error</strong>: This system could not email your credentials to {$email}. Please contact your system administrator.</div>";
}
/*
 * Must have PEAR installed and enabled
   $send_message = mail_notification($recipients = $email, "rgoldman@chelseaschool.edu", $message=$email_message);
	if ($send_message){
		echo "<div class=\"alert alert-info\"><strong>Notification</strong>: For good measure, this system has used an alternative method to send a new password to {$email}.</div>";
		}
	else {
		echo "<div class=\"alert alert-danger\"><strong>Error: </strong>" . $system_message . "</div>";
		}
*/
	?>

<h2>What to Expect</h2>
<?php echo $user_message; ?>


</div>
<?php print_bootstrap_js(); ?>
</body>
</html>