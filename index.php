<?php

/**
 * login.php -- simple login screen
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 * All rights reserved
 *
 * This a simple login screen.
 * Created: June 06, 2005
 * Modified:
 *
 */

/**
 * Path for eGPS required files.
 */

if(!defined('IPP_PATH')) define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
include_once(IPP_PATH . 'include/db.php');

header('Pragma: no-cache'); //don't cache this page!

if(isset($MESSAGE)) $MESSAGE = $MESSAGE; else $MESSAGE="";
if(isset($LOGIN_NAME)) $LOGIN_NAME = $LOGIN_NAME; else $LOGIN_NAME="";

?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
    <!-- All code Copyright &copy; 2005 Grasslands Regional Division #6.
         -Concept and Design by Grasslands IPP Focus Group 2005
         -Programming and Database Design by M. Nielsen, Grasslands
          Regional Division #6
         -User Interface Design and Educational Factors by P Stoddart
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
                    <td><center><img src="<?php echo $page_logo_path; ?>"></center></td>
                    </tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                                <?php if ($MESSAGE) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $MESSAGE . "</p></td></tr></table></center>";} ?>
<BR><BR>
                        <center><table><tr><td><center><p class="header">- IEP-IPP Login -</p></center></td></tr></table></center>
                        <form enctype="multipart/form-data" action="<?php echo IPP_PATH . 'main.php'; ?>" method="post">
                        <center><table>
                            <tr>
                                <td>
                                    <p class="text">Login Name
                                </td>
                                <td width="50">
                                    <input type="text" size="28" name="LOGIN_NAME" value="<?php echo $LOGIN_NAME;?>">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="text">Password
                                </td>
                                <td width="50">
                                    <input type="password" size="30" name="PASSWORD" value="">
                                </td>
                            </tr>
                        </table>
                                    <input class="sbutton" type="submit" value="Submit">
                        </center>
                        </form>
                        </div>
                        </td>
                    </tr>
                </table> 
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center" valign="top">&nbsp;</p></right></td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center" halign="right">
            &nbsp;</td>
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
