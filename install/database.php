<?php
/** @file
 * @brief		Install wizard - Database configuration check
 * @copyright	Copyright (c) 2005 Grasslands Regional Division #6
 * @copyright	GPLv2
 * @copyright	Copyright (c) 2014 Chelsea School
 * @todo
 * 1. Bootstrap
 * 2. Strip copyright information
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

/**
 * @fn error_reporting(1)
 *
 * @param $level level
 *            int
 *            @detail The new error_reporting level. It takes on either a bitmask, or named constants. Using named constants is strongly encouraged to ensure compatibility for future versions. As error levels are added, the range of
 *            integers increases, so older integer-based error levels will not always behave as expected. The available error level constants and the actual meanings of these error levels are described in the predefined constants.
 *            @Return int the old error_reporting level or the current level if no level parameter is given.
 */
error_reporting(1);

/* eGPS required files. */
// require_once(IPP_PATH . 'etc/init.php');
// require_once(IPP_PATH . 'include/db.php');
// require_once(IPP_PATH . 'include/auth.php');
// if ((int)phpversion() < 5) { require_once(IPP_PATH . 'include/fileutils.php'); } //only for pre v5
// require_once(IPP_PATH . 'include/log.php');
// require_once(IPP_PATH . 'include/navbar.php');

/**
 * $var $system_message string
 * @brief $system_message set to NULL for security/sanity
 */
$system_message = "";

