<?php

/** @file
 * @brief 	copy an IEP without demographics
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 50;



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

$student_id="";
if(isset($_GET['student_id'])) $student_id= mysql_real_escape_string($_GET['student_id']);
if(isset($_POST['student_id'])) $student_id = mysql_real_escape_string($_POST['student_id']);

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "Unable to determine student id from uid value. Fatal, quitting";
   exit();
}

$our_permission = getStudentPermission($student_id);
if(!($our_permission == "READ" || $our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL")) {
    //we don't have any permission to this IPP...
    $system_message = $system_message . "You do not have the sufficient permissions to duplicate this program plan (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
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

//************** validated past here SESSION ACTIVE****************

//set the get/put variables for the back button and exit fx...
$szBackGetVars="";
if(isset($_GET['szBackGetVars']))$szBackGetVars = $_GET['szBackGetVars']; 
if(isset($_POST['szBackGetVars']))$szBackGetVars = $_POST['szBackGetVars'];

$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}

function parse_submission() {
    if(!$_POST['first_name']) return "You must supply a first name<BR>";
    if(!$_POST['last_name']) return "You must supply a last name<BR>";
    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if(!preg_match($regexp,$_POST['birthday'])) return "Birthday must be in YYYY-MM-DD format<BR>";
    if(!preg_match($regexp,$_POST['at_school_since'])) return "At School Since must be in YYYY-MM-DD format<BR>";
    //if(!$_POST['prov_ed_num']) return "You must supply a Provincial Education Number<BR>";
    //if(!$_POST['ab_ed_code']) return "You must supply an Alberta Education Coding Value<BR>";

    //check duplicate prov ed number...
    if(!connectIPPDB()) {
          $error_message = $error_message;  //just to remember we need this
          $system_message = $error_message;
          IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     }
     if($_POST['prov_ed_num'] != "") {
       $duplicate_query = "SELECT * FROM student WHERE prov_ed_num='" . mysql_real_escape_string($_POST['prov_ed_num']) ."'";
       $duplicate_result= mysql_query($duplicate_query);
       if(mysql_num_rows($duplicate_result) > 0) {$duplicate_row = mysql_fetch_array($duplicate_result);return "Duplicate Provincial Education Number (name:" . $duplicate_row['first_name'] . " " . $duplicate_row['last_name'] ."),<BR>This student probably already exists in the database<BR>";}
     }
     //$duplicate_query = "SELECT * FROM student WHERE ab_ed_code='" . mysql_real_escape_string($_POST['ab_ed_code']) ."'";
     //$duplicate_result= mysql_query($duplicate_query);
     //if(mysql_num_rows($duplicate_result) > 0) {$duplicate_row = mysql_fetch_array($duplicate_result);return "Duplicate Alberta Education Code Number (name:" . $duplicate_row['first_name'] . " " . $duplicate_row['last_name'] ."),<BR>This student probably already exists in the database<BR>"; }
    

    return NULL;
}

//check if we are adding a student...
if(isset($_POST['add_student'])) {

     if(!connectIPPDB()) {
          $error_message = $error_message;  //just to remember we need this
          $system_message = $error_message;
          IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     }

     //do some error checking on data submission...
     $retval = parse_submission();
     if($retval != NULL) {
         $system_message = $system_message . $retval;
     } else {
       $add_query="INSERT INTO student (first_name,last_name,birthday,prov_ed_num,current_grade,gender) values ('" . mysql_real_escape_string($_POST['first_name']) . "','" .  mysql_real_escape_string($_POST['last_name']) ."','" . mysql_real_escape_string($_POST['birthday']) . "','" .  mysql_real_escape_string($_POST['prov_ed_num']) . "','" . mysql_real_escape_string($_POST['current_grade']) . "','" . mysql_real_escape_string($_POST['gender']) . "')";
       $add_result=mysql_query($add_query);
       if(!$add_result) {
           $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
           $system_message=$system_message . $error_message;
           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
       } else {
           //get the school information to create a history for this student...
           $school_history_query="SELECT * FROM school WHERE school_code='" . mysql_real_escape_string($_POST['school_code']) . "'";
           $school_history_result=mysql_query($school_history_query);
           $school_history_row="";
           if(!$school_history_result) {
               $error_message = $error_message . "You might need to enter or change some of the school history information for this student. The system  wasunable to automatically determine this information because the database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
               $system_message=$system_message . $error_message;
               IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
              $school_history_row=mysql_fetch_array($school_history_result);
           }
           //add the school history to the database...
           //just to be safe we update any existing data for this student_id to have
           //todays end date...
           $new_student_id = mysql_insert_id();
           $history_update_query="UPDATE school_history SET end_date=NOW() where student_id=$new_student_id";
           $history_update_result=mysql_query($history_update_query); //ignore returned errors.

           //add a new school history...choosen school start today, end NULL...
           $history_insert_query = "INSERT INTO school_history (start_date,end_date,school_code,student_id,school_name,school_address,ipp_present) VALUES ('" . mysql_real_escape_string($_POST['at_school_since']) . "',NULL,'" . $school_history_row['school_code'] ."'," . mysql_real_escape_string($new_student_id) . ",'" . $school_history_row['school_name']  . "','" . $school_history_row['school_address']  . "','Y')";
           $history_insert_result = mysql_query($history_insert_query); //ignore returned errors. What we don't know can't hurt us.

           //add this user as a support member for this IPP...
           //$support_list_query = "INSERT INTO support_list (egps_username,student_id,permission) VALUES ('" . mysql_real_escape_string($_SESSION['egps_username']) . "'," . mysql_real_escape_string($new_student_id) . ",'ASSIGN')"; //give self assign
           //$support_list_result = mysql_query($support_list_query); //ignore returned errors...won't cause major problem.

           //now we copy the data as requested!
           if(isset($_POST['supervisor'])) {
             //copy supervisor from duplication student...
             $supervisor_query = "SELECT * FROM supervisor WHERE student_id=$student_id";
             $supervisor_result= mysql_query($supervisor_query); //get
             if(!$supervisor_result) {
                $error_message = $error_message . "Unable to duplicate supervisors (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$supervisor_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
                while($row=mysql_fetch_array($supervisor_result)) {
                    $update_query="INSERT INTO supervisor ( egps_username, position, start_date, end_date, student_id) values ('" . $row['egps_username'] . "','" . $row['position'] . "','" . $row['start_date'] . "',";
                    if($row['end_date']=="") $update_query .= "NULL"; else $update_query .="'" . $row['end_date'] . "'";
                    $update_query .= "," . $new_student_id . ")";
                    $update_result = mysql_query($update_query);
                    if(!$update_result) {
                        $error_message = $error_message . "Unable to duplicate supervisors (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                        $system_message=$system_message . $error_message;
                        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                    }
                }
             }
           }

           if(isset($_POST['support'])) {
             $support_query = "SELECT * FROM support_list WHERE student_id=$student_id";
             $support_result= mysql_query($support_query); //get
             if(!$support_result) {
                $error_message = $error_message . "Unable to duplicate support members (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$supervisor_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
                while($row=mysql_fetch_array($support_result)) {
                   $update_query = "INSERT INTO support_list (egps_username,student_id,permission,support_area) VALUES ('" . $row['egps_username'] . "'," . $new_student_id . ",'" . $row['permission'] . "','" . $row['support_area'] . "')";
                   $update_result = mysql_query($update_query);
                   if(!$update_result) {
                        $error_message = $error_message . "Unable to duplicate support members (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                        $system_message=$system_message . $error_message;
                        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                   }
                   //we need to set this person to assign...
                   $update_query = "UPDATE support_list SET permission='ASSIGN' WHERE  student_id=" . $new_student_id . " AND egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "'";
                   $update_result = mysql_query($update_query);
                   if(!$update_result) {
                        $error_message = $error_message . "Unable to set assign permissions on duplicated program plan (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                        $system_message=$system_message . $error_message;
                        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                   }
                }
             }
           }

           if(isset($_POST['current_code'])) {
             $code_query = "SELECT * FROM coding WHERE student_id=$student_id and end_date IS NULL LIMIT 1";  //get current code only
             $code_result= mysql_query($code_query); //get
             if(!$code_result) {
                $error_message = $error_message . "Unable to duplicate coding (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$code_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
               while($row=mysql_fetch_array($code_result)) {
                  $update_query = "INSERT INTO coding (student_id,code,start_date,end_date) values (" . $new_student_id . "," . $row['code'] . ",NOW(),NULL)";
                  $update_result = mysql_query($update_query);
                  if(!$update_result) {
                        $error_message = $error_message . "Unable to duplicate code (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                        $system_message=$system_message . $error_message;
                        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                  }
               }
             }
           }

           if(isset($_POST['transition'])) {
             $transition_query = "SELECT * FROM transition_plan WHERE student_id=$student_id";  //get current code only
             $transition_result= mysql_query($transition_query); //get
             if(!$transition_result) {
                $error_message = $error_message . "Unable to duplicate transition plans (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$transition_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
                  while($row=mysql_fetch_array($transition_result)) {
                  $update_query = "INSERT INTO transition_plan (student_id,plan,date) values (" . $new_student_id . ",'" . $row['plan'] . "','" . $row['date'] . "')";
                  $update_result = mysql_query($update_query);
                  if(!$update_result) {
                        $error_message = $error_message . "Unable to duplicate transition plans (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                        $system_message=$system_message . $error_message;
                        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                  }
               }
             }
           }

           if(isset($_POST['asst_tech'])) {
             $asst_query = "SELECT * FROM assistive_technology WHERE student_id=$student_id";  //get current code only
             $asst_result= mysql_query($asst_query); //get
             if(!$asst_result) {
                $error_message = $error_message . "Unable to duplicate assistive technology (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$asst_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
                  while($row=mysql_fetch_array($asst_result)) {
                  $update_query = "INSERT INTO assistive_technology (student_id,technology) values (" . $new_student_id . ",'"  . $row['technology'] . "')";
                  $update_result = mysql_query($update_query);
                  if(!$update_result) {
                        $error_message = $error_message . "Unable to duplicate assistive technology (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                        $system_message=$system_message . $error_message;
                        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                  }
               }

             }
           }

           if(isset($_POST['accommodations'])) {
             $accommodation_query = "SELECT * FROM accomodation WHERE student_id=$student_id";  //get current code only
             $accommodation_result= mysql_query($accommodation_query); //get
             if(!$accommodation_result) {
                $error_message = $error_message . "Unable to duplicate accommodations (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$accommodation_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
                while($row=mysql_fetch_array($accommodation_result)) {
                  $update_query = "INSERT INTO accomodation(accomodation, subject, student_id,start_date,end_date) values ('" . $row['accomodation'] . "','" . $row['subject'] . "'," . $new_student_id . ",'"  . $row['start_date'] . "',";
                  if($row['end_date'] == "") $update_query .= "NULL"; else $update_query .= "'" . $row['end_date'] . "'";
                  $update_query .= ")";
                  $update_result = mysql_query($update_query);
                  if(!$update_result) {
                        $error_message = $error_message . "Unable to duplicate accommodations (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                        $system_message=$system_message . $error_message;
                        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                  }
               }

             }
           }

           if(isset($_POST['goals_objectives'])) {
             //ugly, we need to nest this and cross our fingers...
             $goal_query = "SELECT * FROM long_term_goal WHERE student_id=$student_id"; //get the goals
             $goal_result= mysql_query($goal_query); //get
             if(!$goal_result) {
                $error_message = $error_message . "Unable to duplicate goals (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$goal_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
             } else {
                while($row=mysql_fetch_array($goal_result)) {
                  $update_query = "INSERT INTO long_term_goal (student_id,review_date,goal,is_complete,area) values (" . $new_student_id . ",";
                  if($row['review_date'] == "") $update_query .= "NULL"; else $update_query .= "'" . $row['review_date'] . "'";
                  $update_query .= ",'" . $row['goal'] . "','" . $row['is_complete'] . "','" . $row['area'] . "')";
                  $update_result = mysql_query($update_query);
                  if(!$update_result) {
                     $error_message = $error_message . "Unable to duplicate some goals (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                     $system_message=$system_message . $error_message;
                     IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                  } else {
                    //goal added okay so we need to also tie the objectives to this goal!
                    $goal_id = $row['goal_id'];
                    $objective_query = "SELECT * FROM short_term_objective WHERE goal_id=$goal_id"; //get the sto's
                    $objective_result = mysql_query($objective_query);
                    if(!$objective_result) {
                      $error_message = $error_message . "Unable to duplicate objective (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objective_query'<BR>";
                      $system_message=$system_message . $error_message;
                      IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                    } else {
                      //get the last insert id...
                      $new_goal_id = mysql_insert_id();
                      while($row=mysql_fetch_array($objective_result)) {
                        $update_query = "INSERT INTO short_term_objective (goal_id,description,achieved,review_date,results_and_recommendations,strategies,assessment_procedure) VALUES ($new_goal_id,'" . $row['description'] . "','" . $row['achieved'] . "',";
                        if($row['review_date'] == "") $update_query .= "NULL,"; else $update_query .= "'" . $row['review_date'] . "',";
                        if($row['results_and_recommendations'] == "") $update_query .= "NULL,"; else $update_query .= "'" . $row['results_and_recommendations'] . "',";
                        if($row['strategies'] == "") $update_query .= "NULL,"; else $update_query .= "'" . $row['strategies'] . "',";
                        if($row['assessment_procedure'] == "") $update_query .= "NULL)"; else $update_query .= "'" . $row['assessment_procedure'] . "')";
                        $update_result = mysql_query($update_query);
                        if(!$update_result) {
                           $error_message = $error_message . "Unable to duplicate some objectives (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
                           $system_message=$system_message . $error_message;
                           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                        }
                      }
                    }
                  }
                }
             }
           }

           //successful, maybe, so direct to...
           if(!$system_message) {
             header("Location: manage_student.php");
             exit();
           } else {
             $system_message = "The student has been partially copied. Some errors have occured:<BR>" . $system_message;
           }
       }
     }


}

if(!connectUserDB()) {
        $error_message = $error_message;  //just to remember we need this
        $system_message = $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}


//find all of the available schools..
if(!connectIPPDB()) {
   $error_message = $error_message;  //just to remember we need this
   $system_message = $error_message;
   IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$school_query="SELECT * FROM school WHERE 1=1";
$school_result=mysql_query($school_query);

if(!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
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

                        <center><table><tr><td><center><p class="header">-Duplicate <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?>-</p></center></td></tr></table></center>
                        <BR>

                        <center>
                        <form name="addName" enctype="multipart/form-data" action="<?php echo IPP_PATH . "duplicate.php"; ?>" method="post">
                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                        <tr>
                          <td colspan="2">
                          <p class="info_text">You must change the demographic information.</p>
                          <input type="hidden" name="add_student" value="1">
                          <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>

                        <tr>
                          <td bgcolor="#E0E2F2" align="left">First Name:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" name="first_name" size="30" maxsize="125" value="<?php if(isset($_POST['first_name'])) echo $_POST['first_name'];?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Last Name:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" name="last_name" size="30" maxsize="125" value="<?php if(isset($_POST['last_name']))  echo $_POST['last_name']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Birthday: (YYYY-MM-DD)&nbsp;</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" name="birthday" value="<?php if(isset($_POST['birthday'])) echo $_POST['birthday']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.birthday, 'yyyy-m-dd', 0, 0)">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">School:</td>
                          <td bgcolor="#E0E2F2">
                              <SELECT name="school_code">
                              <?php
                                  while($school_row=mysql_fetch_array($school_result)) {
                                      if(isset($_POST['school_code']) && $_POST['school_code'] == $school_row['school_code']) {
                                          echo "<option value=\"" . $school_row['school_code'] . "\" selected>" .  $school_row['school_name'] . "\n";
                                      } else {
                                          echo "<option value=\"" . $school_row['school_code'] . "\">" .  $school_row['school_name'] . "\n";
                                      }
                                  }
                              ?>
                              </SELECT>
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">At School Since: (YYYY-MM-DD)&nbsp;</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" name="at_school_since" value="<?php if(isset($_POST['at_school_since'])) echo $_POST['at_school_since']; else echo date("Y-m-d"); ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.at_school_since, 'yyyy-m-dd', 0, 0)">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Current Grade:</td>
                          <td bgcolor="#E0E2F2">
                            <SELECT name="current_grade">
                                 <OPTION value="0" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "0") echo "selected"; ?>>K or Pre-K
                                 <OPTION value="1" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "1") echo "selected"; ?>>1
                                 <OPTION value="2" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "2") echo "selected"; ?>>2
                                 <OPTION value="3" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "3") echo "selected"; ?>>3
                                 <OPTION value="4" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "4") echo "selected"; ?>>4
                                 <OPTION value="5" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "5") echo "selected"; ?>>5
                                 <OPTION value="6" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "6") echo "selected"; ?>>6
                                 <OPTION value="7" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "7") echo "selected"; ?>>7
                                 <OPTION value="8" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "8") echo "selected"; ?>>8
                                 <OPTION value="9" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "9") echo "selected"; ?>>9
                                 <OPTION value="10" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "10") echo "selected"; ?>>10
                                 <OPTION value="11" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "11") echo "selected"; ?>>11
                                 <OPTION value="12" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "12") echo "selected"; ?>>12
                                 <OPTION value="13" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "13") echo "selected"; ?>>13
                            </SELECT>
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Gender</td>
                          <td bgcolor="#E0E2F2">
                            <SELECT name="gender">
                                <option value="M">Male
                                <option value="F">Female
                            </SELECT>
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Student Number:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" size="30" maxsize="60" name="prov_ed_num" value="<?php if(isset($_POST['prov_ed_num'])) echo $_POST['prov_ed_num'];?>">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;</td>
                        </tr>

                        <tr>
                          <td colspan="2">
                          <p class="info_text">Choose the information to copy</p>
                          </td>
                        </tr>

                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Supervisor</td>
                          <td bgcolor="#E0E2F2">
                            <input type="checkbox" name="supervisor" <?php if(isset($_POST['supervisor'])) echo  "checked"; else if(isset($_POST['add_student'])) echo "unchecked"; else echo "checked"; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Support Members</td>
                          <td bgcolor="#E0E2F2">
                            <input type="checkbox" name="support" <?php if(isset($_POST['support'])) echo "checked"; else if(isset($_POST['add_student'])) echo "unchecked"; else echo "checked"; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Current Coding</td>
                          <td bgcolor="#E0E2F2">
                            <input type="checkbox" name="current_code" <?php if(isset($_POST['current_code'])) echo "checked"; else if(isset($_POST['add_student'])) echo "unchecked"; else echo "checked"; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Transition Plan</td>
                          <td bgcolor="#E0E2F2">
                            <input type="checkbox" name="transition" <?php if(isset($_POST['transition'])) echo "checked"; else if(isset($_POST['add_student'])) echo "unchecked"; else echo "checked"; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Assistive Technology</td>
                          <td bgcolor="#E0E2F2">
                            <input type="checkbox" name="asst_tech" <?php if(isset($_POST['asst_tech'])) echo "checked"; else if(isset($_POST['add_student'])) echo "unchecked"; else echo "checked"; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Accommodations</td>
                          <td bgcolor="#E0E2F2">
                            <input type="checkbox" name="accommodations" <?php if(isset($_POST['accommodations'])) echo "checked"; else if(isset($_POST['add_student'])) echo "unchecked"; else echo "checked"; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Goals/Objectives</td>
                          <td bgcolor="#E0E2F2">
                            <input type="checkbox" name="goals_objectives" <?php if(isset($_POST['goals_objectives'])) echo "checked"; else if(isset($_POST['add_student'])) echo "unchecked"; else echo "checked"; ?>">
                          </td>
                        </tr>

                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;&nbsp;<input type="submit" value="Duplicate"></td>
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
            <td class="shadow-center"><table border="0" width="100%"><tr><td><a href="
            <?php
                echo IPP_PATH . "manage_student.php?$szBackGetVars";
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
