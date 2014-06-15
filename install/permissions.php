<?php

/** @file
 * @brief 		This part of install wizard checks status and makes sure everything is good to proceed
 * @todo
 * 1. Rebrand and theme
 * 2. Copyright Block
 */

// check if we have an init.php file already...security problem
if (is_file("../etc/init.php")) {
    define('IPP_PATH', '../');
    require_once ("../etc/init.php");
}

// the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;

/**
 * Path for required files.
 */

error_reporting(1);

/* eGPS required files. */
// require_once(IPP_PATH . 'etc/init.php');
// require_once(IPP_PATH . 'include/db.php');
// require_once(IPP_PATH . 'include/auth.php');
// if ((int)phpversion() < 5) { require_once(IPP_PATH . 'include/fileutils.php'); } //only for pre v5
// require_once(IPP_PATH . 'include/log.php');
// require_once(IPP_PATH . 'include/navbar.php');

header('Pragma: no-cache'); // don't cache this page!

?>
<!DOCTYPE HTML>
<HTML lang="en">
<HEAD>
<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
<TITLE>MyIEP Installation: Install Permissions</TITLE>
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
					<li class="active"><a href="permissions.php"
						title="Permission Check">Permission Check</a></li>
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
				Installation: <small>Install Permissions</small>
			</h2>
			<p>MyIEP is a free and open, web-based IEP management system.</p>
			<p>
				&copy; 2014 Chelsea School - <a
					href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a>.
			</p>
							<!-- Progress Bar -->
			<div class="progress progress-striped">
				<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="33"
					aria-valuemin="0" aria-valuemax="100" style="width: 33%;"><span class="sr-only">33% Complete</span></div>
			</div>
			</div>
		</div>
	</div>
	<div class="container">
		<?php

if (isset($IPP_IS_CONFIGURED) && $IPP_IS_CONFIGURED) {
    die("The configuration file:" . realpath("../etc/init.php") . " already exists and the IPP_IS_CONFIGURED flag is set. For security reasons you cannot rerun this page. If you want to rerun the install please manually delete the config file.");
}
?>







		<table width="80%" border="0">
			<?php
$option = "Alternatively, you may copy the init.dist.php file to init.php and edit manually.<BR><BR>";
$fail = FALSE;
$file = file("../etc/init.dist.php");
if (! $file) {
    $fail = TRUE;
    echo "Cannot open init.dist.php configuration file for read (" . realpath("../etc/init.dist.php") . "). Set the file permissions and reload this page<BR>$option";
} else {
    $handle = fopen("../etc/init.php", "wc");
    if (! $handle) {
        $fail = TRUE;
        echo "Cannot open init.php configuration file for writing (" . realpath("../etc/init.php") . "). You might need to create this file, set the permissions, and reload this page<BR><BR>('touch init.php' and 'chmod 777 init.php' in a shell from the " . realpath("../etc") . " directory)<BR><BR>$option";
    } else {
        foreach ($file as $line) {
            fwrite($handle, $line);
        }
        fclose($handle);
    }
}

if (! $fail) {
    echo realpath("../etc/init.php") . " is writable. Copied default install. Click next.";
}
// $fail=FALSE;
// if(is_writable("../etc/init.php")) {
// echo "Configuration file (../etc/init.php) is not writeable (FAIL)";
// $fail=TRUE;
// } else {
// echo "Configuration file is writeable (PASS)";
// }

?>

<?php
echo "<form enctype=\"multipart/form-data\" action=\"./database.php" . "\" method=\"post\">";
echo " <button class=\"btn btn-success pull-right\" type=\"submit\" value=\"Next\"";
if ($fail)
    echo " disabled></button>";
echo ">Next</button>";
echo "</form>";

?>


			</div>
			<script src="js/jquery-2.1.1.js" type="text/javascript"></script>
			<script src="js/bootstrap.min.js" type="text/javascript"></script>
			<script type="text/javascript"
				src="js/jquery-ui-1.10.4.custom.min.js"></script>

</BODY>
</HTML>