<?php

/** @file
 * @brief 	view student's school history
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * 1. Filter input, escape output
 * 2. bootstrap
 * 3. spellcheck as necessary
 * 4. navbars (student context)
 */ 
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['student_id'] || $_POST['student_id']
 *
 */

/**
 * Path for IPP required files.
 */

//$system_message = ""; need to accept message.

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/navbar.php');
require_once(IPP_PATH . 'include/create_pdf.php');
require_once(IPP_PATH . 'include/mail_functions.php');
require_once 'include/supporting_functions.php';

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

$student_id="";
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "You've entered this page without supplying a valid student id. Fatal, quitting";
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

function asc2hex ($temp) {
   $len = strlen($temp);
   for ($i=0; $i<$len; $i++) $data.=sprintf("%02x",ord(substr($temp,$i,1)));
   return $data;
}

function parse_submission() {
    //returns null on success else returns $szError
    global $content,$fileName,$fileType;

    if(!$_POST['school_name']) return "You must supply a school name<BR>";
    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if(!preg_match($regexp,$_POST['start_date'])) return "Start date must be in YYYY-MM-DD format<BR>";
    if(!$_POST['end_date']) return "You cannot have a null end date. If you are trying to add a current school within the district please use the 'move' student form rather than the 'Add an out of district school' form<BR>";
    if(!preg_match($regexp,$_POST['end_date'])) return "Start date must be in YYYY-MM-DD format<BR>";

    return NULL;
}

//check if we are moving this student...
if($_POST['move_out_of_district']) {
  $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
  if(!preg_match($regexp,$_POST['move_out_of_district_start_date'])) return "Start date must be in YYYY-MM-DD format<BR>";
  else {
    $update_query="UPDATE school_history SET end_date='" . mysql_real_escape_string($_POST['move_out_of_district_start_date']) . "' WHERE student_id=$student_id AND end_date IS NULL";
    $update_result=mysql_query($update_query);
    if(!$update_result) {
       $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message= $system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
       $system_message = $system_message . "Student moved to IPP Archives<BR>";
    }
  }


}

//check if we are moving this student...
if($_POST['move_to_school']) {
  $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
  if(!preg_match($regexp,$_POST['move_start_date'])) return "Start date must be in YYYY-MM-DD format<BR>";
  else {
    //get this students accommodations
    $accommodation_query="SELECT * FROM accomodation where student_id=$student_id ORDER BY end_date ASC";
    $accommodation_result = mysql_query($accommodation_query);
    if(!$accommodation_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$accommodation_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
       $accommodations="";
       while($accommodation_row = mysql_fetch_array($accommodation_result)) {
           if($accommodation_row['subject'])
              $accommodations .= "Subject=" . $accommodation_row['subject'];
           $accommodations .= " (from " . $accommodation_row['start_date'] . " to ";
           if($accommodation_row['end_date'])
             $accommodations .=  $accommodation_row['end_date'];
           else
              $accommodations .= date("Y-m-d");
           $accommodations .= "): " . $accommodation_row['accomodation'] . "\n\n";
       }
       $school_query="SELECT * FROM school WHERE school_code=" . mysql_real_escape_string($_POST['school_code']);
       $school_result=mysql_query($school_query);
       if(!$school_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        } else {
           $school_row=mysql_fetch_array($school_result);
           //we need to set any current schools to innactive...
           $update_query="UPDATE school_history SET end_date='" . mysql_real_escape_string($_POST['move_start_date']) . "' WHERE student_id=$student_id AND end_date IS NULL";
           $update_result = mysql_query($update_query);
           if(!$update_result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
             $system_message= $system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             //just carry on...
           }
           $insert_query="INSERT INTO school_history (student_id,start_date,end_date,school_code,school_name,school_address,ipp_present,accommodations) VALUES (" . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_POST['move_start_date']) . "',NULL,'" . mysql_real_escape_string($_POST['school_code']) . "','" . $school_row['school_name'] . "','" . $school_row['school_address'] . "','Y','$accommodations')";
           $insert_result=mysql_query($insert_query);
           if(!$insert_result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
             $system_message= $system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
             //we need to notify the school based ipp administrator
             $ipp_admin_query="SELECT * FROM support_member WHERE school_code=" . mysql_real_escape_string($_POST['school_code']) . " and is_local_ipp_administrator='Y'";
             $ipp_admin_result=mysql_query($ipp_admin_query);
             if(!$ipp_admin_result) {
               $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$ipp_admin_query'<BR>";
               $system_message= $system_message . $error_message;
               IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
               if(mysql_num_rows($ipp_admin_result) <= 0) {
                 $system_message = $system_message . "There doesn't appear to be a school based IPP administrator for this school. The student has been moved but there was nobody at the receiving school notified (You might want to phone and let them know).<BR>";
               } else {
                 while($ipp_admin_row=mysql_fetch_array($ipp_admin_result)) {
                  mail_notification($ipp_admin_row['egps_username'],
"This email has been sent to you to notify you that " . $student_row['first_name'] . " " . $student_row['last_name'] . "'s IPP has been moved to your school by " . username_to_common($_SESSION['egps_username']) . ". Please contact them for more information.

You should update the supervisor information and add the appropriate support members for your school to this students IPP (and remove anybody who should no longer be a support member)."
);
                  $system_message = $system_message . $ipp_admin_row['egps_username'] . " ";
                 }
                 $system_message .= " received an emailed notification that this student's IPP was forwarded to their school<BR>";
               }
             }
             //take a snapshot...
             $pdf=create_pdf($student_id);

             //we add the entry.
             $insert_query = "INSERT INTO snapshot(student_id,date,file,filename) VALUES (" . mysql_real_escape_string($student_id) . ",NOW(),'" . mysql_real_escape_string($pdf->Output("ignored",'S')) . "','IPP-" . $student_row['first_name'] . " " . $student_row['last_name'] . " " . date("F-d-Y") . ".pdf')";
             $insert_result = mysql_query($insert_query);
             if(!$insert_result) {
               $error_message = "Snapshot not taken because the database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . substr($insert_query,0,100) . "[truncated]'<BR>";
               $system_message= $system_message . $error_message;
               IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
               $system_message = $system_message . "<BR>A current snapshot was successfully taken of the IPP<BR>";
             }

             unset($_POST['move_start_date']);
           }
        }
    }
  }
}

