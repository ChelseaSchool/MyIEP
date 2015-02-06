<?php

/** @file
 * @brief 	coding of student learning
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		Filter input
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 60; //TA (teaching assistant)



/*   INPUTS: $_GET['student_id']
 *
 */


//blank the variable so no bad guys can change the value
$system_message = "";
// we have to come back to this
$IPP_CODINGS = array("No Code", "Code 40","Code 50","Code 80", "ESL");   //no code is special case

/**
 * Path for IPP required files.
 */
 
define('IPP_PATH','./');

/* MyIEP required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/navbar.php';

 //don't cache this page!
header('Pragma: no-cache');

/**
 * If you're not logged in, redirect to index & exit this page'
 * 
 */
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

// clear student id because - security 
$student_id="";
//get student from either GET or POST arrays
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

/**
 * If we got a here without a student id, exit the page
 */
if($student_id=="") {
   //we shouldn't be here without a username.
   echo "You've entered this page without supplying a valid user name. Fatal, quitting";
   exit();
}

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

//check if we are updating this coding...we already have permission if we are here.
if(isset($_GET['modify_coding']) && $have_write_permission ) {
    //check the date field is valid...
    if($_GET['date']) {
        //check that date is the correct pattern...
        $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
        if(!preg_match($regexp,$_GET['date'])) $system_message = $system_message .  "Date must be in YYYY-MM-DD format<BR>";
    }
    //check to see if we are already coded this way...
    $check_query = "SELECT * FROM coding WHERE end_date IS NULL AND code='" . mysql_real_escape_string($_GET['code']) . "' AND student_id=" . mysql_real_escape_string($student_id);
    if($_GET['code'] == "") $system_message = $system_message . "You must supply a code<BR>";
    $check_result = mysql_query($check_query);
    if(!$check_result) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$check_query'<BR>";
        $system_message=$system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    if(mysql_num_rows($check_result) > 0) {
        $system_message = $system_message . "Nothing to update, student already coded '" . $_GET['code'] . "'<BR>";
    } else {
      //update existing codings...set to end NOW().
      //can only have 1 coding per student.
      if($_GET['date']) {
          $update_query = "UPDATE coding SET end_date='" . mysql_real_escape_string($_GET['date']) . "' WHERE student_id=" . mysql_real_escape_string($student_id) . " AND end_date IS NULL";
      } else {
          $update_query = "UPDATE coding SET end_date=now() WHERE student_id=" . mysql_real_escape_string($student_id) . " AND end_date IS NULL";
      }
      if(!$system_message) {
         $update_result = mysql_query($update_query);
      } else { $update_result = FALSE; }
      if(!$update_result && !$system_message) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
        $system_message=$system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
      } else {
        if($_GET['code'] == "No Code") {
           //special case just do nothing...
        } else {
          if($_GET['date']) {
              $modify_query = "INSERT INTO coding (student_id,code,start_date,end_date) VALUES (" . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_GET['code']) . "','" . mysql_real_escape_string($_GET['date']) . "',NULL)";
          } else {
              $modify_query = "INSERT INTO coding (student_id,code,start_date,end_date) VALUES (" . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_GET['code']) . "',NOW(),NULL)";
          }
          if(!$system_message) {
            //echo $modify_query . "<BR>";
            $modify_result = mysql_query($modify_query);
            if(!$modify_result) {
              $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$modify_query'<BR>";
              $system_message=$system_message . $error_message;
              IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            } else {
              //ok, clear the fields...
              unset($_GET['date']);
              unset($_GET['code']);
            }
          }
        }
      }
    }
}

