<?php
/**
 * @BRIEF 	history of student's previous schools
 * 
 */
 
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody

/**
 * edit_school_history.php
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 * All rights reserved
 *
 * Created: March 18, 2006
 * By: M. Nielsen
 * Modified:
 *
 */

/*   INPUTS: $_GET['student_id'] || $_POST['student_id']
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

$uid="";
if(isset($_GET['uid'])) $uid= $_GET['uid'];
if(isset($_POST['uid'])) $uid = $_POST['uid'];

//get the coordination of services for this student...
$history_query="SELECT * FROM school_history WHERE uid=$uid";

$history_result = mysql_query($history_query);
if(!$history_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$history_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
   $history_row=mysql_fetch_array($history_result);
}

$student_id=$history_row['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "Unable to determine student_id from school history table. Fatal, quitting";
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

$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}

function parse_submission() {
    //returns null on success else returns $szError
    global $history_row;
    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if(!preg_match($regexp,$_POST['start_date'])) return "Start Date must be in YYYY-MM-DD format<BR>";
    if($_POST['end_date'] != "" && !preg_match($regexp,$_POST['end_date'])) return "End Date must be in YYYY-MM-DD format<BR>";
    if(!$_POST['school_name']) return "School Name cannot be blank<BR>";
    if($_POST['end_date'] == "" && $history_row['school_code']=="") return "You cannot leave end date blank on a school that is not within our district<BR>";
    return NULL;
}
//check if we are moving this student...
if(isset($_POST['update_school_history'])) {
  $retval=parse_submission();
  if($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
           if($_POST['end_date']=="") $end_date = "NULL";
           else $end_date = "'" . mysql_real_escape_string($_POST['end_date']) . "'";
           if($history_row['end_date'] != "" && $_POST['end_date'] == "") {
              //we need to set any other currently enrolled end date
              //for this student to end now() ...student can only
              //be enrolled in a single school at a time. so much for Keep it simple stupid.
              $end_query="UPDATE school_history SET end_date=NOW() WHERE student_id=$student_id AND end_date IS NULL";
              $end_result=mysql_query($end_query);
              if(!$end_result) {
                 $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$end_query'<BR>";
                 $system_message=$system_message . $error_message;
                 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
              }
           }
           $update_query="UPDATE school_history SET start_date='" . mysql_real_escape_string($_POST['start_date']) . "', end_date=$end_date,school_name='" . mysql_real_escape_string($_POST['school_name']) . "', school_address='" . mysql_real_escape_string($_POST['school_address']) . "', ipp_present='" . mysql_real_escape_string($_POST['ipp_present']) . "',accommodations='" . mysql_real_escape_string($_POST['accommodations']) . "'";
           if($_POST['grades']=="")
              $update_query .= ",grades=NULL";
           else
              $update_query .= ",grades='" . mysql_real_escape_string($_POST['grades']) . "'";
           $update_query .= " WHERE uid=$uid LIMIT 1";
           $update_result=mysql_query($update_query);
           if(!$update_result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
             $system_message= $system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
             if($history_row['end_date'] == "" && $_POST['end_date'] != "") $system_message .= "Student IPP has been moved to the IPP Archives<BR>";
             //redirect...
             header("Location: " . IPP_PATH . "school_history.php?MESSAGE=$system_message&student_id=" . $student_id);
           }

  }
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
$enum_options_type = mysql_enum_values("school_history","ipp_present");

/*************************** popup chooser support function ******************/
    function createJavaScript($dataSource,$arrayName='rows'){
      // validate variable name
      if(!is_string($arrayName)){
        $system_message = $system_message . "Error in popup chooser support function name supplied not a valid string  (" . __FILE__ . ":" . __LINE__ . ")";
        return FALSE;
      }

    // initialize JavaScript string
      $javascript='<!--Begin popup array--><script>var '.$arrayName.'=[];';

    // check if $dataSource is a file or a result set
      if(is_file($dataSource)){
       
        // read data from file
        $row=file($dataSource);

        // build JavaScript array
        for($i=0;$i<count($row);$i++){
          $javascript.=$arrayName.'['.$i.']="'.trim($row[$i]).'";';
        }
      }

      // read data from result set
      else{

        // check if we have a valid result set
        if(!$numRows=mysql_num_rows($dataSource)){
          return('Invalid result set parameter');
        }
        for($i=0;$i<$numRows;$i++){
          // build JavaScript array from result set
          $javascript.=$arrayName.'['.$i.']="';
          $tempOutput='';
          //output only the first column
          $row=mysql_fetch_array($dataSource);

          $tempOutput.=$row[0].' ';

          $javascript.=trim($tempOutput).'";';
        }
      }
      $javascript.='</script><!--End popup array-->'."\n";

      // return JavaScript code
      return $javascript;
    }

    function echoJSServicesArray() {
        global $system_message;
        $coordlist_query="SELECT DISTINCT `school_name`, COUNT(`school_name`) AS `count` FROM school_history GROUP BY `school_name` ORDER BY `count` DESC LIMIT 200";
        $coordlist_result = mysql_query($coordlist_query);
        if(!$coordlist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$coordlist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        } else {
            //call the function to create the javascript array...
            echo createJavaScript($coordlist_result,"popuplist");
        }
    }
