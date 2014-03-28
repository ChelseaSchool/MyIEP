<?php

/** @file
 * @brief 	unused page for reporting bugs
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		Filter input
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 60; //teaching assistants and up

 

/*   INPUTS: none, nada...zip.
 *
 */

/**
 * Path for IPP required files.
 */

$system_message = "";

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

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

$have_write_permission = FALSE;

//check if we are adding...
if(isset($_POST['add_bug_report'])) {
   //minimal testing of input...

       $add_query = "INSERT INTO bugs (username, status, bug, resolution,referring_page) VALUES ('" . mysql_real_escape_string($_SESSION['egps_username']) . "','Active','" . mysql_real_escape_string($_POST['description']) . "', NULL,'" . mysql_real_escape_string($_POST['referring_page']) . "')";
       $add_result = mysql_query($add_query);
       if(!$add_result) {
         $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
         $system_message=$system_message . $error_message;
         IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
       } else {
         //reset the variables...
         //unset($_POST['type']);
         unset($_POST['description']);
       }

   //$system_message = $system_message . $add_query . "<BR>";
}

//check if we are deleting some entries...
if($_GET['delete_x'] && $permission_level <= $IPP_MIN_DELETE_BUG_PERMISSION || $have_write_permission ) {
    $delete_query = "DELETE FROM bugs WHERE ";
    foreach($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "uid=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
}


//get the strengths/needs for this student...
$bug_query="SELECT * FROM bugs WHERE 1 ORDER BY status ASC, username ASC";
$bug_result = mysql_query($bug_query);
if(!$bug_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$bug_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//get enum fields for area...
function mysql_enum_values($tableName,$fieldName)
{
  $result = mysql_query("DESCRIBE $tableName");

  //then loop:
  while($row = mysql_fetch_array($result))
  {
   //# row is mysql type, in format "int(11) unsigned zerofill"
   //# or "enum('cheese','salmon')" etc.

   ereg('^([^ (]+)(\((.+)\))?([ ](.+))?$',$row['Type'],$fieldTypeSplit);
   //# split type up into array
   $ret_fieldName = $row['Field'];
   $fieldType = $fieldTypeSplit[1];// eg 'int' for integer.
   $fieldFlags = $fieldTypeSplit[5]; // eg 'binary' or 'unsigned zerofill'.
   $fieldLen = $fieldTypeSplit[3]; // eg 11, or 'cheese','salmon' for enum.

   if (($fieldType=='enum' || $fieldType=='set') && ($ret_fieldName==$fieldName) )
   {
     $fieldOptions = split("','",substr($fieldLen,1,-1));
     return $fieldOptions;
   }
  }

  //if the funciton makes it this far, then it either
  //did not find an enum/set field type, or it
  //failed to find the the fieldname, so exit FALSE!
  return FALSE;

}
$enum_options_type = mysql_enum_values("bugs","status");

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
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.strengthneedslist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + "ID #" + form.elements[x].name + ",";
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
                    <tr><td>
                    <center><?php navbar("main.php"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">- IPP Bug Tracking<BR></p></center></td></tr></table></center>
                        <BR>

                        <!-- BEGIN add bug -->
                        <center>
                        <form name="add_bug" enctype="multipart/form-data" action="<?php echo IPP_PATH . "bug_report.php"; ?>" method="post">
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">Edit and click 'Add'.</p>
                           <input type="hidden" name="add_bug_report" value="1">
                           <?php
                             $path = explode('/', $_SERVER['HTTP_REFERER']);
                             $filename=$path[count($path)-1];
                           ?>
                           <input type="hidden" name="referring_page" value="<?php echo $filename; ?>">
                          </td>
                        </tr>
                        <tr>
                           <td valign="center" width="100" bgcolor="#E0E2F2" class="row_default">Bug Description</td><td bgcolor="#E0E2F2" class="row_default"><textarea name="description" cols="40" rows="5" wrap="soft"><?php echo $_POST['description']; ?></textarea></td>
                           <td valign="center" align="center" bgcolor="#E0E2F2" rowspan="1" class="row_default"><input type="submit" name="add" value="add"></td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END add info -->

                        <!-- BEGIN info table -->
                        <form name="infolist" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "bug_report.php"; ?>" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                        <center><table width="80%" border="0" cellpadding="0" cellspacing="1">
                        <tr><td colspan="6">Background Information:</td></tr>
                        <?php
                        $bgcolor = "#DFDFDF";

                        //print the header row...
                        echo "<tr><td bgcolor=\"#E0E2F2\">&nbsp;</td><td bgcolor=\"#E0E2F2\">Name</td><td align=\"center\" bgcolor=\"#E0E2F2\">Status</td><td align=\"center\" bgcolor=\"#E0E2F2\">Description (click to edit)</td><td align=\"center\" bgcolor=\"#E0E2F2\">Status</td></tr>\n";
                        while ($bug_info_row=mysql_fetch_array($bug_result)) { //current...
                            if($bug_info_row['status']=="Active") $bgcolor="#FFCCCC";
                            if($bug_info_row['status']=="On hold") $bgcolor="#FFFFCC";
                            if($bug_info_row['status']=="Resolved") $bgcolor="#99FFCC";
                            echo "<tr>\n";
                            echo "<td bgcolor=\"#E0E2F2\"><input type=\"checkbox\" name=\"" . $bug_info_row['uid'] . "\"></td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . username_to_common($bug_info_row['username']) . "</td>";

                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">";
                            if(($_SESSION['egps_username'] == $bug_info_row['username'] && $bug_info_row['status'] != "Resolved")|| $permission_level <= $IPP_MIN_EDIT_BUG_PERMISSION) echo "<a href=\"" . IPP_PATH . "edit_bug.php?uid=" . $bug_info_row['uid'] . "\" class=\"editable_text\">";
                            echo $bug_info_row['status'];
                            if(($_SESSION['egps_username'] == $bug_info_row['username'] && $bug_info_row['status'] != "Resolved") || $permission_level <= $IPP_MIN_EDIT_BUG_PERMISSION) echo "</a>";
                            echo "</td>\n";

                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">";
                            if(($_SESSION['egps_username'] == $bug_info_row['username'] && $bug_info_row['status'] != "Resolved") || $permission_level <= $IPP_MIN_EDIT_BUG_PERMISSION) echo "<a href=\"" . IPP_PATH . "edit_bug.php?uid=" . $bug_info_row['uid'] . "\" class=\"editable_text\">";
                            echo $bug_info_row['bug'];
                            if(($_SESSION['egps_username'] == $bug_info_row['username'] && $bug_info_row['status'] != "Resolved") || $permission_level <= $IPP_MIN_EDIT_BUG_PERMISSION) echo "</a>";
                            echo "</td>\n";


                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">";
                            if(($_SESSION['egps_username'] == $bug_info_row['username'] && $bug_info_row['status'] != "Resolved") || $permission_level <= $IPP_MIN_EDIT_BUG_PERMISSION) echo "<a href=\"" . IPP_PATH . "edit_bug.php?uid=" . $bug_info_row['uid'] . "\" class=\"editable_text\">";
                            echo $bug_info_row['resolution'];
                            if(($_SESSION['egps_username'] == $bug_info_row['username'] && $bug_info_row['status'] != "Resolved") || $permission_level <= $IPP_MIN_EDIT_BUG_PERMISSION) echo "</a>";
                            echo "</td>\n";

                            echo "</tr>\n";
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }
                        ?>
                        <tr>
                          <td colspan="5" align="left">
                             <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
                             </td>
                             <td>
                             <?php
                                //if we have permissions also allow delete and set all.
                                if($permission_level <= $IPP_MIN_DELETE_BUG_PERMISSION || $have_write_permission) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"1\">";
                                }
                             ?>
                             </td>
                             </tr>
                             </table>
                          </td>
                        </tr>
                        </table></center>
                        </form>
                        <!-- end info table -->

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
            <?php navbar("main.php"); ?>
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