//check if we are modifying a student...
if($_POST['add_school_history']) {
  $retval=parse_submission();
  if($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
    //we add the entry.
    $insert_query = "INSERT INTO school_history (student_id,school_name,school_address,grades,start_date,end_date,accommodations,ipp_present) VALUES (" . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_POST['school_name']) . "',";
     if($_POST['school_address']) $insert_query .= "'" . mysql_real_escape_string($_POST['school_address']) . "',";
     else $insert_query .= "NULL,";
     if($_POST['grades']) $insert_query .= "'" . mysql_real_escape_string($_POST['grades']) . "',";
     else $insert_query .= "NULL,";
     $insert_query = $insert_query . "'" . mysql_real_escape_string($_POST['start_date']) . "','" . mysql_real_escape_string($_POST['end_date']) . "',";
     if($_POST['accommodations']) $insert_query .= "'" . $_POST['accommodations'] . "',";
     else $insert_query .= "NULL,";
     if($_POST['ipp_present']) $insert_query .= "'" . $_POST['ipp_present'] . "')";
     else $insert_query .= "'?')";
     $insert_result = mysql_query($insert_query);
     if(!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
        //clear some fields
        unset($_POST['school_name']);
        unset($_POST['start_date']);
        unset($_POST['end_date']);
        unset($_POST['school_address']);
        unset($_POST['accommodations']);
        unset($_POST['ipp_present']);
        unset($_POST['grades']);
     }
  }
}