/************************ end popup chooser support funtion  ******************/

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
    <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
    <script language="javascript" src="<?php echo IPP_PATH . "include/autocomplete.js"; ?>"></script>
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

      function warnArchive() {
          alert("Setting this end date will move this student to the IPP archives\nDo this only if the student has moved outside the district.\n(Use the move feature on the school history page to move schools within the district)"); return false;
      }

      function setOthersEnded() {
         var szConfirmMessage = "Setting end date blank will set the currently enrolled schools\nto end effective today. (student can only be enrolled in one school)";
         if(document.all.end_date.value == "") {
           if(confirm(szConfirmMessage))
              return true;
          else
              return false;
         }
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
    </SCRIPT>
    <?php
       //output the javascript array for the chooser popup
       echoJSServicesArray();
    ?>
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
                    <center><?php navbar("school_history.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">- IPP Edit School History-<BR>(<?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?>)</p></center></td></tr></table></center>
                        <BR>

                        <!-- BEGIN edit history entry -->
                        <center>
                        <form name="edit_history" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_school_history.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\""; else if($history_row['school_code'] !="" && $history_row['end_date'] != "") echo "onSubmit=\"return setOthersEnded();\"";?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">Edit School History</p>
                           <input type="hidden" name="update_school_history" value="1">
                           <input type="hidden" name="uid" value="<?php echo $uid; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td bgcolor="#E0E2F2" class="row_default">School Name:</td><td bgcolor="#E0E2F2" class="row_default">
                            <input type="text" tabindex="1" name="school_name" <?php if($history_row['school_code'] != "") echo "disabled"; ?> size="30" maxsize="255" value="<?php echo $history_row['school_name']; ?>" onkeypress="return autocomplete(this,event,popuplist)"> &nbsp;<img src="<?php echo IPP_PATH . "images/choosericon.png"; ?>" height="17" width="17" border=0 <?php if($history_row['school_code'] == "") { echo "onclick=\"popUpChooser(this,document.all.school_name);\""; } ?> >
                            <?php
                              if($history_row['school_code'] != "") {
                                echo "<input type=\"hidden\" name=\"school_name\" value=\"" . $history_row['school_name'] . "\">";
                              }
                            ?>
                            </td>
                            <td valign="center" align="center" bgcolor="#E0E2F2" rowspan="7" class="row_default"><input type="submit" tabindex="6" name="update" value="update"></td>
                        </tr>
                        <tr>
                           <td valign="center" bgcolor="#E0E2F2" class="row_default">School Address (optional):</td><td bgcolor="#E0E2F2" class="row_default"><textarea spellcheck="true" name="school_address" tabindex="2" cols="30" rows="5" wrap="soft"><?php echo $history_row['school_address']; ?></textarea></td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Start Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" tabindex="3" name="start_date" value="<?php echo $history_row['start_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.start_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">End Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input <?php if($history_row['end_date']=="") echo "onclick=\"warnArchive();\""; ?> type="text" tabindex="4" name="end_date" value="<?php echo $history_row['end_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="<?php if($history_row['end_date']=="") echo "warnArchive();";?>popUpCalendar(this, document.all.end_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">IPP Present?:</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                             <?php
                                  $tabindex=5;
                                  foreach($enum_options_type as $i => $value) {
                                      echo "<input type=\"radio\" name=\"ipp_present\" tabindex=\"$tabindex\" value=\"$value\"";
                                      if($value == $history_row['ipp_present']) echo " checked";
                                      echo ">$value&nbsp;";
                                      $tabindex++;
                                   }
                             ?>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Grades:</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" size="10" maxsize="254" tabindex="<?php echo $tabindex; $tabindex++; ?>" name="grades" value="<?php echo $history_row['grades']; ?>">
                           </td>
                        </tr>
                        <tr>
                           <td valign="center" bgcolor="#E0E2F2" class="row_default">Accommodations (optional):</td><td bgcolor="#E0E2F2" class="row_default"><textarea spellcheck="true" name="accommodations" tabindex="<?php echo $tabindex; ?>" cols="30" rows="5" wrap="soft"><?php echo $history_row['accommodations']; ?></textarea></td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END edit history entry -->

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
             <?php navbar("school_history.php?student_id=$student_id"); ?>
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
