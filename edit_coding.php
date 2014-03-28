<?php
/**
 * @BRIEF 	edit student learning code
 * 
 */
 
 
/* 
 *Notes 
 * 1. coding the students legally recorded disability
 * 2. we don't use
 * 3. mysql_real_escape_string() is used. Find replacement/
 */
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 50; //Teaching Staff



/*   INPUTS: $_GET['uid'],$_POST['uid']
 *
 */

/**
 * Path for IPP required files.
 */

$system_message = "";

//$IPP_CODINGS = array("No Code", "Code 40","Code 50","Code 80", "ESL");   //no code is special case

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

$uid="";
if(isset($_GET['uid'])) $uid= mysql_real_escape_string($_GET['uid']);
if(isset($_POST['uid'])) $uid = mysql_real_escape_string($_POST['uid']);

//check this students existing coding...
$code_row="";
$code_query = "SELECT * FROM coding WHERE uid=$uid";
$code_result = mysql_query ($code_query);
if(!$code_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$code_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
   $code_row= mysql_fetch_array($code_result);
}


$student_id=$code_row['student_id'];
if($student_id=="") {
   //we shouldn't be here without a username.
   echo "Cannot get student id from coding id. Fatal, quitting";
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
if(isset($_POST['modify_coding']) =="1" && $have_write_permission ) {
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
     if(!preg_match($regexp,$_POST['start_date'])) { $system_message = $system_message . "Start Date must be in YYYY-MM-DD format<BR>"; }
     else {
       if(!($_POST['end_date'] == ""  || preg_match($regexp,$_POST['end_date']))) { $system_message = $system_message . "End Date must be in YYYY-MM-DD format<BR>"; }
       else {
           $update_query = "UPDATE coding SET code='" . mysql_real_escape_string($_POST['code']) . "',start_date='" . mysql_real_escape_string($_POST['start_date']) . "'";
           if($_POST['end_date'] == "") $update_query .= ",end_date=NULL";   //set no end date.
           else $update_query .= ",end_date='" . mysql_real_escape_string($_POST['end_date']) . "'";
           $update_query .= " WHERE uid=$uid LIMIT 1";
           $update_result = mysql_query($update_query);
           if(!$update_result) {
              $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
              $system_message=$system_message . $error_message;
              IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
             //redirect
             //$system_message = $update_query . "<BR>";
             header("Location: " . IPP_PATH . "coding.php?student_id=" . $student_id);
           }
       }
    }
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
                    <center><?php navbar("coding.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">-Edit Student Code-</p></center></td></tr></table></center>
                        <BR>

                        <center>
                        <form name="changeCode" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_coding.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="2">
                          <p class="info_text">Edit and click 'Update Coding'.</p>
                           <input type="hidden" name="modify_coding" value="1">
                           <input type="hidden" name="uid" value="<?php echo $uid; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" class="row_default">Code</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <select name="code">
                            <?php
                            while($valid_code_row = mysql_fetch_array($valid_code_result)) {
                            //foreach($IPP_CODINGS as $index => $value) {
                              if($code_row['code'] == $valid_code_row['code_number']) {
                                echo "<option selected value=\"" . $valid_code_row['code_number'] . "\">" . $valid_code_row['code_number'] . '-' . $valid_code_row['code_text'] . "</option>";
                              } else {
                                echo "<option value=\"" . $valid_code_row['code_number'] . "\">" . $valid_code_row['code_number'] . '-' . $valid_code_row['code_text'] .  "</option>";
                              }
                            }
                            ?>
                            </select>
                            </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Start Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" name="start_date" value="<?php echo $code_row['start_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.start_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">End Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" name="end_date" value="<?php echo $code_row['end_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.end_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2"><input type="submit" value="Update Coding"></td>
                        </tr>
                        </table>
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
            <td class="shadow-center">
            <?php navbar("coding.php?student_id=$student_id"); ?>
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
