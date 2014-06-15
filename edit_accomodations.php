<?php

/** @file
 * @brief 	modify accomodations perscribed by IEP
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
 * 1. Filter input
 * 2. find bug in php
 */ 
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['uid'], $_POST['uid']
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

$uid="";
if(isset($_GET['uid'])) $uid= mysql_real_escape_string($_GET['uid']);
if(isset($_POST['uid'])) $uid = mysql_real_escape_string($_POST['uid']);

$accomodation_row="";
$accomodation_query = "SELECT * FROM accomodation WHERE uid=" . mysql_real_escape_string($uid);
$accomodation_result = mysql_query($accomodation_query);
if(!$accomodation_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$accomodation_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
  $accomodation_row=mysql_fetch_array($accomodation_result);
}

$student_id=$accomodation_row['student_id'];
if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "Unable to retrieve student id from accmodation id. Fatal, quitting";
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
} 

else {
	$student_row= mysql_fetch_array($student_result);
}

if(isset($_POST['edit_accomodation']) && $have_write_permission)
 {

     $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
     if(!preg_match($regexp,$_POST['start_date'])) { $system_message = $system_message . "Start Date must be in YYYY-MM-DD format<BR>"; }
     else {
       if(!($_POST['end_date'] == ""  || preg_match($regexp,$_POST['end_date']))) { $system_message = $system_message . "End Date must be in YYYY-MM-DD format<BR>"; }
       else {
           $update_query = "UPDATE accomodation SET accomodation='" . mysql_real_escape_string($_POST['accomodation']) . "',start_date='" . mysql_real_escape_string($_POST['start_date']) . "',subject='" . mysql_real_escape_string($_POST['subject']) . "'";
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
             header("Location: " . IPP_PATH . "accomodations.php?student_id=" . $student_id);
           }
       }
    }
   //$system_message = $system_message . $add_query . "<BR>";
}

/******************** popup chooser support function ******************/
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
          die('Invalid result set parameter');
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
        $acclist_query="SELECT accomodation FROM typical_accomodation WHERE 1 ORDER BY accomodation ASC LIMIT 200";
        $acclist_result = mysql_query($acclist_query);
        if(!$acclist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$acclist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        } else {
            //call the function to create the javascript array...
            if(mysql_num_rows($acclist_result)) echo createJavaScript($acclist_result,"popuplist");
        }
    }
/************************ end popup chooser support funtion  ******************/

?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
   <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="About MyIEP">
    <meta name="author" content="Rik Goldman" >
    <link rel="shortcut icon" href="./assets/ico/favicon.ico">
    <TITLE><?php echo $page_title; ?></TITLE>
   <!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./css/jumbotron.css" rel="stylesheet">
	<style type="text/css">body { padding-bottom: 70px; }</style>
    
    <!-- Datepicker Depends -->
    <?php print_datepicker_depends(); ?>

    
    <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
    <script language="javascript" src="<?php echo IPP_PATH . "include/autocomplete.js"; ?>"></script>
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <?php
       //output the javascript array for the chooser popup
       echoJSServicesArray();
    ?>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "delete_supervisor=";
          var szConfirmMessage = "Are you sure you want to modify/delete program area(s):\n";
          var count = 0;
          form=document.programareahistory;
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
<!-- Example Invokation of Datepicker -->
	<!-- input type=datepicker name="review_date" id="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd"  -->
<link rel="stylesheet" href="css/smoothness/jquery-ui-1.10.4.custom.css">
<script src="js/smoothness/jquery-ui-1.10.4.custom.js"></script>
<!-- <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>-->
	

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
            <li><a href="index.php">Logout</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="help.php">Help</a></li>
            <li><a onclick="history.go(-1);">Back</a></li>
            <li><a href=<?php echo "ipp_pdf.php?student_id=" . $student_row['student_id'] . "&file=ipp.pdf"; ?>>Get PDF</a></li>
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
                <!-- <li><a href="superuser_add_goals.php">Goals Database</a></li>-->
                <li><a href="./student_archive.php">Archive</a></li>
                <li><a href="./user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              </ul>
            </li>
          </ul>
         </div>
         <!--/.nav-collapse -->
        <!--<div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form" nctype="multipart/form-data" action="jumbotron.php" method="post">
            <div class="form-group">
              <input type="text" placeholder="User Name" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control" name="PASSWORD" value="">
            </div>
            <button type="submit" value="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </div>     
<div class="jumbotron"><div class="container">     

<?php if ($system_message) echo "<p>" . $system_message . "</p>"; ?>

<h1>Edit Accomodations: <small><?php echo $student_row['first_name'] . " " . $student_row['last_name'] ?> </small></h1>
<h2>Logged in as: <small><?php echo $_SESSION['egps_username']; ?> (Permission: <?php echo $our_permission; ?>)</small></h2>



</div> <!-- Close Jumbotron -->               
</div><!-- End Container -->                 
<div class="container">
                        <!-- BEGIN edit accomodation -->
                        
<form role="form" name="edit_accomodation" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_accomodations.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\""; ?>>
<div class="form-group"> 
	<input type="hidden" name="edit_accomodation" value="1">
	<input type="hidden" name="uid" value="<?php echo $uid; ?>">
</div>
<div class="form-group">
	<label>Accommodation</label> &nbsp; <input autofocus class="form-control" type="text" tabindex="1" name="accomodation" value="<?php echo $accomodation_row['accomodation']; ?>">
</div>
<div class="form-group">
<label>Subject or Area</label> &nbsp; <input class="form-control" type="text" tabindex="2" name="subject" size="35" maxsize="255" value="<?php echo $accomodation_row['subject']; ?>">                           
</div>
<div class="form-group">
<label>Start Date (YYYY-MM-DD)</label>
<input class="form-control datepicker" type="datepicker" tabindex="3" name="start_date" data-provide="datepicker" data-date-format="yyyy-mm-dd" value="<?php echo $accomodation_row['start_date']; ?>">
</div>
<div class="form-group">
<label>End Date (YYYY-MM-DD)</label>            
<input type="datepicker" class="form-control datepicker"  name="end_date" data-provide="datepicker" data-date-format="yyyy-mm-dd" value="<?php echo $accomodation_row['end_date']; ?>">
</div>                        
<div class="form-group">
<input class="btn btn-default" type="submit" tabindex="5" name="Update" value="Update">
 </div>
</form>
 <!-- END edit accomodation -->


                        
                       
        </div>
        
        
         <!-- close container --> 
        <!-- Bootstrap core JavaScript
 ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<!-- Bootstrap core JavaScript
 ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/jquery-2.1.0.min.js"></script>
<script src="./js/bootstrap.min.js"></script>   
<script type="text/javascript" src="./js/jquery-ui-1.10.4.custom.min.js"></script>	
    <?php print_complete_footer(); ?>
    </BODY>
</HTML>
