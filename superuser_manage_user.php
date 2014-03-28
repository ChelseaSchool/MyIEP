<?php

/** @file
 * @brief 	user managment ?
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Investigate purpose of this page
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 20;



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message ="";
if(isset($szBackGetVars)) $szBackGetVars = $szBackGetVars; else $szBackGetVars= "";

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
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL && !(isLocalAdministrator($_SESSION['egps_username']))) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

$permission_level = getPermissionLevel($_SESSION['egps_username']);

$ippuserid="";
if(isset($_GET['ippuserid'])) $ippuserid=mysql_real_escape_string($_GET['ippuserid']);
   else $ippuserid=mysql_real_escape_string($_POST['ippuserid']);

//we want to run a check to make sure that if we are a local admin that
//we can't access a person not at our school...
if(isLocalAdministrator($_SESSION['egps_username']) && getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL) {
  //we are a local administrator with no other access rights (ie we're a local admin but not a principal as well)
  $user_query= "SELECT * FROM support_member WHERE egps_username='$ippuserid'";
  $user_result = mysql_query($user_query);
  if(!$user_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$user_query'<BR>";
    $system_message= $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {
    if(mysql_num_rows($user_result) <= 0) "IPP Member not found<BR>Query=$user_query";
    $user_row=mysql_fetch_array($user_result);
  }

  $us_query= "SELECT * FROM support_member WHERE egps_username='" . $_SESSION['egps_username'] . "'";
  $us_result = mysql_query($us_query);
  if(!$us_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$us_query'<BR>";
    $system_message= $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {
    if(mysql_num_rows($us_result) <= 0) $system_message .= "IPP Member not found<BR>Query=$us_query";
    $us_row=mysql_fetch_array($us_result);
  }

  if($user_row['school_code'] != $us_row['school_code']) {
     $system_message = $system_message . "You do not have permission to view this page. You must be in the same school as this person to edit their information. (" . $user_row['school_code'] . "!=" . $us_row['school_code'] . ")";
     IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     require(IPP_PATH . 'security_error.php');
     exit();
  }
}

//************** validated past here SESSION ACTIVE****************


if(isset($_POST['Update'])) {
   //we are updating this users information...
   $update_query = "UPDATE support_member SET egps_username='$ippuserid',";  //do this so we start with a comma.
   $update_query .= "first_name='" . mysql_real_escape_string($_POST['first_name']) . "',";
   $update_query .= "last_name='" . mysql_real_escape_string($_POST['last_name']) . "',";
   $update_query .= "email='" . mysql_real_escape_string($_POST['email']) . "',";
   if($permission_level <= 20 || (isLocalAdministrator($_SESSION['egps_username']))) {
      if(($_POST['permission_level'] > 20 && (isLocalAdministrator($_SESSION['egps_username']))) || $permission_level==0) {
         $update_query .= " permission_level=" . mysql_real_escape_string($_POST['permission_level']) . ",";
      } else {
         $system_message .= "You do not have permission to make this modification to this IPP members permission level<BR>";
      }
      if($permission_level==0) {
        $update_query .= " school_code=" . mysql_real_escape_string($_POST['school_code']) . ",";
        $update_query .= " is_local_ipp_administrator='";
        if(isset($_POST['is_local_ipp_administrator'])) $update_query .= "Y";
        else $update_query .= "N";
        $update_query .= "',";
      }
      //strip off trailing ','...
      $update_query = substr($update_query, 0, -1);

      $update_query .= " WHERE egps_username='$ippuserid'";
      if($permission_level != 0) $update_query .= " AND permission_level > 20";
      $update_query .= " LIMIT 1";
      //$system_message .= $update_query . "<BR>";
      $update_result = mysql_query($update_query);
      if(!$update_result) {
           $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
           $system_message=$system_message . $error_message;
           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
      } else {
         //redirect back to the staff list...
      }
   } else {
      $system_message .= "You don't have the permission level necessary to do this<BR>";
   }

   //$system_message .= "-->" . $_POST['is_local_ipp_administrator'] . "<--";
}

$user_query= "SELECT * FROM support_member WHERE egps_username='$ippuserid'";
$user_result = mysql_query($user_query);
if(!$user_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$user_query'<BR>";
    $system_message= $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
  if(mysql_num_rows($user_result) <= 0) $system_message .= "IPP Member not found<BR>";
  $user_row=mysql_fetch_array($user_result);
}

$school_query="SELECT * FROM school WHERE 1=1";
$school_result=mysql_query($school_query);

if(!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$permission_query = "SELECT * FROM permission_levels WHERE 1=1 ORDER BY level DESC ";
$permission_result = mysql_query($permission_query);
if(!$permission_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$permission_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

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
         -User Interface Design and Educational Factors by P Stoddart,
          Grasslands Regional Division #6
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
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">- Manage Member -</p></center></td></tr></table></center>

                        <center><table width="80%" border="0"><tr>
                          <td align="center">
                          <?php echo "<a href=\"" . IPP_PATH . "change_ipp_password.php?username=" . $user_row['egps_username'] . "\"><img src=\"" . IPP_PATH  . "images/mainbutton.php?title=Change Password\" border=0>\n";
                          ?>
                          </td>
                        </tr>
                        </table></center><BR>

                        <center>
                        <form enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_manage_user.php"; ?>" method="post">
                        <input type="hidden" name="ippuserid" value="<?php echo $user_row['egps_username']; ?>">
                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">Edit and Click Update</p>
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="right">Username:</td><td bgcolor="#E0E2F2"><input type="text" value="<?php echo $user_row['egps_username']; ?>" disabled name="userid" length="30"></td><td align="left" bgcolor="#E0E2F2" rowspan="8">&nbsp;&nbsp;<input type="submit" name="Update" value="Update"></td>
                        </tr>
                        <tr>
                        <td bgcolor="#E0E2F2" align="right">First Name:</td>
                        <td bgcolor="#E0E2F2"><input type="text" name="first_name" value="<?php echo $user_row['first_name'];?>"></td>
                        </tr>
                        <tr>
                        <td bgcolor="#E0E2F2" align="right">Last Name:</td>
                        <td bgcolor="#E0E2F2"><input type="text" name="last_name" value="<?php echo $user_row['last_name'];?>"></td>
                        </tr>
                        <tr>
                        <td bgcolor="#E0E2F2" align="right">Email:</td>
                        <td bgcolor="#E0E2F2"><input type="text" name="email" value="<?php echo $user_row['email'];?>"></td>
                        </tr>

                        <tr><td bgcolor="#E0E2F2" align="right">School </td><td bgcolor="#E0E2F2">
                        <SELECT name="school_code" <?php if($permission_level != 0) echo "disabled"; ?>>
                        <?php
                            while($school_row=mysql_fetch_array($school_result)) {
                                if($user_row['school_code'] == $school_row['school_code']) {
                                    echo "<option value=\"" . $school_row['school_code'] . "\" selected>" .  $school_row['school_name'] . "\n";
                                } else {
                                    echo "<option value=\"" . $school_row['school_code'] . "\">" .  $school_row['school_name'] . "\n";
                                }
                            }
                        ?>
                        </SELECT>
                        </td></tr>
                        <tr><td bgcolor="#E0E2F2" align="right">Permission Level </td><td bgcolor="#E0E2F2">
                        <?php
                            echo "<SELECT name=\"permission_level\" style=\"width:200px;text-align: left;\">\n";
                              while($pval = mysql_fetch_array($permission_result)) {
                                 if($permission_level == 0 || $pval['level'] > 20) //only allow school based to add up to principal.
                                  echo "\t<OPTION value=" . $pval['level'];
                                  if($user_row['permission_level'] == $pval['level']) echo " selected ";
                                  echo  ">" . $pval['level_name'] . "</OPTION>\n";
                              }
                              echo "</SELECT>\n"
                        ?>
                        </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="right">Local Administrator:</td><td bgcolor="#E0E2F2"><input type="checkbox" name="is_local_ipp_administrator" <?php if($user_row['is_local_ipp_administrator'] =='Y') echo "checked"; ?> <?php if($permission_level != 0) echo "disabled"; ?>></td>
                        </tr>
                        <tr>
                        <td colspan="2" bgcolor="#E0E2F2" align="center"><p class="small_text">(Wildcards: '%'=match any '_'=match single)</p></td>
                        </tr>
                        </table>
                        <input type="hidden" name="szBackGetVars" value="<?php echo $szBackGetVars; ?>">
                        </form>
                        </center>

                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center"><table border="0" width="100%"><tr><td width="60"><a href="
            <?php
                echo IPP_PATH . "superuser_manage_users.php?$szBackGetVars";
            ?>"><img src="<?php echo IPP_PATH; ?>images/back-arrow-white.png" border=0></a></td><td width="60"><a href="<?php echo IPP_PATH . "main.php"; ?>"><img src="<?php echo IPP_PATH; ?>images/homebutton-white.png" border=0></a></td><td valign="bottom" align="center">Logged in as: <?php echo $_SESSION['egps_username'];?></td><td align="right"><a href="<?php echo IPP_PATH;?>"><img src="<?php echo IPP_PATH; ?>images/logout-white.png" border=0></a></td></tr></table></td>
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
