<?php
/** @file
 * @brief 	launch page

 * initial screen will launch the ipp program. The rationale behind this is to get a window without navigational and file bars.
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Delete this page carefully
 */ 
 
 


/**
 * Path for eGPS required files.
 */

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');

header('Pragma: no-cache'); //don't cache this page!
if(isset($system_message)) $system_message = $system_message; else $system_message="";

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

     <SCRIPT LANGUAGE="JavaScript">
        <!-- This script and many more are available free online at -->
        <!-- The JavaScript Source!! http://javascript.internet.com -->

        <!-- Begin
           function Start(page) {
               OpenWin = window.open(page,"_blank", "toolbar=no,menubar=no,location=no,scrollbars=yes,resizable=yes");
               return FALSE;
           }
        // End -->
     </SCRIPT>
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
                    <!--  <tr align="Center">
                    <td><center><img src="<?php echo $page_logo_path; ?>"></center></td>
                    </tr>-->
                    <tr>
                        <td valign="top">
                        <div id="main">
                                <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">- <?php echo $IPP_ORGANIZATION; ?> -<BR></p></center></td></tr></table></center>
                        <form enctype="multipart/form-data" action="<?php echo IPP_PATH . 'launch.php'; ?>" method="post" onSubmit="Start('<?php echo IPP_PATH . "index.php";?>')")>
                        <center><table>
                        <tr>
                            <td>
                                    Please click the 'Launch IPP' button below to launch the IEP-IPP program
                                    in a new browser window.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                    <center><input class="sbutton" type="submit" value="Launch IEP-IPP";"></center>
                            </td>
                        </tr>
                        </table>
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
                   &nbsp;</script>  
            </td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
        <center></center>
    </BODY>
</HTML>
