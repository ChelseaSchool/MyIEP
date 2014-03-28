<?php

/** @file
 * @brief 	display logs (superuser)
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Filter input
 */  
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0;  //only super administrator



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message="";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');

header('Pragma: no-cache'); //don't cache this page!

if(isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if(!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
} else {
    if(!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
}
//************* SESSION active past here **************************

//check permission levels
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE****************
$permission_level=getPermissionLevel($_SESSION['egps_username']);
//check permission levels
if($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

  //get the list of all users...
  //wonder how php handles dangling else...
  if(!isset($_GET['iLimit']))
    if(!isset($_POST['iLimit'])) $iLimit = 50;
        else $iLimit=$_POST['iLimit'];
  else $iLimit = $_GET['iLimit'];

  if(!isset($_GET['iCur']))
    if(!isset($_POST['iCur'])) $iCur = 0;
    else $iCur=$_POST['iCur'];
  else $iCur = $_GET['iCur'];

  if(!isset($_GET['szLevel']))
    if(!isset($_POST['szLevel'])) $szLevel = "ERROR";
    else $szLevel=$_POST['szLevel'];
  else $szLevel = $_GET['szLevel'];

function getLog() {
    global $error_message,$iLimit,$iCur,$szLevel;
    if(!connectIPPDB()) {
        $error_message = $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    if($szLevel == "ALL") {
        $log_query = "SELECT * FROM error_log WHERE 1=1 ORDER BY time DESC LIMIT $iCur,$iLimit";
    } else {
        $log_query = "SELECT * FROM error_log WHERE level='$szLevel' ORDER BY time DESC LIMIT $iCur,$iLimit";

    }
    $log_result = mysql_query($log_query);
    if(!$log_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$log_query'<BR>";
        return NULL;
    }
    return $log_result;
}

function getLogTotals() {
    global $error_message,$iLimit,$iCur,$szLevel;

    if(!connectIPPDB()) {
        $error_message = $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    if($szLevel == "ALL") {
        $log_query = "SELECT * FROM error_log WHERE 1=1";
    } else {
        $log_query = "SELECT * FROM error_log WHERE level='$szLevel'";

    }
    $log_result = mysql_query($log_query);
    if(!$log_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$log_query'<BR>";
        return NULL;
    }
    return mysql_num_rows($log_result);
}


$sqlLog=getLog();
if(!$sqlLog) {
    $system_message = $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$sqlLogTotals=getLogTotals();

//set back vars...
$szBackGetVars="";
foreach($_GET as $key => $value) {
    $szBackGetVars = $szBackGetVars . $key . "=" . $value . "&";
}
//strip trailing '&'
$szBackGetVars = substr($szBackGetVars, 0, -1);


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
         -CSS and layout images are courtesy A. Clapton.
     -->

    <SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to delete:\n";
          var count = 0;
          form=document.userlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + form.elements[x].name + " ";
                     count++;
                  }
              }
          }
          if(!count) { alert("Nothing Selected"); return false; }
          if(confirm(szConfirmMessage))
              return true;
          else
              return false;
      }
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
                    <tr align="Center">
                    <td><center><img src="<?php echo $page_logo_path; ?>"></center></td>
                    </tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">- View Log Files -</p></center></td></tr></table></center>

                        <!-- search logs to be implemented...
                        <form enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_manage_users.php"; ?>" method="get">
                        <center><table width="80%"><tr>
                        <td align=center bgcolor="#E0E2F2">
                            Search Username:&nbsp;&nbsp;<input type="text" name="username" size="30">&nbsp;&nbsp;<input type="submit" value="Query">
                            <p class="small_text">(Wildcards: '%'=match any '_'=match single)</p>
                        </td>
                        </tr></table></center>
                        </form>
                        end search logs -->

                        <?php //display users... ?>
                        <form name="loglist" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_view_logs.php"; ?>" method="post">
                        <input type="hidden" name="select_level" value="1">
                        <input type="hidden" name="iCur" value="0">
                        <input type="hidden" name="iLimit" value="<?php echo $iLimit; ?>">
                        <BR>
                        <center>View:
                        <SELECT name="szLevel">
                            <OPTION value="ALL" selected>ALL
                            <OPTION value="ERROR">Errors
                            <OPTION value="WARNING">Warnings
                            <OPTION value="INFORMATIONAL">Informational
                        </SELECT>
                        Limit:
                        <INPUT name="iLimit" value="<?php echo $iLimit; ?>" size="5">
                        <INPUT type="submit" value="Go">
                        </center>
                        <center><table width="80%" border="0">

                        <?php
                        $bgcolor = "#DFDFDF";

                        //print the next and prev links...
                        echo "<tr><td>&nbsp;</td><td>";
                        if($iCur != 0) {
                            //we have previous values...
                            echo "<a href=\"./superuser_view_logs.php?iCur=" . ($iCur-$iLimit) . "&iLimit=$iLimit&szLevel=$szLevel\" class=\"default\">previous $iLimit</a>";
                        } else {
                            echo "&nbsp;";
                        }
                        echo "</td><td colspan=\"2\">";
                        echo "&nbsp;";
                        echo "</td>";
                        if(($iLimit+$iCur < $sqlLogTotals)) {
                            echo "<td align=\"right\"><a href=\"./superuser_view_logs.php?iCur=" . ($iCur+$iLimit) . "&iLimit=$iLimit&szLevel=$szLevel\" class=\"default\">next ";
                            if( $sqlLogTotals-($iCur+$iLimit) > $iLimit) {
                                echo $iLimit . "</td>";
                            } else {
                                echo ($sqlLogTotals-($iCur+$iLimit)) . "</td>";
                            }
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                        echo "</tr>\n";
                        //end print next and prev links

                        //print the header row...
                        echo "<tr><td bgcolor=\"#E0E2F2\">UID</td><td align=\"center\" bgcolor=\"#E0E2F2\">Level</td><td align=\"center\" bgcolor=\"#E0E2F2\">Username</td><td align=\"center\" bgcolor=\"#E0E2F2\">Student ID</td><td align=\"center\" bgcolor=\"#E0E2F2\">Date & Time</td><td align=\"center\" bgcolor=\"#E0E2F2\">Message</td></tr>\n";
                        while ($log_row=mysql_fetch_array($sqlLog)) {
                            echo "<tr>\n";
                            echo "<td bgcolor=\"$bgcolor\"><p class=\"small_text\">" . $log_row['uid'] . "<p></td>\n";
                            echo "<td bgcolor=\"$bgcolor\"><p class=\"small_text\">" . $log_row['level'] . "<p></td>\n";
                            echo "<td bgcolor=\"$bgcolor\"><p class=\"small_text\">" . $log_row['username'] . "<p></td>\n";
                            echo "<td bgcolor=\"$bgcolor\"><p class=\"small_text\">" . $log_row['student_id'] . "<p></td>\n";
                            echo "<td bgcolor=\"$bgcolor\"><p class=\"small_text\">" . $log_row['time'] . "<p></td>\n";
                            echo "<td bgcolor=\"$bgcolor\"><p class=\"small_text\">" . $log_row['message'] . "<p></td>\n";
                            echo "</tr>\n";
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }
                        ?>
                        </table></center>
                        </form>
                        <BR>

                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center"><table border="0" width="100%"><tr><td width="60"><a href="<?php echo IPP_PATH . "main.php"; ?>"><img src="<?php echo IPP_PATH; ?>images/back-arrow.png" border=0></a></td><td width="60"><a href="<?php echo IPP_PATH . "main.php"; ?>"><img src="<?php echo IPP_PATH; ?>images/homebutton.png" border=0></a></td><td valign="bottom" align="center">Logged in as: <?php echo $_SESSION['egps_username'];?></td><td width="60"><a href="<?php echo IPP_PATH;?>"><img src="<?php echo IPP_PATH; ?>images/logout.png" border=0></a></td></tr></table></td>
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
