<?php
/** @file
 * @brief 	add notes by guardian
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
$MINIMUM_AUTHORIZATION_LEVEL = 100;    //everybody (do checks within document)

/**
 * guardian_notes.php -- add/edit/delete guardian information
 * INPUTS: guardian_id,student_id as _GET.
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 * All rights reserved
 *
 * Created: July 14, 2005
 * By: M. Nielsen
 * Modified:  July 21,2005
 *
 */

/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message= "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/navbar.php');

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
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}


//check permissions if necessary...
$guardian_query = "SELECT * FROM guardians LEFT JOIN guardian ON guardians.guardian_id=guardian.guardian_id where guardians.guardian_id=" . $_GET['guardian_id'];
$guardian_result= mysql_query($guardian_query);
if(!$guardian_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$guardian_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

if(mysql_num_rows($guardian_result) <= 0) {
   $system_message = $system_message . "No such guardian found: ID# " . $_GET['guardian_id'] . "<BR>";
   IPP_LOG($system_message,$_SESSION['egps_username'],'WARNING');
}

$have_permission = false;
while($guardian_row=mysql_fetch_array($guardian_result)) {
    $our_permission = getStudentPermission($guardian_row['student_id']);
    if($our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission != "ALL") {
        //we don't have permission...
        //do nothing.
    } else {
        $have_permission = true;
    }
}

if(!$have_permission) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

$student_id=$_GET['student_id'];


//check if we are deleting...
if(isset($_GET['delete_x'])) {
    $delete_query = "DELETE FROM guardian_note WHERE ";
    foreach($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "uid=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //echo $delete_query . "<-><BR>";
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
}

//check if we are adding...
if(isset($_GET['add_note'])) {
    $add_query = "INSERT INTO guardian_note (guardian_id,note,date,priority_note) VALUES (" . $_GET['guardian_id'] . ",'" . mysql_real_escape_string($_GET['note']) . "',NOW(),";
    if(isset($_GET['priority_note']) && $_GET['priority_note'] == '1') {
       $add_query = $add_query . "'Y')";
    } else {
       $add_query = $add_query . "'N')";
    }
    $add_result = mysql_query($add_query);
    if(!$add_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
        //add a log entry that this user has added the note UID.
    }
}

$note_query="SELECT * FROM guardian_note WHERE guardian_id=" . $_GET['guardian_id'] . " ORDER BY priority_note,date ASC";
$note_result=mysql_query($note_query);
if(!$note_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$note_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}


//reset the mysql table pointer
mysql_data_seek($guardian_result,0);

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
     <SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to delete:\n";
          var count = 0;
          form=document.notelist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + " (UID #" + form.elements[x].name + ")\n";
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
                    <tr><td>
                    <center><?php navbar("guardian_view.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center>
                        <table width="80%" cellspacing="0" cellpadding="0"><tr><td><center><p class="header">- IPP Guardian Notes -</p></center></td></tr><tr><td><center><p class="bold_text">
                        <?php

                            $target_row=mysql_fetch_array($guardian_result);
                            echo "Guardian: " . $target_row['last_name'] . "," . $target_row['first_name'] . "<BR>";
                        ?></p></center></td></tr>
                        </table>
                        </center>

                        <!-- BEGIN Add Note -->
                        <form name="addnote" enctype="multipart/form-data" action="<?php echo IPP_PATH . "guardian_notes.php"; ?>" method="get">
                        <center>
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <input type="hidden" name="guardian_id" value="<?php echo $target_row['guardian_id']; ?>">
                        <input type="hidden" name="student_id" value="<?php echo $_GET['student_id']; ?>">
                        <input type="hidden" name="add_note" value="1">
                        <tr><td colspan="2" class="wrap_top">Add Note</td></tr>
                        <tr><td class="wrap_left" bgcolor="#E0E2F2" width="100%">
                            <textarea tabindex="1" name="note" cols="40" rows="3" wrap="soft"></textarea><BR>
                            <center><input type="checkbox" tabindex="2" name="priority_note" value="1">Priority Flag</center>
                        </td><td bgcolor="#FFFFFF" class="wrap_right" rowspan="1" width="100"><center><INPUT TYPE="image" tabindex="3" SRC="<?php echo IPP_PATH . "images/smallbutton.php?title=Add"; ?>" border="0" name="add" value="1"></center></td></tr>
                        <tr><td class="wrap_bottom_left" bgcolor="#E0E2F2" width="100%">&nbsp;</td><td class="wrap_bottom_right" bgcolor="#FFFFFF" width="100">&nbsp;</td></tr>
                        </table>
                        </center>
                        </form>
                        <!-- END Add Note -->

                        <BR><BR>

                        <!--BEGIN notes table -->
                        <form name="notelist" onSubmit="return deleteChecked()" enctype="multipart/form-data" action="<?php echo IPP_PATH . "guardian_notes.php"; ?>" method="get">
                        <center><table width="80%" border="0">
                        <input type="hidden" name="guardian_id" value="<?php echo $target_row['guardian_id']; ?>">
                        <input type="hidden" name="student_id" value="<?php echo $_GET['student_id']; ?>">
                        <?php
                        $bgcolor = "#DFDFDF";
                        echo "<tr><td bgcolor=\"#E0E2F2\">&nbsp;</td><td bgcolor=\"#E0E2F2\">UID</td><td align=\"center\" bgcolor=\"#E0E2F2\">Priority?</td><td align=\"center\" bgcolor=\"#E0E2F2\">Note</td><td align=\"center\" bgcolor=\"#E0E2F2\">Date</td></tr>\n";
                        while ($note_row=mysql_fetch_array($note_result)) {
                            echo "<tr>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><input type=\"checkbox\" name=\"" . $note_row['uid'] . "\" value=\"" . $note_row['uid'] . "\"></td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $note_row['uid'] . "</td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $note_row['priority_note'] . "<p></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $note_row['note'] . "</td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><p class=\"small_text\">" . $note_row['date'] . "<p></td>\n";
                            echo "</tr>\n";
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }
                        if($permission_level <= $IPP_MIN_DELETE_GUARDIAN_NOTES)
                            echo "<tr><td colspan=\"5\" align=\"left\"><img src=\"" . IPP_PATH . "images/table_arrow.png\">&nbsp;With Selected: <INPUT TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" name=\"delete\" value=\"1\"></td></tr>\n";
                        
                        ?>
                        </table></center>
                        </form>
                        <!--END notes table -->


                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center">
            <?php navbar("guardian_view.php?student_id=$student_id"); ?>
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
