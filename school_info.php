<?php

/** @file
 * @brief 	view school specific information / add school
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * 1. filter input, escape output
 * 2. confirm this page's role
 * 3. bootstrap and nav
 * 4. spellcheck?
 * 5. Can we draw from information here to customize the application?
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0; //only super administrator



/*   INPUTS: $_GET['student_id']
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

//************** validated past here ****************

function parse_submission() {
    //returns null on success else returns $szError
    global $content,$fileName,$fileType;
    $regexp='/^[0-9]*$/';
    if(!preg_match($regexp, $_POST['school_code'])) return "You must supply a valid school code (numbers only)<BR>";
    if(!$_POST['school_name']) return "You must supply a school name<BR>";
    if(!$_POST['school_address']) return "You must supply a school address<BR>";
    if(!$_POST['school_colour']) $_POST['school_colour'] = "#FFFFFF";

    //check that colour is the correct pattern...
    $regexp = '/^#[0-9a-fA-F]{6}$/';
    if(!preg_match($regexp,$_POST['school_colour'])) return "Colour must be in '#RRGGBB' format<BR>";

    return NULL;
}

//check if we are modifying a student...
if(isset($_POST['add_school'])) {
  $retval=parse_submission();
  if($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
    //we add the entry.
    $red=substr($_POST['school_colour'],1,2);
    $green=substr($_POST['school_colour'],3,2);
    $blue=substr($_POST['school_colour'],5,2);
    $insert_query = "INSERT INTO school (school_code,school_name,school_address,red,green,blue) VALUES ('" . mysql_real_escape_string($_POST['school_code']) . "','" . mysql_real_escape_string($_POST['school_name']) . "','" . mysql_real_escape_string($_POST['school_address']) . "','$red','$green','$blue')";
    $insert_result = mysql_query($insert_query);
     if(!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . $insert_query . "<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
        //clear some fields
        unset($_POST['school_code']);
        unset($_POST['school_name']);
        unset($_POST['school_address']);
        unset($_POST['school_colour']);
     }
  }
}

//check if we are deleting some entries...
if(isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_SCHOOL) {
    $delete_query = "DELETE FROM school WHERE ";
    foreach($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "school_code=" . $key . " or ";
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


//get the medication for this student...
$school_query="SELECT * FROM school WHERE 1 ORDER by school_name ASC";
$school_result = mysql_query($school_query);
if(!$school_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
    
    <script language="javascript" src="<?php echo IPP_PATH . "include/picker.js"; ?>"></script>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "schoollist=";
          var szConfirmMessage = "Are you sure you want to delete the following:\n";
          var count = 0;
          form=document.schoollist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + form.elements[x].name + ",";
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
                    <center><?php navbar("main.php"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">-Manage Schools-</p></center></td></tr></table></center>
                        <BR>

                        <!-- BEGIN add school -->
                        <center>
                        <form name="add_school" enctype="multipart/form-data" action="<?php echo IPP_PATH . "school_info.php"; ?>" method="post">
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">Edit and click 'Add'.</p>
                           <input type="hidden" name="add_school" value="1">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" bgcolor="#E0E2F2" class="row_default">School Code:</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <input type="text" tabindex="1" name="school_code" value="<?php if(isset($_POST['school_code']))  echo $_POST['school_code']; ?>" size="30" maxsize="254">
                            </td>
                            <td valign="center" align="center" bgcolor="#E0E2F2" rowspan="4" class="row_default"><input type="submit" tabindex="5" value="add" value="add"></td>
                        </tr>
                        <tr>
                            <td valign="bottom" bgcolor="#E0E2F2" class="row_default">School Name:</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <input type="text" tabindex="2" name="school_name" value="<?php if(isset($_POST['school_name'])) echo $_POST['school_name']; ?>" size="30" maxsize="254">
                            </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">School Address:</td>
                           <td bgcolor="#E0E2F2" class="row_default"><textarea spellcheck="true" name="school_address" tabindex="3" cols="30" rows="3" wrap="soft"><?php if(isset($_POST['school_address'])) echo $_POST['school_address']; ?></textarea></td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">School Colour:</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <INPUT TYPE="TEXT" NAME="school_colour" MAXLENGTH="7" tabindex="4" SIZE="7" value="<?php if(isset($_POST['school_colour']))echo $_POST['school_colour']; ?>">
                               <a href="javascript:TCP.popup(document.forms['add_school'].elements['school_colour'], 1)"><img width="15" height="13" border="0" alt="Click Here to Pick the color" src="<?php echo IPP_PATH . "images/colour_sel.gif"; ?>"></a>
                           </td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END add school -->

                        <!-- BEGIN school table -->
                        <form name="schoollist" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "school_info.php"; ?>" method="get">
                        <center><table width="80%" border="0" cellpadding="0" cellspacing="1">
                        <tr><td colspan="6">Schools (click to edit):</td></tr>
                        <?php
                        $bgcolor = "#DFDFDF";

                        //print the header row...
                        echo "<tr><td bgcolor=\"#E0E2F2\">&nbsp;</td><td align=\"center\" bgcolor=\"#E0E2F2\">Code</td><td align=\"center\" bgcolor=\"#E0E2F2\">School Name</td><td align=\"center\" bgcolor=\"#E0E2F2\">School Address</td><td align=\"center\" bgcolor=\"#E0E2F2\">School Colour</td></tr>\n";
                        while ($school_row=mysql_fetch_array($school_result)) { //current...
                            echo "<tr>\n";
                            echo "<td bgcolor=\"#E0E2F2\"><input type=\"checkbox\" name=\"" . $school_row['school_code'] . "\"></td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "edit_school.php?school_code=" . $school_row['school_code'] . "\" class=\"editable_text\">" . $school_row['school_code']  ."</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "edit_school.php?school_code=" . $school_row['school_code'] . "\" class=\"editable_text\">" . $school_row['school_name']  ."</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "edit_school.php?school_code=" . $school_row['school_code'] . "\" class=\"editable_text\">" . $school_row['school_address'] . "</a></td>\n";
                            echo "<td bgcolor=\"" . $school_row['red'] . $school_row['green'] . $school_row['blue']  . "\" class=\"row_default\"><center><a href=\"" . IPP_PATH . "edit_school.php?school_code=" . $school_row['school_code'] . "\" class=\"editable_text\">#". $school_row['red'] . $school_row['green'] . $school_row['blue']  . "</a></center></td>\n";
                            echo "</tr>\n";
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }
                        ?>
                        <tr>
                          <td colspan="6" align="left">
                             <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
                             </td>
                             <td>
                             <?php
                                //if we have permissions also allow delete.
                                if($permission_level <= $IPP_MIN_DELETE_SCHOOL) {
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
                        <!-- end school table -->

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
              <?php navbar("main.php"); ?></td>
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
