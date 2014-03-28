<?php
if(is_file("../etc/init.php")) {
   define('IPP_PATH','../');
   require_once("../etc/init.php");
   if(isset($IPP_IS_CONFIGURED) && $IPP_IS_CONFIGURED) {
      die("The configuration file:" . realpath("../etc/init.php") . " already exists and the IPP_IS_CONFIGURED flag is set. For security reasons you cannot rerun this page. If you want to rerun the install please manually delete the config file.");
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
error_reporting(1);

$system_message = "";

/* eGPS required files. */
//require_once(IPP_PATH . 'etc/init.php');
//require_once(IPP_PATH . 'include/db.php');
//require_once(IPP_PATH . 'include/auth.php');
//if ((int)phpversion() < 5) { require_once(IPP_PATH . 'include/fileutils.php'); } //only for pre v5
//require_once(IPP_PATH . 'include/log.php');
//require_once(IPP_PATH . 'include/navbar.php');

header('Pragma: no-cache'); //don't cache this page!

if(isset($_POST['update'])) {
  //we need to update the config file
  $file=file("../etc/init.php");
               if(!$file) $system_message .= "Cannot open init.php configuration file. You will need to manually edit this file<BR>";
               else {
                 $updated_file = "";
                 foreach($file as $line) {
                  // echo "Line: " . $line . "<BR>";
                   if(preg_match('/page_title/',$line)) {$line = "\$page_title = \"" . $_POST['config_title'] . "\";\n";}
                   if(preg_match('/IPP_PAGE_ROOT/',$line)) {$line = "\$IPP_PAGE_ROOT = \"" . $_POST['config_url'] . "\";\n";}
                   if(preg_match('/IPP_ORGANIZATION /',$line)) {$line = "\$IPP_ORGANIZATION = \"" . $_POST['config_organization'] . "\";\n";}
                   if(preg_match('/IPP_ORGANIZATION_ADDRESS1/',$line)) {$line = "\$IPP_ORGANIZATION_ADDRESS1 = \"" . $_POST['config_address1'] . "\";\n";}
                   if(preg_match('/IPP_ORGANIZATION_ADDRESS2/',$line)) {$line = "\$IPP_ORGANIZATION_ADDRESS2 = \"" . $_POST['config_address2'] . "\";\n";}
                   if(preg_match('/IPP_ORGANIZATION_ADDRESS3/',$line)) {$line = "\$IPP_ORGANIZATION_ADDRESS3 = \"" . $_POST['config_address3'] . "\";\n";}

                   if(preg_match('/mail_host/',$line)) {$line = "\$mail_host= \"" . $_POST['config_email'] . "\";\n";}
                   $updated_file .= $line;
                   //echo $line . "<BR>";
                 }
              $handle=fopen("../etc/init.php","w");
              if(!$handle) { $fail=TRUE; echo "Cannot open init.php configuration file for writing (" . realpath("../etc/init.php") . "). You will need to manually edit this file<BR>";} else {
                     fwrite($handle,$updated_file);
                     fclose($handle);
                     header("Location: cleanup.php");
                  }

              }
}
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE>IEP-IPP Installation</TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "../layout/greenborders.css";
        -->
    </style>
    <!-- All code Copyright &copy; 2005 Grasslands Regional Division #6.
         -Concept and Design by Grasslands IPP Focus Group 2005
         -Programming and Database Design by M. Nielsen, Grasslands
          Regional Division #6
         -CSS and layout images are courtesy A. Clapton.
     -->
</HEAD>
    <BODY>
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
                    <tr align="Center">
                    <td><center><img src="../images/banner.png"></center></td>
                    </tr>
                    <tr><td>
                    &nbsp;
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
 <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">- Installation Configuration -</p></center></td></tr></table></center>
        <BR><center><table width="80%" border="0"><tr><td>


<form enctype="multipart/form-data" action="./config.php" method="post">
<input type="hidden" name="update" value="1">
                <center><table width="80%" border="0">
                  <tr>
                    <td>Page Title</td>
                    <td><input type="text" size="50" name="config_title" value="<?php if(isset($_POST['config_title'])) echo $_POST['config_title']; else echo "IEP-IPP Special Education Program Plans"; ?>"></td>
                  </tr>
                  <tr>
                    <td>URL</td>
                    <td><input type="text" size="50" name="config_url" value="<?php if(isset($_POST['config_url'])) echo $_POST['config_url']; else {if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on") echo "https://"; else echo "http://"; echo $_SERVER['SERVER_NAME'] . str_replace('/install/config.php','',$_SERVER['REQUEST_URI']);} ?>"></td>
                  </tr>
                  <tr>
                    <td>Organization</td>
                    <td><input type="text" size="50" name="config_organization" value="<?php if(isset($_POST['config_organization'])) echo $_POST['config_organization']; else echo "Your District Name"; ?>"></td>
                  </tr>
                  <tr>
                    <td>Address 1</td>
                    <td><input type="text" size="50" name="config_address1" value="<?php if(isset($_POST['config_address1'])) echo $_POST['config_address1']; else echo ""; ?>"></td>
                  </tr>
                  <tr>
                    <td>Address 2</td>
                    <td><input type="text" size="50" name="config_address2" value="<?php if(isset($_POST['config_address2'])) echo $_POST['config_address2']; else echo ""; ?>"></td>
                  </tr>
                  <tr>
                    <td>Address 3</td>
                    <td><input type="text" size="50" name="config_address3" value="<?php if(isset($_POST['config_address3'])) echo $_POST['config_address3']; else echo ""; ?>"></td>
                  </tr>
                  <tr>
                    <td>Email Server</td>
                    <td><input type="text" size="50" name="config_email" value="<?php if(isset($_POST['config_email'])) echo $_POST['config_email']; else echo "localhost"; ?>"></td>
                  </tr>
                </table><input name="create" class="sbutton" type="submit" value="Next"></center>
                </form>


<BR>*these entries are changeable later by editing the etc/init.php file
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
        <center>System Copyright &copy; 2005 Grasslands Regional Division #6.</center>
    </BODY>
</HTML>