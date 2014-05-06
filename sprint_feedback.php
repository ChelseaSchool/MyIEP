<?php
/** @file 
 *  @brief  Provide user feedback opportunity during development
 *  
 */

//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;

if(isset($system_message)) $system_message = $system_message;
else $system_message = "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once 'include/mail_functions.php';
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
if ((int)phpversion() < 5) { require_once(IPP_PATH . 'include/fileutils.php'); } //only for pre v5
require_once(IPP_PATH . 'include/log.php');
//require_once(IPP_PATH . 'include/navbar.php');
require_once(IPP_PATH . 'include/supporting_functions.php');
//require_once(IPP_PATH . 'include/config.inc.php');
header('Pragma: no-cache'); //don't cache this page!


if(isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if(!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        if(isset($_SESSION['egps_username'])) IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        else IPP_LOG($system_message,'no session','ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
} else {
    if(!validate()) {
        $system_message = $system_message . $error_message;
        if(isset($_SESSION['egps_username'])) IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        else IPP_LOG($system_message,"no session",'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
}

//$user = mysql_real_escape_string($_POST["LOGIN_NAME"]);
$session_name = $_SESSION["egps_username"];
//$session_login = $_SESSION['LOGIN_NAME'];
//$referrer = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
if (isset ($_POST['contents'])) {
	$feedback = implode("; ", $_POST);
	mail_notification("rgoldman@chelseaschool.edu", $feedback);
	mail("rgoldman@chelseaschool.edu", "MyIEP Sprint Feedback", $feedback);
	IPP_Log($feedback, $_SESSION['egps_username'], $level='INFORMATIONAL');
	header('Location: main.php');
}

?>





<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="Rik Goldman">
<link rel="shortcut icon" href="./assets/ico/favicon.ico">

<title>User Experience Feedback Form</title>

<!-- Bootstrap core CSS -->
<link href="css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="css/jumbotron.css" rel="stylesheet">

</head>

<body>

	<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse"
					data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span> <span
						class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="main.php">MyIEP</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li><a href="main.php">Home</a></li>
					<li><a href="about.php">About</a></li>
            		<li class="active"><a href="sprint_feedback.php">Leave Feedback</a></li>
					<li><a href="help.php">Help</a></li>
					<li><a href="index.php">Logout</a></li>
				</ul>

				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown"><a href="#" class="dropdown-toggle"
						data-toggle="dropdown">Navigation <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="./manage_student.php">Students</a></li>
							<li class="divider"></li>
							<li><a href="change_ipp_password.php">Reset Password</a></li>
							<li><a href="superuser_add_goals.php">Goals Database</a></li>
							<li><a href="student_archive.php">Archive</a></li>
							<li><a href="user_audit.php">Audit</a></li>
							<li><a href="superuser_manage_coding.php">Manage Codes</a></li>
							<li><a href="school_info.php">Manage Schools</a></li>
							<li><a href="superuser_view_logs.php">View Logs</a></li>
						</ul></li>
				</ul>
			</div>
			<!--/.nav-collapse -->
			<!--<div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form" nctype="multipart/form-data" action="jumbotron.php" method="post">
            <div class="form-group">
              <input type="text" placeholder="User Name" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control" name="PASSWORD" value="">
            </div>
            <button type="submit" value="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
		</div>
	</div>

	<!-- Main jumbotron for a primary marketing message or call to action -->
	<div class="jumbotron">
		<div class="container">
			<h1>
				User Experience <small>Feedback Form</small>
			</h1>
			
			<p>
				<a class="btn btn-primary btn-lg" onClick="history.go(-1);" role="button">Go Back &raquo;</a>
			</p>
		</div>
	</div>


<div class="container">
<form enctype="multipart/form-data" action="sprint_feedback.php" method="post">
<input hidden type="text" name="session_name" value="<?php echo $session_name;?>">
<input hidden type="URL" name="referrer" value="http://<?php echo $referrer; ?>">
<div class="form-group">
<label>Subject</label>
<input required class="form-control" type="text" spellcheck="true" name="RE" placeholder="This is about...">
<label>Message</label>
<textarea required class="form-control" rows="10" name="contents" spellcheck="true"></textarea>
</div>
<button class="btn btn-large btn-standard" type="submit">Submit</button>
</form>
</div> <!-- /container -->


	<?php echo print_footer(); ?>


	<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="js/jquery-2.1.0.min.js" type="text/javascript"></script>
	<script src="./js/bootstrap.min.js" type="text/javascript"></script>
</body>
</html>
