<?php

/** @file
 * @brief 	add objective to goal
 * @detail
 * 
 * This page answers to "Edit" in the goal list. One can add an objective here too. The filename is misleading.
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		
 * * Filter input
 * * check datepicker (for all pages)
 * 
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody check within



/*   INPUTS:
 *           $_POST['student_id']
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
//require_once(IPP_PATH . 'include/navbar.php');
require_once(IPP_PATH . 'include/supporting_functions.php');

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

if((!isset($_GET['student_id']) || $_GET['student_id']=="") &&  (!isset($_POST['student_id']) || $_POST['student_id'] == "")) {
    //ack
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit();
} else {
   if(!isset($_POST['student_id']))
    $student_id=mysql_real_escape_string($_GET['student_id']);
   else
    $student_id=mysql_real_escape_string($_POST['student_id']);
}

$goal_id="";
if(isset($_POST['lto'])) $goal_id=mysql_real_escape_string($_POST['lto']);
if(isset($_GET['lto'])) $goal_id=mysql_real_escape_string($_GET['lto']);

$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

//see if we are adding a goal
if(isset($_POST['add_goal']) && $have_write_permission) {

    if(!isset($_POST['goal_description']) || $_POST['goal_description'] == "") {
        $system_message = $system_message . "You must supply a description of this goal<BR>";
        header("Location: add_goal_1.php?student_id=$student_id&MESSAGE=$system_message");
        exit();
    }  else {
      $description=strip_tags($_POST['goal_description']);
      //$description=eregi_replace("\r\n",' ',$description);
      //$description=eregi_replace("\r",' ',$description);
      //$description=eregi_replace("\n",' ',$description);
      $description= mysql_real_escape_string($description);
      $check_query="SELECT * FROM long_term_goal WHERE student_id=" . mysql_real_escape_string($student_id) . " AND goal='" . mysql_real_escape_string($description) . "'";
      $check_result=mysql_query($check_query);
      if(mysql_num_rows($check_result) > 0) {
          $system_message = $system_message . "This is already set as a goal for this student (you might have hit reload)<BR>";
          $check_row=mysql_fetch_array($check_result);
          $goal_id=$check_row['goal_id'];
      } else {
        //check that date is the correct pattern...
        $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
        if(!preg_match($regexp,$_POST['review_date'])) {
          $system_message = $system_message . "Date must be in YYYY-MM-DD format<BR>";
          header("Location: " . IPP_PATH . "add_goal_1.php?goal_area=" . $_POST['goal_area'] . "&review_date=" . $_POST['review_date'] . "&description=" .  $_POST['goal_description'] . "&MESSAGE=" . $system_message . "&student_id=" . $student_id);
        }
        else {
            $area="";
            if($_POST['goal_area'] == "") {
                $area = "Other";
            } else {
                //lets get the area...
                $area_query="SELECT * FROM typical_long_term_goal_category WHERE cid=" . mysql_real_escape_string($_POST['goal_area']);
                $area_result=mysql_query($area_query);
                if(!$area_result) $area="Other";
                else { $area_row=mysql_fetch_array($area_result); $area = $area_row['name'];}
            }
            $insert_goal_query="INSERT INTO long_term_goal (goal,student_id,review_date,area) VALUES ('$description',$student_id,'" . mysql_real_escape_string($_POST['review_date']) . "','" . mysql_real_escape_string($_POST['goal_area']) . "')";
            $insert_goal_result = mysql_query($insert_goal_query);
            if(!$insert_goal_result) {
                $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_goal_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                header("Location: " . IPP_PATH . "add_goal_1.php?MESSAGE=" . $system_message . "&student_id=" . $student_id);
            }  else {
                $goal_id=mysql_insert_id();
                unset($_POST['description']);
            }
        }
      }
    }
}

if($goal_id == "" && (!isset($goal_id) || $goal_id=="") ) {
   $system_message = $system_message . "An error occured: you have arrived here without a valid goal id number, perhaps you don't have permission levels necessary.<BR>";
} else {
   if($goal_id == "") { $goal_id=$goal_id; }
   //find the student owner of this objective...
   $goal_query="SELECT long_term_goal.*,short_term_objective.*,long_term_goal.review_date AS goal_review_date FROM long_term_goal LEFT JOIN short_term_objective ON long_term_goal.goal_id=short_term_objective.goal_id WHERE long_term_goal.goal_id=" . mysql_real_escape_string($goal_id);
   $goal_result=mysql_query($goal_query);
   if(!$goal_result) {
     $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$goal_query'<BR>";
     $system_message=$system_message . $error_message;
     IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   } else {
      $goal_row=mysql_fetch_array($goal_result);
      $student_id=$goal_row['student_id'];
      $goal_review_date=$goal_row['goal_review_date'];
      //recheck permissions...
   }
}

$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

//check if we are updating the goal text...
if(isset($_POST['update_goal']) && $have_write_permission) {
   if($_POST['goal_description'] == "") $system_message = $system_message . "You must supply goal text<BR>";
   else {
      $update_query="UPDATE long_term_goal SET area=";
      $update_query .= "'" . mysql_real_escape_string($_POST['goal_area']) . "'";
      $update_query .= ", review_date='" . mysql_real_escape_string($_POST['review_date']) . "',goal='" . mysql_real_escape_string($_POST['goal_description']) . "' WHERE goal_id=$goal_id LIMIT 1";
      $update_result = mysql_query($update_query);
      if(!$update_result) {
         $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
         $system_message=$system_message . $error_message;
         IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
      }
    //rerun the queries
    $goal_query="SELECT long_term_goal.*,short_term_objective.*,long_term_goal.review_date AS goal_review_date FROM long_term_goal LEFT JOIN short_term_objective ON long_term_goal.goal_id=short_term_objective.goal_id WHERE long_term_goal.goal_id=" . mysql_real_escape_string($goal_id);
    $goal_result=mysql_query($goal_query);
    if(!$goal_result) {
     $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$goal_query'<BR>";
     $system_message=$system_message . $error_message;
     IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
      $goal_row=mysql_fetch_array($goal_result);
      $student_id=$goal_row['student_id'];
      $goal_review_date=$goal_row['goal_review_date'];
    }

   }
}

//check if we are adding an objective...
if(isset($_POST['add_objective']) && $have_write_permission) {
   if($_POST['description'] == "")  {
      $system_message = $system_message . "You must supply a short term objective<BR>";
   }  else {
     //check that date is the correct pattern...
     $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
     if(!preg_match($regexp,$_POST['review_date'])) {
          $system_message = $system_message . "Date must be in YYYY-MM-DD format<BR>";
     } else {
       $insert_query = "INSERT INTO short_term_objective (goal_id,description,review_date,results_and_recommendations,strategies,assessment_procedure) values (";
       $insert_query = $insert_query . mysql_real_escape_string($goal_id) . ",";
       $insert_query = $insert_query . "'" . mysql_real_escape_string($_POST['description']) . "',";
       $insert_query = $insert_query . "'" . mysql_real_escape_string($_POST['review_date']) . "',";

       if($_POST['results_and_recommendations'] == "")
          $insert_query = $insert_query . "NULL,";
       else
          $insert_query = $insert_query . "'" . mysql_real_escape_string($_POST['results_and_recommendations']) . "',";

       if($_POST['strategies'] == "")
          $insert_query = $insert_query . "NULL,";
       else
          $insert_query = $insert_query . "'" . mysql_real_escape_string($_POST['strategies']) . "',";

       if($_POST['assessment_procedure'] == "")
          $insert_query = $insert_query . "NULL";
       else
          $insert_query = $insert_query . "'" . mysql_real_escape_string($_POST['assessment_procedure']) . "'";

       $insert_query = $insert_query . ")";

       $insert_result = mysql_query($insert_query);
       if(!$insert_result) {
         $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
         $system_message=$system_message . $error_message;
         IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
       } else {
         unset($_POST['description']);
         unset($_POST['review_date']);
         unset($_POST['results_and_recommendations']);
         unset($_POST['strategies']);
         unset($_POST['assessment_procedure']);
       }
     }
   }
}

//rerun the query...ok, lazy...should be reworked
if($goal_id == "" && (!isset($goal_id) || $goal_id=="") ) {
   $system_message = $system_message . "An error occured: you have arrived here without a valid goal id number, perhaps you don't have permission levels necessary. lto='$goal_id'<BR>";
} else {
   if($goal_id == "") { $goal_id=$goal_id; }
   //find the student owner of this objective...
   $goal_query="SELECT * FROM long_term_goal LEFT JOIN short_term_objective ON long_term_goal.goal_id=short_term_objective.goal_id WHERE long_term_goal.goal_id=" . mysql_real_escape_string($goal_id);
   $goal_result=mysql_query($goal_query);
   if(!$goal_result) {
     $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$goal_query'<BR>";
     $system_message=$system_message . $error_message;
     IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   } else {
      $goal_row=mysql_fetch_array($goal_result);
      $student_id=$goal_row['student_id'];
      //recheck permissions...
   }
}


//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

if($student_id) {
  $student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
  $student_result = mysql_query($student_query);
  if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {$student_row= mysql_fetch_array($student_result);}
}

//last thing...add an instructional note:


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

          $bad_chars = array("\n", "\r");
          $tempOutput.= mysql_real_escape_string(str_replace($bad_chars, "\\n",$row[0])) . ' ';

          $javascript.=trim($tempOutput).'";';
        }
      }
      $javascript.='</script><!--End popup array-->'."\n";

      // return JavaScript code
      return $javascript;
    }

    function echoJSServicesArray() {
        global $system_message;
        $asslist_query="SELECT DISTINCT `assessment_procedure`, COUNT(`assessment_procedure`) AS `count` FROM short_term_objective GROUP BY `assessment_procedure` ORDER BY `count` DESC LIMIT 200";
        $asslist_result = mysql_query($asslist_query);
        if(!$asslist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$asslist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        } else {
            //call the function to create the javascript array...
            if(mysql_num_rows($asslist_result)) echo createJavaScript($asslist_result,"popuplist");
        }
    }
/************************ end popup chooser support funtion  ******************/
?> 
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="About MyIEP">
    <meta name="author" content="Rik Goldman" >
    <link rel="shortcut icon" href="./assets/ico/favicon.ico">
    <TITLE><?php echo $page_title; ?></TITLE>
    <!-- Bootstrap core CSS -->
    <?php print_bootstrap_head();?>
    <?php print_datepicker_depends(); ?>
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./css/jumbotron.css" rel="stylesheet">
	<style type="text/css">body { padding-bottom: 70px; }</style>
    <!-- Bootstrap Datepicker CSS
	<link href="css/datepicker.css" rel="stylesheet">
    <script src="js/jquery-2.1.0.min.js"></script>
	 <script src="js/jquery-ui-1.10.4.custom.min.js></script>
     <script src="js/bootstrap-datepicker.js"></script>-->
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.medicationlist;
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

	<!-- <link rel="stylesheet" href="css/smoothness/jquery-ui-1.10.4.css">
	<script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>-->
	<script> 
	$(function() {
	$( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
	});
	</script>
</HEAD>
<BODY>
 <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="main.php">MyIEP</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="main.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a onclick="history.go(-1);">Back</a></li>
            <li><a href=<?php echo "ipp_pdf.php?student_id=" . $student_row['student_id'] . "&file=ipp.pdf"; ?>>Get PDF</li></a>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Records: <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?><b class="caret"></b></a>
              <ul class="dropdown-menu">
              	<li><a href="<?php echo IPP_PATH . "long_term_goal_view.php?student_id=" . $student_row['student_id']; ?>">Goals</a></li>
              	<li class="divider"></li>
              	<li><a href="<?php echo IPP_PATH . "guardian_view.php?student_id=" . $student_row['student_id'];?>">Guardians</a></li>
              	<li><a href="<?php echo IPP_PATH . "strength_need_view.php?student_id=" . $student_row['student_id'];?>">Strengths &amp; Needs</a></li>
              	<li><a href="<?php echo IPP_PATH . "coordination_of_services.php?student_id=" . $student_row['student_id'];?>">Coordination of Services</a></li>
              	<li><a href="<?php echo IPP_PATH . "achieve_level.php?student_id=" . $student_row['student_id'];?>">Achievement Level</a></li>
              	<li><a href="<?php echo IPP_PATH . "medical_info.php?student_id=" . $student_row['student_id'];?>">Medical Information</a></li>
              	<li><a href="<?php echo IPP_PATH . "medication_view.php?student_id=" . $student_row['student_id'];?>">Medication</a></li>
              	<li><a href="<?php echo IPP_PATH . "testing_to_support_code.php?student_id=" . $student_row['student_id'];?>">Support Testing</a></li>
              	<li><a href="<?php echo IPP_PATH . "background_information.php?student_id=" . $student_row['student_id'];?>">Background Information</a></li>
              	<li><a href="<?php echo IPP_PATH . "year_end_review.php?student_id=" . $student_row['student_id'];?>">Year-End Review</a></li>
              	<li><a href="<?php echo IPP_PATH . "anecdotals.php?student_id=" . $student_row['student_id'];?>">Anecdotals</a></li>
              	<li><a href="<?php echo IPP_PATH . "assistive_technology.php?student_id=" . $student_row['student_id'];?>">Assistive Techology</a></li>
              	<li><a href="<?php echo IPP_PATH . "transition_plan.php?student_id=" . $student_row['student_id'];?>">Transition Plan</a></li>
              	<li><a href="<?php echo IPP_PATH . "accomodations.php?student_id=" . $student_row['student_id'];?>">Accomodations</a></li>
              	<li><a href="<?php echo IPP_PATH . "snapshots.php?student_id=" . $student_row['student_id'];?>">Snapshots</a></li></ul>
            </ul>
             
          <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php">Logout</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./manage_student.php">Students</a></li>
                <li class="divider"></li>
                <li><a href="change_ipp_password.php">Reset Password</a></li>
                <li><a href="superuser_add_goals.php">Goals Database</a></li>
                <li><a href="./student_archive.php">Archive</a></li>
                <li><a href="./user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              </ul>
            </li>
          </ul>
         </div>
         
      </div>
    </div>   
<?php print_jumbotron_with_page_name("Revise Goal", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission);?>	
	
	
    <div class="container">
<div class="row">


<p><?php if ($system_message) { echo $system_message;} ?></p>

</div>




<!-- Left -->
<div class="col-md-4">
<h3>Edit Goal</h3>
<!-- Form Begins -->
<form spellcheck="true" name="edit_goal" enctype="multipart/form-data" action="goal_change_checker.php" method="post">
<div class=form-group>
<!-- Hidden -->
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
<input type="hidden" name="lto" value="<?php echo $goal_id; ?>">
<input type="hidden" name="update_goal" value="1">
<!--  End hidden -->
<div class="form-group">
<label>Goal Area</label>

<select required class="form-control" name="goal_area" placeholder="Choose Goal Area"  value="<?php echo $goal_row['area']; ?>">

<?php   

$area_query = "SELECT * FROM `typical_long_term_goal_category` WHERE `is_deleted` = \"N\" ORDER BY `typical_long_term_goal_category`.`name` ASC";
$area_result = mysql_query($area_query);
if(!$area_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$area_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
$area_result_row = mysql_fetch_array($area_result);
while ($area_result_row=mysql_fetch_array($area_result)) {
		if ($area_result_row['name'] != $goal_row['area']) {
    		echo "<option value=\"" . $area_result_row['name'] . "\">" . $area_result_row['name'] . "</option>\n";
    	}
    	else 
    		echo "<option selected value=\"" . $area_result_row['name'] . "\">" . $area_result_row['name'] . "</option>\n";
    		
}
//$goal=$goal_row['description'];
?>
</select> 
<label>Goal</label></p>
<textarea spellcheck="true" class="form-control" name="goal_text" spellcheck="true" cols="45" rows="3" wrap="soft"><?php echo $goal_row['goal']; ?></textarea></p>
<label>Review Date</label>
<input class="form-control datepicker" autocomplete="off" type="datepicker" id="datepicker" name="goal_review_date" data-provide="datepicker" data-date-format="yyyy-mm-dd" value="<?php echo $goal_review_date; ?>"></p>
</div>
<button class="btn btn-lg btn-success" type="submit" name="Update" value="Update">Submit Revision</button>
</form>
</div>
</div>


<!-- Right -->
<div class="col-md-4">
<!-- BEGIN add short term objective -->

<h3>Add an Objective</h3>
<form name="add_objective" enctype="multipart/form-data" action="<?php echo IPP_PATH . "add_objective_receipt.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
<div class="form-group">


<!-- Hidden -->
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
<input type="hidden" name="lto" value="<?php echo $goal_id; ?>">
<input type="hidden" name="add_objective" value="1">
<input type="hidden" name="goal" value="<?php echo $goal; ?>">
<input type="hidden" name="student_first_name" value="$student_row['first_name']">
<input type="hidden" name="student_last_name" value="$student_row['last_name']">
<!-- End Hidden -->

<div class="form-group">
<label>Objective</label>
<textarea placeholder="<?php echo $student_row['first_name'];?> will ... " required spellcheck="true" class="form-control" spellcheck="true" spellcheck="true" name="objective" tabindex="1" cols="40" rows="3" wrap="soft"><?php if(isset($_POST['objective'])) echo $_POST['objective']; ?></textarea>
<label>Review Date (YYYY-MM-DD)</label>
<input required type="datepicker" autocomplete="off" class="form-control datepicker" name="objective_review_date" data-provide="datepicker" data-date-format="yyyy-mm-dd" tabindex="2" value="<?php if(isset($_POST['review_date'])) echo $_POST['review_date']; ?>">
<label>Assessment Procedure</label>
<textarea required class="form-control" spellcheck="true" spellcheck="true" name="assessment_procedure" tabindex="3" cols="35" rows="3" wrap="soft"><?php if(isset($_POST['assessment_procedure'])) echo $_POST['assessment_procedure']; ?></textarea></p>
<label>Strategies</label>
<textarea required class="form-control" spellcheck="true" spellcheck="true" name="strategies" tabindex="4" cols="40" rows="3" wrap="soft"><?php if(isset($_POST['strategies'])) echo $_POST['strategies']; ?></textarea>
<label>Progress Review</label>
<textarea class="form-control" placeholder="Maybe not yet." spellcheck="true" spellcheck="true" name="results_and_recommendations" tabindex="5" cols="40" rows="3" wrap="soft"><?php if(isset($_POST['results_and_recommendations'])) echo $_POST['results_and_recommendations']; ?></textarea></p>
</div>
<button class="btn btn-lg btn-success" type="submit" tabindex="6" name="Add" value="Add">Add Objective</button>
</div>
</form>


        

</div> <!-- end column -->
<div class="col-md-4">
<h3>Current Objectives</h3>                 
<?php if($goal_row['description'] != "") {
	do {
    echo "<p><label>Objective</label></p>" . "<p>" . $goal_row['description'] . "</p>";
    } while ($goal_row = mysql_fetch_array($goal_result));
}
?>                             
</div><!-- end third column -->
</div> <!-- end row -->
</div>
    <?php print_complete_footer(); ?>
    <?php print_bootstrap_js()?>
    </BODY>
</HTML>