//check if we are deleting some peeps...
if(isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_STUDENT_CODING_PERMISSION && $have_write_permission ) {
    $delete_query = "DELETE FROM coding WHERE ";
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

//check this students existing coding...
$code_query = "SELECT * FROM coding WHERE student_id =" . mysql_real_escape_string($student_id) . " AND end_date IS NULL";
$code_result = mysql_query ($code_query);
if(!$code_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$code_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
$code_row= mysql_fetch_array($code_result);

$code_history_query = "SELECT * FROM coding WHERE student_id=" . mysql_real_escape_string($student_id) . " ORDER BY start_date DESC";
$code_history_result = mysql_query ($code_history_query);
if(!$code_history_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$code_history_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//get the valid codes...
$valid_code_query="SELECT * FROM valid_coding WHERE 1";
$valid_code_result = mysql_query ($valid_code_query);
if(!$valid_code_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$valid_code_query'<BR>";
    $system_message=$system_message . $error_message;
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
    
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_history=";
          var szConfirmMessage = "Are you sure you want to delete history:\n";
          var count = 0;
          form=document.ipphistorylist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + "ID #" + form.elements[x].name + "\n";
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
                    <center><?php navbar("student_view.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">-Change Student Coding-</p></center></td></tr></table></center>
                        <BR>

                        <center>
                        <form name="changeCode" enctype="multipart/form-data" action="<?php echo IPP_PATH . "coding.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="2">
                          <p class="info_text">Edit and click 'Update Coding'.</p>
                           <input type="hidden" name="modify_coding" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" class="row_default">Code</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <select name="code">
                            <?php
                            //if($code_row['code'] == "")  {
                               echo "<option selected value=\"\">Choose...</option>";
                            //}
                            while($valid_code_row = mysql_fetch_array($valid_code_result)) {
                            //foreach($IPP_CODINGS as $index => $value) {
                              //if($code_row['code'] == $valid_code_row['code_number']) {
                              //  echo "<option selected value=\"" . $valid_code_row['code_number'] . "\">" . $valid_code_row['code_number'] . '-' . $valid_code_row['code_text'] . "</option>";
                              //} else {
                                echo "<option value=\"" . $valid_code_row['code_number'] . "\">" . $valid_code_row['code_number'] . '-' . $valid_code_row['code_text'] .  "</option>";
                              //}
                            }
                            ?>
                            </select>
                            </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Start Date: (YYYY-MM-DD)<BR>(blank for now)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" name="date" value="<?php if(isset($_GET['date'])) echo $_GET['date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2"><input type="submit" value="Update Coding"></td>
                        </tr>
                        </table>
                        </form>
                        </center>

                        <!-- BEGIN ipp history table -->
                        <form name="ipphistorylist" onSubmit="return deleteChecked()" enctype="multipart/form-data" action="<?php echo IPP_PATH . "coding.php"; ?>" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                        <center><table width="80%" border="0" cellpadding="0" cellspacing="0">

                        <?php
                        $bgcolor = "#DFDFDF";
                        //print the title...
                        echo "<tr><td colspan=\"5\"><p class=\"info_text\">Coding History</p></td></tr>";

                        //print the header row...
                        echo "<tr><td bgcolor=\"#E0E2F2\">&nbsp;</td><td align=\"center\" bgcolor=\"#E0E2F2\">ID</td><td align=\"center\" bgcolor=\"#E0E2F2\">Code</td><td align=\"center\" bgcolor=\"#E0E2F2\">Start Date</td><td align=\"center\" bgcolor=\"#E0E2F2\">End Date</td></tr>\n";
                        while ($code_history_row=mysql_fetch_array($code_history_result)) {
                            echo "<tr>\n";
                            echo "<td bgcolor=\"#E0E2F2\" class=\"row_default\"><input type=\"checkbox\" name=\"" . $code_history_row['uid'] . "\"></td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $code_history_row['uid'] . "</td>\n";
                            echo "<td bgcolor=\"$bgcolor\"><center><a href=\"" . IPP_PATH . "edit_coding.php?uid=" . $code_history_row['uid'] . "\" class=\"editable_text\">" . $code_history_row['code'] . "</a></center></td>\n";
                            echo "<td bgcolor=\"$bgcolor\"><a href=\"" . IPP_PATH . "edit_coding.php?uid=" . $code_history_row['uid'] . "\" class=\"editable_text\">" . $code_history_row['start_date'] . "</a></td>\n";
                            if($code_history_row['end_date'] == "" )
                              echo "<td bgcolor=\"$bgcolor\"><a href=\"" . IPP_PATH . "edit_coding.php?uid=" . $code_history_row['uid'] . "\" class=\"editable_text\">-Ongoing-</a></td>\n";
                            else
                              echo "<td bgcolor=\"$bgcolor\"><a href=\"" . IPP_PATH . "edit_coding.php?uid=" . $code_history_row['uid'] . "\" class=\"editable_text\">" . $code_history_row['end_date'] . "</a></td>\n";
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
                                //if we have all permissions also allow delete and set all...we know we have 'write'+ already
                                if($permission_level <= $IPP_MIN_DELETE_STUDENT_CODING_PERMISSION && $have_write_permission) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"DELETE\">";
                                }
                             ?>
                             </td>
                             </tr>
                             </table>
                          </td>
                        </tr>
                        </table></center>
                        </form>
                        <!-- end ipp history table -->

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
            <?php navbar("student_view.php?student_id=$student_id"); ?>
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
