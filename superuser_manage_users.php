<?php

/** @file
 * @brief 	mangage users main menu (superuser)
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Investigate and clarify script function
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0; //Super admin only (note: exception is local administrators)



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message = "";

if(!defined('IPP_PATH')) define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/mail_functions.php');

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

//************** validated past here SESSION ACTIVE****************
$permission_level=getPermissionLevel($_SESSION['egps_username']);
//check permission levels
if($permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//check if we are deleting some people
if(isset($_GET['delete_users']) || isset($_GET['delete_users_x'])) {

    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    $delete_query="";
    if($permission_level == 0) {
       $delete_query = "DELETE FROM support_member WHERE ";
    } else {
       $delete_query = "DELETE FROM support_member WHERE school_code='" . getUserSchoolCode($_SESSION['egps_username']) . "' AND ";
    }
    foreach($_GET as $key => $value) {
        if($key != "delete_users" && $value=="on"  )
        $delete_query = $delete_query . "egps_username='" . str_replace("_",".",$key) . "' or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //echo $delete_query;

    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    //$system_message = $delete_query;
}

//check if we are deleting some people
if((isset($_GET['set_local_admin_users']) || isset($_GET['set_local_admin_users_x'])) && $permission_level==0 ) {  //only super admins

    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    $update_query = "UPDATE support_member SET is_local_ipp_administrator='Y' WHERE ";
    foreach($_GET as $key => $value) {
        if($key != "delete_users" && $value=="on"  )
        $update_query = $update_query . "egps_username='" . str_replace("_",".",$key) . "' or ";
    }
    //strip trailing 'or' and whitespace
    $update_query = substr($update_query, 0, -4);
    //echo $delete_query;

    $update_result = mysql_query($update_query);
    if(!$update_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
      //send a notification to the people set as site based ipp admin.
      foreach($_POST as $key => $value) {
        if($key != "delete_users" && $value=="on"  )
           mail_notification(mysql_real_escape_string(str_replace("_",".",$key)),
"This email has been sent to you to notify you that you have been set as one of the school based IPP administrators for your school.

This means you have full access to all of the IPP's at your school to move and assign permissions to the IPP's there. You are able to add teaching and TA staff members onto the IPP system for your school and you will be sent notifications when students are moved into your school so that you are able to assign the IPPs to the appropriate person.
");
      }
    }
    //$system_message = $delete_query;
}

//check if we are deleting some people
if((isset($_GET['unset_local_admin_users']) || isset($_GET['unset_local_admin_users_x'])) && $permission_level==0) { //only super admins
    //$system_message .= "Debug Msg: unsetting local admin<BR>";
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    $update_query = "UPDATE support_member SET is_local_ipp_administrator='N' WHERE ";
    foreach($_GET as $key => $value) {
        if($key != "delete_users" && $value=="on"  )
        $update_query = $update_query . "egps_username='" . str_replace("_",".",$key) . "' or ";
    }
    //strip trailing 'or' and whitespace
    $update_query = substr($update_query, 0, -4);
    //echo $delete_query;

    $update_result = mysql_query($update_query);
    if(!$update_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    //$system_message = $delete_query;
}


//find number of support_members
$iNumSupportMembers = getNumUsers();
if($iNumSupportMembers == NULL) {
    //throw an error
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//find number of support_members online
$iNumSupportMembersOnline = getNumUsersOnline();
if($iNumSupportMembersOnline == NULL) {
    //throw an error
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//get the list of all users...

if(!isset($_GET['iLimit'])) $iLimit = 10; else $iLimit = $_GET['iLimit'];
if(!isset($_GET['iCur'])) $iCur = 0; else $iCur = $_GET['iCur'];
if(isset($_POST['iCur']) && $_POST['iCur'] != "" ) $iCur=$_POST['iCur'];
function getUsers() {
    global $error_message,$iLimit,$iCur,$bShowNav,$system_message;
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    if(!isset($_GET['username'])) {
        if(isset($_GET['showall'])) {
            $query = "SELECT * FROM support_member LEFT JOIN school ON support_member.school_code=school.school_code where 1=1 ORDER BY egps_username ASC";
        } else {
            $query = "SELECT * FROM support_member LEFT JOIN school ON support_member.school_code=school.school_code where 1=1 ORDER BY egps_username ASC LIMIT $iCur,$iLimit";
            $bShowNav = TRUE;
        }
    } else {
        $query = "SELECT * FROM support_member LEFT JOIN school ON support_member.school_code=school.school_code WHERE egps_username LIKE '" . $_GET['username'] . "' ORDER BY egps_username ASC";

    }

    if(isset($_GET['index'])) {
      $query = "SELECT * FROM support_member LEFT JOIN school on support_member.school_code=school.school_code WHERE ASCII(LOWER(egps_username)) >= ASCII('" . mysql_real_escape_string($_GET['index']) . "') ORDER BY egps_username ASC LIMIT $iLimit";
      //do some moronic thing to find our index- were I not so lazy I'd find a more elegant method.
      $get_index_query="SELECT * FROM support_member LEFT JOIN school on support_member.school_code=school.school_code WHERE ASCII(LOWER(egps_username)) < ASCII('" . mysql_real_escape_string($_GET['index']) . "')";
      $get_index_result=mysql_query($get_index_query);
      if($get_index_result) $iCur=mysql_num_rows($get_index_result);
      else $system_message .= "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$get_index_query'<BR>";
    }
    $result = mysql_query($query);
    if(!$result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
        return NULL;
    }
    return $result;
}

$sqlUsers=getUsers();
if(!$sqlUsers) {
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

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


    <SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to modify or delete:\n";
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
    <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
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

                        <center><table><tr><td><center><p class="header">-Manage Users-</p></center></td></tr></table></center>
                        <center><table width="80%" border="0"><tr>
                          <td align="center">
                          <?php echo "<p class=\"info_text\">There are currently $iNumSupportMembers support members registered on the system district-wide; $iNumSupportMembersOnline currently online.</p>";  ?>
                          </td>
                        </tr>
                        </table></center>

                        <center><table width="80%" border="0"><tr>
                          <td align="center">
                          <?php echo "<a href=\"" . IPP_PATH . "superuser_new_member_2.php?$szBackGetVars\"><img src=\"" . IPP_PATH  . "images/mainbutton.php?title=New Member\" border=0>\n";
                          ?>
                          </td>
                        </tr>
                        </table></center>

                        <table width="100%" border="0">
                        <tr><td nowrap>
                        <a href="superuser_manage_users.php?index=a" class="small">a</a>&nbsp;
                        <a href="superuser_manage_users.php?index=b" class="small">b</a>&nbsp;
                        <a href="superuser_manage_users.php?index=c" class="small">c</a>&nbsp;
                        <a href="superuser_manage_users.php?index=d" class="small">d</a>&nbsp;
                        <a href="superuser_manage_users.php?index=e" class="small">e</a>&nbsp;
                        <a href="superuser_manage_users.php?index=f" class="small">f</a>&nbsp;
                        <a href="superuser_manage_users.php?index=g" class="small">g</a>&nbsp;
                        <a href="superuser_manage_users.php?index=h" class="small">h</a>&nbsp;
                        <a href="superuser_manage_users.php?index=i" class="small">i</a>&nbsp;
                        <a href="superuser_manage_users.php?index=j" class="small">j</a>&nbsp;
                        <a href="superuser_manage_users.php?index=k" class="small">k</a>&nbsp;
                        <a href="superuser_manage_users.php?index=l" class="small">l</a>&nbsp;
                        <a href="superuser_manage_users.php?index=m" class="small">m</a>&nbsp;
                        <a href="superuser_manage_users.php?index=n" class="small">n</a>&nbsp;
                        <a href="superuser_manage_users.php?index=o" class="small">o</a>&nbsp;
                        <a href="superuser_manage_users.php?index=p" class="small">p</a>&nbsp;
                        <a href="superuser_manage_users.php?index=q" class="small">q</a>&nbsp;
                        <a href="superuser_manage_users.php?index=r" class="small">r</a>&nbsp;
                        <a href="superuser_manage_users.php?index=s" class="small">s</a>&nbsp;
                        <a href="superuser_manage_users.php?index=t" class="small">t</a>&nbsp;
                        <a href="superuser_manage_users.php?index=u" class="small">u</a>&nbsp;
                        <a href="superuser_manage_users.php?index=v" class="small">v</a>&nbsp;
                        <a href="superuser_manage_users.php?index=w" class="small">w</a>&nbsp;
                        <a href="superuser_manage_users.php?index=x" class="small">x</a>&nbsp;
                        <a href="superuser_manage_users.php?index=y" class="small">y</a>&nbsp;
                        <a href="superuser_manage_users.php?index=z" class="small">z</a>&nbsp;
                        </td></tr>
                        </table>

                        <HR>

                        <!--search fx
                        <form enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_manage_users.php"; ?>" method="get">
                        <center><table width="80%"><tr>
                        <td align=center bgcolor="#E0E2F2">
                            Search Username:&nbsp;&nbsp;<input type="text" name="username" size="30">&nbsp;&nbsp;<input type="submit" value="Query">
                            <p class="small_text">(Wildcards: '%'=match any '_'=match single)</p>
                        </td>
                        </tr></table></center>
                        </form>
                        end search fx -->

                        <?php //display users... ?>
                        <form name="userlist" onSubmit="return deleteChecked()" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_manage_users.php"; ?>" method="get">
                        <center><table width="80%" border="0">
                        <input type="hidden" name="iCur" value="<?php echo $iCur; ?>">
                        <?php
                        $bgcolor = "#DFDFDF";

                        //print the next and prev links...
                        echo "<tr><td colspan=\"2\" align=\"left\">";
                        if($bShowNav && $iCur != 0) {
                            //we have previous values...
                            echo "<a href=\"./superuser_manage_users.php?iCur=";
                            if($iCur-$iLimit < 0) echo "0"; else echo ($iCur-$iLimit);
                            echo "\" class=\"default\">previous ";
                            if($iCur-$iLimit < 0) echo $iCur; else echo "$iLimit</a>";
                        } else {
                            echo "&nbsp;";
                        }
                        echo "</td><td colspan=\"2\">";
                        if($bShowNav) echo "<center>Browse Usernames</center>";
                        echo "<center><p class=\"small_text\">click username to edit</p></center>";
                        echo "</td>";
                        if($bShowNav && ($iLimit+$iCur < $iNumSupportMembers)) {
                            echo "<td colspan=\"2\" align=\"right\"><a href=\"./superuser_manage_users.php?iCur=" . ($iCur+$iLimit) . "\" class=\"default\">next ";
                            if( $iNumSupportMembers-($iCur+$iLimit) > $iLimit) {
                                echo $iLimit . "</td>";
                            } else {
                                echo ($iNumSupportMembers-($iCur+$iLimit)) . "</td>";
                            }
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                        echo "</tr>\n";
                        //end print next and prev links

                        //print the header row...
                        echo "<tr><td bgcolor=\"#E0E2F2\">&nbsp;</td><td align=\"center\" bgcolor=\"#E0E2F2\">Username</td><td align=\"center\" bgcolor=\"#E0E2F2\">School</td><td align=\"center\" bgcolor=\"#E0E2F2\">Permission level</td><td align=\"center\" bgcolor=\"#E0E2F2\">Last IP</td><td align=\"center\" bgcolor=\"#E0E2F2\">Last Active</td></tr>\n";
                        while ($users_row=mysql_fetch_array($sqlUsers)) {
                            //get the permission_level name...
                            $level_query = "SELECT * FROM permission_levels WHERE level=" . $users_row['permission_level'];
                            $level_result = mysql_query($level_query);
                            if(!$level_result) {
                                $system_message = $system_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$level_query'<BR>";
                                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                            }
                            $level_row = mysql_fetch_array($level_result);
                            //if we have a local admin colour row red...
                            if($users_row['is_local_ipp_administrator']=='Y') { $temp_bgcolor=$bgcolor; $bgcolor="FF9999";}
                            echo "<tr>\n";
                            echo "<td bgcolor=\"#E0E2F2\"><input type=\"checkbox\" name=\"" . $users_row['egps_username'] . "\"></td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "superuser_manage_user.php?ippuserid=" . $users_row['egps_username'] . "\" class=\"default\">" . $users_row['egps_username'] . "</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $users_row['school_name'] . "</td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $level_row['level_name'];
                            if($users_row['is_local_ipp_administrator']=='Y') echo "<BR><B>(school ipp admin)<B>";
                            echo "</td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $users_row['last_ip'] . "</td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $users_row['last_active'] . "</td>\n";
                            echo "</tr>\n";
                            //if we have a local admin reset the colour to what it was
                            if($users_row['is_local_ipp_administrator']=='Y') { $bgcolor=$temp_bgcolor; }
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }
                        ?>
                        <tr><td colspan="5" align="left">
                           <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
                             </td>
                             <td>
                               <INPUT TYPE="image" NAME="delete_users" SRC="<?php echo IPP_PATH . "images/smallbutton.php?title=Delete"; ?>" border="0" value="1">
                               <?php
                               if($permission_level == 0) { //only super admins...
                                echo "<INPUT TYPE=\"image\" NAME=\"set_local_admin_users\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Set+Local+Admin" . "\" border=\"0\" value=\"1\"><INPUT TYPE=\"image\" NAME=\"unset_local_admin_users\" SRC=\"" .  IPP_PATH . "images/smallbutton.php?title=Unset+Local+Admin" . "\" border=\"0\" value=\"1\">";
                               }
                               ?>
                             </td>
                             </tr>
                            </table>
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
            <td class="shadow-center"><table border="0" width="100%"><tr><td width="60"><a href="<?php echo IPP_PATH . "main.php"; ?>"><img src="<?php echo IPP_PATH; ?>images/back-arrow-white.png" border=0></a></td><td width="60"><a href="<?php echo IPP_PATH . "main.php"; ?>"><img src="<?php echo IPP_PATH; ?>images/homebutton-white.png" border=0></a></td><td valign="bottom" align="center">Logged in as: <?php echo $_SESSION['egps_username'];?></td><td width="60"><a href="<?php echo IPP_PATH;?>"><img src="<?php echo IPP_PATH; ?>images/logout-white.png" border=0></a></td></tr></table></td>
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