//check if we are deleting some entries...
if($_GET['delete_x'] && $permission_level <= $IPP_MIN_DELETE_SCHOOL_HISTORY && $have_write_permission ) {
    $delete_query = "DELETE FROM school_history WHERE ";
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



//get the coordination of services for this student...
$history_query="SELECT * FROM school_history WHERE student_id=$student_id ORDER BY end_date ASC";

$history_result = mysql_query($history_query);
if(!$history_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$history_query'<BR>";
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
$enum_options_type = mysql_enum_values("school_history","ipp_present");

$school_query="SELECT * FROM school WHERE 1=1";
$school_result=mysql_query($school_query);

if(!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
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
print_html5_primer();
print_bootstrap_head();
?> 

    
    
    
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
 <?php 
 print_student_navbar($student_row['first_name'] . " " . $student_row['last_name']);
 print_jumbotron_with_page_name("School History", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission);
  ?>
  <div class="container">
  <div class="row">
  
  <div class="col-md-3">
  
  <ul class="nav nav-pills">
  <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
      <?php echo "Manage " . $student_row['first_name'] . " " . $student_row['last_name'] . "'s Schools"; ?> <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
      <li><a href="#within">Move Student within this District</a></li>
      <li><a href="#out">Move Student out of District</a></li>
      <li><a href="#out">Graduate Student</a></li>
      <li><a href="#add">Add Out-of-District School to History</a></li>
      <li><a href="#history">Browse Student's School History</a></li>
      
      </ul>
  </li>
</ul>

</div>
  
   <div class="col-md-9">
  
        <table width="80%" class="shadow" border="0" cellspacing="0" cellpadding="0" align="center">  
        <tr>
          <td class="shadow-topLeft"></td>
            <td class="shadow-top"></td>
            <td class="shadow-topRight"></td>
        </tr>
        <tr>
            <td class="shadow-left"></td>
            <td class="shadow-center" valign="top">
                <table class="frame" width=80% align=center border="0">
                   <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"100%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        
                        <!-- BEGIN move -->
                        <center>
                        <form name="add_history" enctype="multipart/form-data" action="<?php echo IPP_PATH . "school_history.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="100%">
                        <tr>
                          <td colspan="3">
                          <h2><a name="move">Move student within the district</a></h2>
                           <input type="hidden" name="move_to_school" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td bgcolor="#E0E2F2" class="row_default">School Name:</td><td bgcolor="#E0E2F2" class="row_default">
                            <SELECT name="school_code" tabindex="1">
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
                            </td>
                            <td valign="center" align="center" bgcolor="#E0E2F2" rowspan="2" class="row_default"><input type="submit" tabindex="3" name="move" value="move"></td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Move Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" tabindex="2" name="move_start_date" value="<?php if(isset($_POST['move_start_date'])) echo $_POST['move_start_date']; else echo date("Y-m-d");?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.move_start_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END move -->
                        <BR>
                        <!-- BEGIN move out of district-->
                        <center>
                        <form name="add_history" enctype="multipart/form-data" action="<?php echo IPP_PATH . "school_history.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="100%">
                        <tr>
                          <td colspan="3">
                          <h2><a name="out">Move out of district/graduate student</a></h2>
                           <input type="hidden" name="move_out_of_district" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>

                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Move Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" tabindex="4" name="move_out_of_district_start_date" value="<?php if(isset($_POST['move_out_of_district_start_date'])) echo $_POST['move_out_of_district_start_date']; else echo date("Y-m-d");?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.move_start_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                           <td valign="center" align="center" bgcolor="#E0E2F2" rowspan="1" class="row_default"><input type="submit" tabindex="5" name="move" value="move"></td>
                        </tr>
                        <tr>
                        <td bgcolor="#E0E2F2" class="row_default" colspan="3">
                        <center>*This will move this student to the IPP Archives</center>
                        </td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END move out of district-->
                        <BR>
                        <!-- BEGIN add new entry -->
                        <center>
                        <form name="add_history" enctype="multipart/form-data" action="<?php echo IPP_PATH . "school_history.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="100%">
                        <tr>
                          <td colspan="3">
                          <h2><a name="add">Add an out of district school to the history</a></h2>
                           <input type="hidden" name="add_school_history" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td bgcolor="#E0E2F2" class="row_default">School Name:</td><td bgcolor="#E0E2F2" class="row_default">
                            <input type="text" tabindex="6" name="school_name" size="30" maxsize="255" value="<?php echo $_POST['school_name']; ?>" onkeypress="return autocomplete(this,event,popuplist)"> &nbsp;<img src="<?php echo IPP_PATH . "images/choosericon.png"; ?>" height="17" width="17" border=0 onClick="popUpChooser(this,document.all.school_name)" >
                            </td>
                            <!--td valign="center" align="center" bgcolor="#E0E2F2" rowspan="6" class="row_default"><input type="submit" tabindex="6" name="add" value="add"></td-->
                        </tr>
                        <tr>
                           <td valign="center" bgcolor="#E0E2F2" class="row_default">School Address (optional):</td><td bgcolor="#E0E2F2" class="row_default"><textarea spellcheck="true" name="school_address" tabindex="7" cols="30" rows="3" wrap="soft"><?php echo $_POST['school_address']; ?></textarea></td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Start Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" tabindex="8" name="start_date" value="<?php if(isset($_POST['start_date'])) echo $_POST['start_date']; else echo date("Y-m-d");?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.start_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">End Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" tabindex="9" name="end_date" value="<?php echo $_POST['end_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.end_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Program Plan Present?:</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                             <?php
                                  $tabindex=10;
                                  foreach($enum_options_type as $i => $value) {
                                      echo "<input type=\"radio\" tabindex=\"$tabindex\" name=\"ipp_present\" value=\"$value\"";
                                      if($value == $_POST['ipp_present']) echo " checked";
                                      echo ">$value&nbsp;";
                                      $tabindex++;
                                   }
                             ?>
                           </td>
                        </tr>
                         <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Grades:</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" size="10" maxsize="254" tabindex="<?php echo $tabindex; $tabindex++; ?>" name="grades" value="<?php if(isset($_POST['grades'])) echo $_POST['grades']; ?>">
                           </td>
                        </tr>
                        <tr>
                           <td valign="center" bgcolor="#E0E2F2" class="row_default">Accommodations (optional):</td><td bgcolor="#E0E2F2" class="row_default"><textarea spellcheck="true" name="accommodations" tabindex="<?php echo $tabindex; ?>" cols="30" rows="3" wrap="soft"><?php echo $_POST['accommodations']; ?></textarea></td>
                        </tr>
                        <tr>
                           <td valign="center" align="center" bgcolor="#E0E2F2" colspan="2" class="row_default"><center><input type="submit" tabindex="<?php $tabindex++;echo $tabindex; ?>" name="add" value="add"></center></td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END add new entry -->

                        <!-- BEGIN history table -->
                        <form name="schoolhistorylist" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "school_history.php"; ?>" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                        <center><table width="100%" border="0" cellpadding="0" cellspacing="1">
                        <tr><td colspan="7"><h2><a name="history">School History</a> <small>(click fields to edit)</small></h2></td></tr>
                        <?php
                        $bgcolor = "#DFDFDF";

                        //print the header row...
                        echo "<tr><td bgcolor=\"#E0E2F2\">&nbsp;</td><td bgcolor=\"#E0E2F2\">uid</td><td align=\"center\" bgcolor=\"#E0E2F2\">School</td><td align=\"center\" bgcolor=\"#E0E2F2\">Address</td><td align=\"center\" bgcolor=\"#E0E2F2\">Dates (from-to)</td><td align=\"center\" bgcolor=\"#E0E2F2\">Grades</td><td align=\"center\" bgcolor=\"#E0E2F2\">IPP</td><td align=\"center\" bgcolor=\"#E0E2F2\">Accommodations</td></tr>\n";
                        while ($history_row=mysql_fetch_array($history_result)) { //current...
                            echo "<tr>\n";
                            echo "<td bgcolor=\"#E0E2F2\"><input type=\"checkbox\" name=\"" .$history_row['uid'] . "\"></td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" .$history_row['uid'] . "</td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "edit_school_history.php?uid=" .$history_row['uid'] . "\" class=\"editable_text\">" .$history_row['school_name']  ."</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "edit_school_history.php?uid=" .$history_row['uid'] . "\" class=\"editable_text\">" .$history_row['school_address']  ."</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "edit_school_history.php?uid=" .$history_row['uid'] . "\" class=\"editable_text\">" .$history_row['start_date'] . "-";
                            if($history_row['end_date'] != "") echo $history_row['end_date'];
                            else echo "current";
                            echo "</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><center><a href=\"" . IPP_PATH . "edit_school_history.php?uid=" .$history_row['uid'] . "\" class=\"editable_text\">" .$history_row['grades'] . "</a></center></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><center><a href=\"" . IPP_PATH . "edit_school_history.php?uid=" .$history_row['uid'] . "\" class=\"editable_text\">" .$history_row['ipp_present'] . "</a></center></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "edit_school_history.php?uid=" .$history_row['uid'] . "\" class=\"editable_text\">" . preg_replace('/\n/','<BR>',$history_row['accommodations']) . "</a></td>\n";
                            //echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><center>"; if($coord_row['report_in_file'] =="") echo "-none"; else echo "<a href=\"javascript: openDoc('" . IPP_PATH . "get_attached.php?table=coordination_of_services&uid=" .$history_row['uid'] ."&student_id=" . $student_id ."','_doc')"  . "\">File</a>"; echo "</center></td>\n";
                            echo "</tr>\n";
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }
                        ?>
                        <tr>
                          <td colspan="7" align="left">
                             <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected**:
                             </td>
                             <td>
                             <?php
                                //if we have permissions also allow delete and set all.
                                if($permission_level <= $IPP_MIN_DELETE_SCHOOL_HISTORY && $have_write_permission) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"1\">";
                                }
                             ?>
                             </td>
                             </tr>
                             </table>
                          </td>
                        </tr>
                        <?php
                          if($permission_level <= $IPP_MIN_DELETE_SCHOOL_HISTORY && $have_write_permission) {
                             echo "<tr><td colspan=\"7\"><h3><strong>**Note:</strong> Deleting the current school will move this student's Program Plan to the Archives as s/he will no longer have an active school.</h3></td></tr>";
                          }
                        ?>
                        </table></center>
                        </form>
                        <!-- end history table -->

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
        <footer><?php print_complete_footer(); ?></footer>
        </div></div></div>
        <?php print_bootstrap_js(); ?>
    </BODY>
</HTML>