header('Pragma: no-cache'); // don't cache this page!
if (isset($_POST['db_host'])) {
    // we are creating the datase
    $link = mysql_connect($_POST['db_host'], $_POST['db_username'], $_POST['db_password']);
    if ($link == FALSE) {
        $system_message = "Could not connect to database: for the following reason: '" . mysql_error() . "'<BR>\n";
    } else {
        $db_user = mysql_select_db($_POST['db_name']);
        if (! $db_user) {
            $system_message = "Could not select database: '" . $mysql_user_database . "' for the following reason: '" . mysql_error() . "'</BR>\n";
        } else {
            // we are good to go ahead...
            if (isset($_POST['db_populate']) && $_POST['db_populate'] == "on") {
                // $system_message .= "Populating<BR>";
                
                $file_content = file("default.sql");
                $sql = "";
                
                $FAIL = FALSE;
                foreach ($file_content as $sql_line) {
                    if (trim($sql_line) != "" && strpos($sql_line, "--") === false) {
                        $sql .= $sql_line;
                        if (preg_match("/;[\040]*\$/", $sql_line)) {
                            $result = mysql_query($sql) or die(mysql_error());
                            // if(!$result) { $system_message .= "Cannot populate the database: " . mysql_error() . "<BR>"; $FAIL=TRUE; }
                            $sql = "";
                        }
                    }
                }
                
                // we need to update the configuration file...
                $file = file("../etc/init.php");
                if (! $file)
                    $system_message .= "Cannot open init.php configuration file. You will need to manually edit this file<BR>";
                else {
                    $updated_file = "";
                    foreach ($file as $line) {
                        // echo "Line: " . $line . "<BR>";
                        if (preg_match('/mysql_user_host/', $line)) {
                            $line = "\$mysql_user_host = \"" . $_POST['db_host'] . "\";\n";
                        }
                        if (preg_match('/mysql_data_host/', $line)) {
                            $line = "\$mysql_data_host = \"" . $_POST['db_host'] . "\";\n";
                        }
                        if (preg_match('/mysql_user_database/', $line)) {
                            $line = "\$mysql_user_database = \"" . $_POST['db_name'] . "\";\n";
                        }
                        if (preg_match('/mysql_data_database/', $line)) {
                            $line = "\$mysql_data_database = \"" . $_POST['db_name'] . "\";\n";
                        }
                        if (preg_match('/mysql_data_username/', $line)) {
                            $line = "\$mysql_data_username= \"" . $_POST['db_username'] . "\";\n";
                        }
                        if (preg_match('/mysql_user_username/', $line)) {
                            $line = "\$mysql_user_username = \"" . $_POST['db_username'] . "\";\n";
                        }
                        if (preg_match('/mysql_user_password/', $line)) {
                            $line = "\$mysql_user_password = \"" . $_POST['db_password'] . "\";\n";
                        }
                        if (preg_match('/mysql_data_password/', $line)) {
                            $line = "\$mysql_data_password = \"" . $_POST['db_password'] . "\";\n";
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
                    }
                    
                    header("Location: config.php");
                }
            } else {
                $file = file("../etc/init.php");
                
                if (! $file)
                    $system_message .= "Cannot open init.php configuration file. You will need to manually edit this file<BR>";
                else {
                    $updated_file = "";
                    
                    foreach ($file as $line) {
                        
                        if (preg_match('/mysql_user_host/', $line)) {
                            $line = "\$mysql_user_host = \"" . $_POST['db_host'] . "\";\n";
                        }
                        if (preg_match('/mysql_data_host/', $line)) {
                            $line = "\$mysql_data_host = \"" . $_POST['db_host'] . "\";\n";
                        }
                        if (preg_match('/mysql_user_database/', $line)) {
                            $line = "\$mysql_user_database = \"" . $_POST['db_name'] . "\";\n";
                        }
                        if (preg_match('/mysql_data_database/', $line)) {
                            $line = "\$mysql_data_database = \"" . $_POST['db_name'] . "\";\n";
                        }
                        if (preg_match('/mysql_data_username/', $line)) {
                            $line = "\$mysql_data_username= \"" . $_POST['db_username'] . "\";\n";
                        }
                        if (preg_match('/mysql_user_username/', $line)) {
                            $line = "\$mysql_user_username = \"" . $_POST['db_username'] . "\";\n";
                        }
                        if (preg_match('/mysql_user_password/', $line)) {
                            $line = "\$mysql_user_password = \"" . $_POST['db_password'] . "\";\n";
                        }
                        if (preg_match('/mysql_data_password/', $line)) {
                            $line = "\$mysql_data_password = \"" . $_POST['db_password'] . "\";\n";
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
                    }
                    
                    header("Location: config.php");
                }
            }
        }
    }
}
?>
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
<TITLE>MyIEP Installation: MySQL Connection</TITLE>
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
					<!--  For later sprint
					<li><a title="Not enabled yet" href="about.php" title="About MyIEP"><span
							class="glyphicon glyphicon-info-sign"></span></a></li>
					<li><a href="help.php" title="Some Help Here"><span
							class="glyphicon glyphicon-question-sign"></span></a></li>-->
				</ul>
			</div>
			<!--/.navbar-collapse -->
		</div>
	</div>

	<div class="jumbotron">
		<div class="container">
			<h1>MyIEP</h1>
			<h2>
				Installation: <small>MySQL Settings</small>
			</h2>
			<!-- Progress Bar -->
			<h3>Progress</h3>
			<div class="progress progress-striped">
			      <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="66"
					aria-valuemin="0" aria-valuemax="66" style="width: 66%;"><span class="sr-only">66% Complete</span></div>
			</div>
			<p>MyIEP is a free and open, web-based IEP management system.</p>
			<p>
				&copy; 2014 Chelsea School - <a
					href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a>.
                <?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>
			
			</p>
			
			</div>
		</div>
	</div>
	<div class="container">

		<form enctype="multipart/form-data" action="./database.php"
			method="post">
			<div class="form-group">


			<label>
				Database Host</label>
				<input class="form-control" type="text" name="db_host"
					value="<?php if(isset($_POST['db_host'])) echo $_POST['db_host']; else echo "localhost"; ?>">
			

			<label>
				Database Name</label>
				<input  class="form-control" required autocomplete="off" type="text"
					name="db_name"
					value="<?php if(isset($_POST['db_name'])) echo $_POST['db_name']; else echo "ipp"; ?>">
			

			<label>
				Database Username</label>
				<input  class="form-control" required autocomplete="off" type="text"
					name="db_username"
					value="<?php if(isset($_POST['db_username'])) echo $_POST['db_username']; else echo "ipp"; ?>">
			

			<label>Database Password</label>
				<input class="form-control" required autocomplete="off" "password" name="db_password">
            </div>
			<label>
				Populate Database (uncheck if this is an upgrade)</label><input
					type="checkbox" name="db_populate"
					<?php if(!isset($_POST['db_populate'])) echo "CHECKED"; else echo "CHECKED"?>>
            
			<button name="create" class="btn btn-success pull-right" type="submit"
				value="Next">Next</button>
            </div>
		</form>







	</div>
	<script src="js/jquery-2.1.1.js" type="text/javascript"></script>
	<script src="js/bootstrap.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>
</BODY>
</HTML>