<?php
/**@file
 * @brief		completes the installation
 * @todo		Theme with bootstrap
 * 
 */
 //set the config file flag to prevent security problems with this install directory.
$file=file("../etc/init.php");
               if(!$file) $system_message .= "Cannot open init.php configuration file. You will need to manually set IPP_IS_CONFIGURED to true<BR>";
               else {
                 $updated_file = "";
                 foreach($file as $line) {
                  // echo "Line: " . $line . "<BR>";
                  if(preg_match('/IPP_IS_CONFIGURED/',$line)) {$line = "\$IPP_IS_CONFIGURED= TRUE;\n";}
                   $updated_file .= $line;
                   //echo $line . "<BR>";
                 }
              $handle=fopen("../etc/init.php","w");
              if(!$handle) { $fail=TRUE; echo "Cannot open init.php configuration file for writing (" . realpath("../etc/init.php") . "). You will need to manually edit this file<BR>";} else {
                     fwrite($handle,$updated_file);
                     fclose($handle);
                  }

              }

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;
/**
 * install wizard
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 * All rights reserved
 *
 * Created: February 17, 2007.
 * By: M. Nielsen
 */

/**
 * Path for required files.
 */
error_reporting(0);

$system_message = "";

/* eGPS required files. */
require_once('../etc/init.php');
//require_once(IPP_PATH . 'include/db.php');
//require_once(IPP_PATH . 'include/auth.php');
//if ((int)phpversion() < 5) { require_once(IPP_PATH . 'include/fileutils.php'); } //only for pre v5
//require_once(IPP_PATH . 'include/log.php');
//require_once(IPP_PATH . 'include/navbar.php');

header('Pragma: no-cache'); //don't cache this page!

?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
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
					<li class="active"><a href="cleanup.php" title="Cleaning Up">Clean Up</a></li>
					
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
				Installation: <small>Clean Up</small>
			</h2>
			<p>MyIEP is a free and open, web-based IEP management system.</p>
			<p>
				&copy; 2014 Chelsea School - <a
					href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a>.
			</p>
			 <?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>
			
			<!-- Progress Bar -->
			<div class="progress progress-striped">
				<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100"
					aria-valuemin="0" aria-valuemax="100" style="width: 100%;"><span class="sr-only">100% Complete</span></div>
			</div>


		</div>
	</div>
	<div class="container">
        <table class="shadow" border="0" cellspacing="0" cellpadding="0" align="center">  
        <tr>
          <td class="shadow-topLeft"></td>
            <td class="shadow-top"></td>
            <td class="shadow-topRight"></td>
        </tr>
        <tr>
            <td class="shadow-left"></td>
            <td class="shadow-center" valign="top">
                <table class="frame" width=620px align=center border="0">
                    
                    <tr><td>
                    &nbsp;
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">

        <BR><center><table width="80%" border="0"><tr><td>
        <?php
          //attempt to chmod 755 config.php
          if(!chmod("../etc/init.php",0755)) { echo "<p>Could not set permissions on init.php file you should do this manually:</p><p>'chmod " . realpath("../etc/init.php") . " 755' or higher to suit your system.</p>"; } else { echo "<p>Permissions on init.php set to 755. On a public, multiuser system, you may want to change this.</p>"; }
        ?>
        <p>For the sake of security, you may choose to delete the install directory.</p>
        <p><a href="../index.php"><button class="btn btn-success pull-right">Login</button></a></p>
                </td></tr></table></center>
                        
                        </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center">&nbsp;</td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
    </div>
    	<script src="js/jquery-2.1.1.js" type="text/javascript"></script>
	<script src="js/bootstrap.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>    
    </BODY>
</HTML>
