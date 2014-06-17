<?php

/** @file
 *  @brief		configuration settings for install wizard
 *  @todo
 *  1. Bootstrap
 *  2. Copyright block
 */
if (is_file("../etc/init.php")) {
    define('IPP_PATH', '../');
    require_once ("../etc/init.php");
    if (isset($IPP_IS_CONFIGURED) && $IPP_IS_CONFIGURED) {
        die("The configuration file:" . realpath("../etc/init.php") . " already exists and the IPP_IS_CONFIGURED flag is set. For security reasons you cannot rerun this page. If you want to rerun the install please manually delete the config file.");
    }
}

// the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;

/**
 * Path for required files.
 */
error_reporting(1);

$system_message = "";

/* eGPS required files. */
// require_once(IPP_PATH . 'etc/init.php');
// require_once(IPP_PATH . 'include/db.php');
// require_once(IPP_PATH . 'include/auth.php');
// if ((int)phpversion() < 5) { require_once(IPP_PATH . 'include/fileutils.php'); } //only for pre v5
// require_once(IPP_PATH . 'include/log.php');
// require_once(IPP_PATH . 'include/navbar.php');
require_once IPP_PATH . 'include/supporting_functions.php';
header('Pragma: no-cache'); // don't cache this page!

if (isset($_POST['update'])) {
    // we need to update the config file
    $file = file("../etc/init.php");
    if (! $file)
        $system_message .= "Cannot open init.php configuration file. You will need to manually edit this file<BR>";
    else {
        $updated_file = "";
        foreach ($file as $line) {
            // echo "Line: " . $line . "<BR>";
            if (preg_match('/page_title/', $line)) {
                $line = "\$page_title = \"" . $_POST['config_title'] . "\";\n";
            }
            if (preg_match('/IPP_PAGE_ROOT/', $line)) {
                $line = "\$IPP_PAGE_ROOT = \"" . $_POST['config_url'] . "\";\n";
            }
            if (preg_match('/IPP_ORGANIZATION /', $line)) {
                $line = "\$IPP_ORGANIZATION = \"" . $_POST['config_organization'] . "\";\n";
            }
            if (preg_match('/IPP_ORGANIZATION_ADDRESS1/', $line)) {
                $line = "\$IPP_ORGANIZATION_ADDRESS1 = \"" . $_POST['config_address1'] . "\";\n";
            }
            if (preg_match('/IPP_ORGANIZATION_ADDRESS2/', $line)) {
                $line = "\$IPP_ORGANIZATION_ADDRESS2 = \"" . $_POST['config_address2'] . "\";\n";
            }
            if (preg_match('/IPP_ORGANIZATION_ADDRESS3/', $line)) {
                $line = "\$IPP_ORGANIZATION_ADDRESS3 = \"" . $_POST['config_address3'] . "\";\n";
            }
            
            if (preg_match('/mail_host/', $line)) {
                $line = "\$mail_host= \"" . $_POST['config_email'] . "\";\n";
            }
            $updated_file .= $line;
            // echo $line . "<BR>";
        }
        $handle = fopen("../etc/init.php", "w");
        if (! $handle) {
            $fail = TRUE;
            echo "Cannot open init.php configuration file for writing (" . realpath("../etc/init.php") . "). You will need to manually edit this file<BR>";
        } else {
            fwrite($handle, $updated_file);
            fclose($handle);
            header("Location: cleanup.php");
        }
    }
}
?>
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
<TITLE>MyIEP Installation: Configuration</TITLE>
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
					<li class="active"><a href="config.php" title="Configure Variables">Configure</a></li>
					
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
				Installation: <small>Configure Variables</small>
			</h2>
			<p>MyIEP is a free and open, web-based IEP management system.</p>
			<p>
				&copy; 2014 Chelsea School - <a
					href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a>.
			</p>
			 <?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>
			
			<!-- Progress Bar -->
			<div class="progress progress-striped">
				<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="90"
					aria-valuemin="0" aria-valuemax="100" style="width: 90%;"><span class="sr-only">90% Complete</span></div>
			</div>


		</div>
	</div>
	<div class="container">

		<p>These values can be adjusted later by editing the etc/init.php
			file.</p>
		<form enctype="multipart/form-data" action="./config.php"
			method="post">
			<div class="form-group">
				<input type="hidden" name="update" value="1"> <label>Application
					Title</label><input class="form-control" required type="text"
					spellcheck="true" size="50" name="config_title"
					value="<?php if(isset($_POST['config_title'])) echo $_POST['config_title']; else echo "MyIEP Special Education Program Management"; ?>">

				<label>URL</label> <input class="form-control" type="text" size="50"
					name="config_url"
					value="<?php if(isset($_POST['config_url'])) echo $_POST['config_url']; else {if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on") echo "https://"; else echo "http://"; echo $_SERVER['SERVER_NAME'] . str_replace('/install/config.php','',$_SERVER['REQUEST_URI']);} ?>">
				</td> <label>Organization</label> <input required
					class="form-control" type="text" size="50"
					name="config_organization"
					value="<?php if(isset($_POST['config_organization'])) echo $_POST['config_organization']; else echo "Your District Name"; ?>">
				</td> <label>Address 1</label> <input required class="form-control"
					type="text" size="50" name="config_address1"
					value="<?php if(isset($_POST['config_address1'])) echo $_POST['config_address1']; else echo ""; ?>">
				</td> <label>Address 2</label> <input class="form-control"
					type="text" size="50" name="config_address2"
					value="<?php if(isset($_POST['config_address2'])) echo $_POST['config_address2']; else echo ""; ?>">
				</td> <label>Address 3</label> <input class="form-control"
					type="text" size="50" name="config_address3"
					value="<?php if(isset($_POST['config_address3'])) echo $_POST['config_address3']; else echo ""; ?>">
				</td> <label>Email Server</label> <input class="form-control"
					type="text" size="50" name="config_email"
					value="<?php if(isset($_POST['config_email'])) echo $_POST['config_email']; else echo "localhost"; ?>">
				</td>
			</div>
			<p>
				<button name="create" class="btn btn-success pull-right"
					type="submit" value="Next">Next</button>
			</p>

		</form>



	</div>
		<script src="js/jquery-2.1.1.js" type="text/javascript"></script>
	<script src="js/bootstrap.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>
</BODY>
</HTML>