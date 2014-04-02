<?php

/** @file
 * @brief 		This part of install wizard checks status and makes sure everything is good to proceed
 * @todo
 * 1. Rebrand and theme
 * 2. Copyright Block
 */

//check if we have an init.php file already...security problem
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
 * Path for required files.
 */

error_reporting(1);

/* eGPS required files. */
//require_once(IPP_PATH . 'etc/init.php');
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
    <style type="text/css" media="screen">
        <!--
            @import "../layout/greenborders.css";
        -->
    </style>
    
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

                        <center><table><tr><td><center><p class="header">- Installation Permissions -</p></center></td></tr></table></center>
        <BR><center><table width="80%" border="0"><tr><td>
        <?php
               $option = "Alternatively, you may copy the init.dist.php file to init.php and edit manually.<BR><BR>";
               $fail=FALSE;
               $file=file("../etc/init.dist.php");
               if(!$file) {$fail=TRUE; echo "Cannot open init.dist.php configuration file for read (" . realpath("../etc/init.dist.php") . "). Set the file permissions and reload this page<BR>$option";}
               else {        
                  $handle=fopen("../etc/init.php","wc");
                  if(!$handle) { $fail=TRUE; echo "Cannot open init.php configuration file for writing (" . realpath("../etc/init.php") . "). You might need to create this file, set the permissions, and reload this page<BR><BR>('touch init.php' and 'chmod 777 init.php' in a shell from the " . realpath("../etc") . " directory)<BR><BR>$option";} else {
                     foreach($file as $line) {
                        fwrite($handle,$line);
                     }
                     fclose($handle);
                  }
               }
               
               if(!$fail) {
                 echo realpath("../etc/init.php") . " is writable. Copied default install. Click next";
               }
                //$fail=FALSE;
                //if(is_writable("../etc/init.php")) {
                // echo "Configuration file (../etc/init.php) is not writeable (FAIL)";
                // $fail=TRUE;
                //} else {
                // echo "Configuration file is writeable (PASS)";
                //}

                ?>
                </td></tr></table></center>
                        <?php 
   echo "<form enctype=\"multipart/form-data\" action=\"./database.php" . "\" method=\"post\">";
   echo " <center><input class=\"sbutton\" type=\"submit\" value=\"Next\"";
   if($fail) echo " DISABLED";
   echo "></center>";
   echo "</form>";

                        
                        ?>
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
        
    </BODY>
</HTML>