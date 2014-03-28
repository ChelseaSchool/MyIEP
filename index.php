<?php
/*! @file
 *  @brief 	Landing AND login page
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @remark		Now HTML5
 * @todo		
 * 1. Filter input
 * 2. fix html tag problem (extra rectangle appears on browser rendering
 */

define('IPP_PATH','./');





//this is unnecessary
if(!defined('IPP_PATH')) define('IPP_PATH','./');

//check if we are running install wizard
if(!is_file(IPP_PATH . "etc/init.php")){
	require_once(IPP_PATH . 'install/index.php');
	exit();
}
/* eGPS required files. */
require_once(IPP_PATH . "etc/init.php");
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'etc/init.php');
include_once(IPP_PATH . 'include/db.php');

header('Pragma: no-cache'); //don't cache this page!
logout();

if(isset($system_message)) $system_message = $system_message; else $system_message="";
if(isset($LOGIN_NAME)) $LOGIN_NAME = $LOGIN_NAME; else $LOGIN_NAME="";

?> 
<!DOCTYPE html>
<html lang="en">
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>

</HEAD>
    <BODY>
        <table class="shadow" border="0" cellspacing="0" cellpadding="0" align="center">  
        <!--<tr>
          <td class="shadow-topLeft"></td>
            <td class="shadow-top"></td>
            <td class="shadow-topRight"></td>
        </tr>-->
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
                                <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>
<BR><BR>
                        <center><table><tr><td><center><p class="header">MyIEP Login -</p></center></td></tr></table></center>
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
        <center></center>
    </BODY>
</HTML>
