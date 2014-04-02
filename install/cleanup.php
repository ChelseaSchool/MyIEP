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
 <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">- Installation Cleanup -</p></center></td></tr></table></center>
        <BR><center><table width="80%" border="0"><tr><td>
        <?php
          //attempt to chmod 755 config.php
          if(!chmod("../etc/init.php",0755)) { echo "Could not set permissions on init.php file you should do this manually:<BR><BR>'chmod " . realpath("../etc/init.php") . " 755' or higher to suit your system.<BR><BR>"; } else { echo "Permissions on init.php set to 755. On a public, multiuser system, you may want to change this."; }
        ?>
           You can go to <a href="<?php echo $IPP_PAGE_ROOT;?>"><?php echo $IPP_PAGE_ROOT;?></a> to access the IEP-IPP system.

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
