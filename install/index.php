<?php
/** @file
 * 
 *  @brief 		install wizard
 *  
 *  @todo
 *  * Rebrand, theme
 *  * add check for magic quotes
 *  * Return PHP version, perhaps check for compatibility
 *
 *  @author    M Nielson
 *  
 *  @copyright 2007 Grassland Regional #6
 *  
 *  @copyright 2014 Chelsea School
 *  
 *  @license   GPv2 <http://www.gnu.org/licenses/gpl-2.0.html>
 */
// check if we have an init.php file already...security problem
/**
 * diabled for sanity check
 *
 * if (is_file("../etc/init.php")) {
 * die("To run the install, " . realpath("../etc/init.php") . " must not already exist!");
 * }
 */

// the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;

require_once '../include/supporting_functions.php';

/*
 * Don't Cache this Page
 */

header ( 'Pragma: no-cache' );

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Installation Index">
<meta name="author" content="Rik Goldman">
<TITLE>MyIEP Installation</TITLE>
<!-- Bootstrap core CSS -->
<link href="../css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="../css/jumbotron.css" rel="stylesheet">
<style type="text/css">
body {
	padding-bottom: 70px;
}
</style>
</HEAD>
<BODY>
	<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse"
					data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span> <span
						class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">MyIEP Install Wizard</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="index.php" title="Depency Check">Check
							Dependencies</a></li>
					<li><a onclick="history.go(-1);" title="Back a Page"><span
							class="glyphicon glyphicon-circle-arrow-left"></span></a></li>
					<li><a href="sprint_feedback.php" title="Leave User Feedback"><span
							class="glyphicon glyphicon-envelope"></span></a></li>
					<li><a href="index.php" title="Logout of MyIEP"><span
							class="glyphicon glyphicon-off"></span></a></li>
					<li><a href="about.php" title="About MyIEP"><span
							class="glyphicon glyphicon-info-sign"></span></a></li>
					<li><a href="help.php" title="Some Help Here"><span
							class="glyphicon glyphicon-question-sign"></span></a></li>
				</ul>
			</div>
			<!--/.navbar-collapse -->
		</div>
	</div>

	<div class="jumbotron">
		<div class="container">
			<h1>MyIEP</h1>
			<h2>
				Installation: <small>Check Dependencies</small>
			</h2>
			<p>MyIEP is a free and open, web-based IEP management system.</p>
			<p>
				&copy; 2014 Chelsea School - <a
					href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a>.
			</p>

		</div>
	</div>
	<div class="container">

		<ul>

			<?php $fail=FALSE; ?>
			<?php
if (! extension_loaded ( "mysql" )) {
    echo "<li><a href=\"http://packages.ubuntu.com/trusty/php5-mysql\" target=\"_target\">Mysql Extensions</a> are not loaded (FAIL)";
    $fail = TRUE;
} else {
    echo "<li>Mysql Extension is loaded (PASS)";
}
if (! extension_loaded ( "gd" )) {
    echo "<li><a href=\"http://en.wikipedia.org/wiki/GD_Graphics_Library\" target=\"_blank\">GD Libraries</a> are not loaded (FAIL)";
    $fail = TRUE;
} else {
    echo "<li>GD Libraries are loaded (PASS)";
}
if (! extension_loaded ( "iconv" )) {
    echo "<li><a href=\"http://www.php.net/manual/en/iconv.installation.php\" target=\"_blank\">iconv Libraries</a> are not loaded (FAIL)";
    $fail = TRUE;
} else {
    echo "<li>iconv Libraries are loaded (PASS)";
}

if (! @include_once 'Mail.php') {
    echo "<li><a href=\"http://pear.php.net/package/Mail/redirected\" target=\"_blank\">Pear Mail Class</a> is not loaded (Not Recommended)";
} else {
    echo "<li>Pear Mail Class is loaded (Recommended)";
}
if (! @include_once 'Mail/mime.php') {
    echo "<li><a href=\"http://pear.php.net/package/Mail_Mime/redirected\" target=\"_blank\">Pear Mail/Mime</a> Class is not loaded (Not Recommended)";
} else {
    echo "<li>Pear Mail/Mime Class is loaded (Recommended)";
}
if (! @include_once 'Net/SMTP.php') {
    echo "<li>Pear Net/SMTP Class is not loaded (Not Recommended)";
} else {
    echo "<li>Pear Net/SMTP Class is loaded (Recommended)";
}
if (get_magic_quotes_gpc ()) {
    echo "<li>gpc_magic_quotes is enabled (FAIL)</li>";
    $fail = TRUE;
} else {
    echo "<li>Magic quotes are disabled (PASS)</li>";
}
?>
</ul>
<form enctype="multipart/form-data" action="../install/permissions.php" method="post">

<?php
    if ($fail) {
        echo "<button class=\"btn pull-right btn-danger\" type=\"submit\"";
    } else {
        echo "<button class=\"btn pull-right btn-success\" type=\"submit\" value=\"Next\"";
    }
    if ($fail) {
        echo " disabled>Missing Dependencies</button>";
    } else {
        echo ">Continue</button>";
    }
    ?></button>
		</form>



	</div>

	<script src="js/jquery-2.1.1.js" type="text/javascript"></script>
	<script src="js/bootstrap.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>
</BODY>
</HTML>