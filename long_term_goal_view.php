<?php
/*! @file
 *  @brief 	View goals and access page to edit progress
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
 * 1. Filter input
 * 2. Make sure the duplicate in include/ can be safely removed
 * 3. Re-enable buttons when other pages are ready
 * 5. Split is deprecated (line 521)
 * 6. JQuery to reveal only specified areas
 */

/** @var $MINIMUM_AUTHORIZATION_LEVEL = 100
 *  @brief The authorization level for this page (everybody)
 *  @todo  1. change to lowercase throughout the code (standardization and consistency (all caps is reserved for constants, globals)
 *  	   2. Should probably be recase as parameter for a function
 *  @bugs  none
 */
$MINIMUM_AUTHORIZATION_LEVEL = 100;




if(isset($system_message)) $system_message = $system_message; else $system_message = "";

/**@constant define('IPP_PATH','./')
 * @brief		Path for IPP required files (constant).
 * @todo		make a safe function; this is on every page
 * 
 * @author		M. Nielson
 */
define('IPP_PATH','./');

//* @remark required files
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/navbar.php');
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

$student_id="";
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
//if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

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

if(!isset($_GET['student_id']) || $_GET['student_id'] == "") {
    //ack
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit();
} else {
    $student_id=$_GET['student_id'];
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

//check if we are adding...
if(isset($_GET['next']) && $have_write_permission) {
  //if(!isset($_GET['goal_area']) || $_GET['goal_area'] == "") {
  //  $system_message = $system_message . "You must supply a goal area<BR>";
  //} else {
     header("Location: ./add_goal_1.php?goal_area=" . $_GET['goal_area'] . "&student_id=" . $student_id);
  //}
}
/*For later
 * $host  = $_SERVER['HTTP_HOST'];
 * $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
 * $extra = 'mypage.php';
 * header("Location: http://$host$uri/$extra");
 * exit;
 */

if(isset($_GET['setUncompleted']) && $have_write_permission) {
   $update_query = "UPDATE long_term_goal SET is_complete='N' WHERE goal_id=" . mysql_real_escape_string($_GET['setUncompleted']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['deleteSTO']) && $have_write_permission) {
   $update_query = "DELETE FROM short_term_objective WHERE uid=" . mysql_real_escape_string($_GET['deleteSTO']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['deleteLTG']) && $have_write_permission) {
   //delete the sto's
   $update_query = "DELETE FROM short_term_objective WHERE goal_id=" . mysql_real_escape_string($_GET['deleteLTG']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }  else {
     //delete the ltg's
     $update_query = "DELETE FROM long_term_goal WHERE goal_id=" . mysql_real_escape_string($_GET['deleteLTG']);
     $update_result = mysql_query($update_query);
     if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } //else { $system_message = $system_message . "OKIE DOKIE<BR>"; }
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['setCompleted']) && $have_write_permission) {
   $update_query = "UPDATE long_term_goal SET is_complete='Y' WHERE goal_id=" . mysql_real_escape_string($_GET['setCompleted']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['setSTOCompleted']) && $have_write_permission) {
   $update_query = "UPDATE short_term_objective SET achieved='Y' WHERE uid=" . mysql_real_escape_string($_GET['setSTOCompleted']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['setSTOUncompleted']) && $have_write_permission) {
   $update_query = "UPDATE short_term_objective SET achieved='N' WHERE uid=" . mysql_real_escape_string($_GET['setSTOUncompleted']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['deleteGoal']) && $have_write_permission) {
   $delete_query = "DELETE FROM long_term_goal WHERE goal_id=" . mysql_real_escape_string($_GET['deleteGoal']);
   $delete_result = mysql_query($delete_query);
   if(!$delete_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

$long_goal_query = "SELECT * FROM long_term_goal WHERE student_id=$student_id ORDER BY area ASC, is_complete DESC, goal ASC";
$long_goal_result = mysql_query($long_goal_query);
if(!$long_goal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$long_goal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$area_type_query = "SELECT  * from  typical_long_term_goal_category WHERE is_deleted='N'";
$area_type_result = mysql_query($area_type_query);
if(!$area_type_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$area_type_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

/*************************** popup chooser support function ******************/

/*
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

        // check if we have a zero resultant set
        if(!$numRows=mysql_num_rows($dataSource)){
          //zero result set (create empty array)
          $javascript.='</script><!--End popup array-->'."\n";
          return $javascript;
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
        //get a list of all available goal categories...
        $catlist_query="SELECT * FROM typical_long_term_goal_category where is_deleted='N' ORDER BY name ASC";
        $catlist_result=mysql_query($catlist_query);
        if(!$catlist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$catlist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            return;
        }

        while($catlist=mysql_fetch_array($catlist_result)) {
           $objlist_query="SELECT typical_long_term_goal.goal FROM typical_long_term_goal WHERE cid=" . $catlist['cid'] . " AND typical_long_term_goal.is_deleted='N'";
           $objlist_result = mysql_query($objlist_query);
           if(!$objlist_result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objlist_query'<BR>";
             $system_message= $system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
             //call the function to create the javascript array...
             echo createJavaScript($objlist_result,$catlist['name']);
           }
        }
    }

*/
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
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./css/jumbotron.css" rel="stylesheet">
	<style type="text/css">body { padding-bottom: 70px; }</style>
    
     
     <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
     <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
     
     <?php
       //output the javascript array for the chooser popup
       //echoJSServicesArray();
     ?>
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }

      function noSelection() {
          alert("You must choose a goal category to enable the chooser"); return false;
      }

    </SCRIPT>
<link rel=stylesheet type=text/css href=./css/jquery-ui-1.10.4.custom.css>

<?php //$details="hide"; ?> 
<?php  /*
if  ($details="hide")
 {
	echo "<style>";
	echo "[id^=\"details\"]{";
	echo "   display: none;";
	echo "</style>";
	echo "}";
	}
	else 
	{
		echo "<style>";
		echo "[id^=\"details\"]{";
		echo "   display: inline;";
		echo "}";
		echo "</style>";	
	} */  
?>



<script>
function toggle ()
{
	$("div#details").toggle ("explode", 100)
}
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

<?php if ($system_message) echo $system_message; ?>

<h1>Goals: <small><?php echo $student_row['first_name'] . " " . $student_row['last_name'] ?> </small></h1>
<h2>Logged in as: <small><?php echo $_SESSION['egps_username']; ?> (Permission: <?php echo $our_permission; ?>)</small></h2>
<button class="btn btn-lg btn-primary" onclick="toggle ()" role="button">Toggle Details &raquo;</button>

</div> <!-- close container -->

</div> <!-- Close Jumbotron -->

 
<!--  End Jumbotron -->
<div class=container>
<p><em><strong>Release Note</strong>: Details of objectives are hidden on this page for this release. Click <a onclick="toggle ()">here</a> to toggle.</em></p>




<?php
//check if we have no goals...we need to end this table in this case.
if(mysql_num_rows($long_goal_result) == 0 ) {
	echo "<p>There are no goals to view</p>";
	}
	$goal_num=1;
	while($goal = mysql_fetch_array($long_goal_result)) {
		//div for use by jquery filter action
		$div_id=$goal['area'];
		echo "<div class=\"container\" id=\"$div_id\">";
		
		echo "<div class=\"row\"><div class=\"col-md-12\"><div class=\"container\">";
		echo "<h2><a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id']  . "\"";
		if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
		else echo "onClick=\"return changeStatusCompleted();\"";
		
		
		
		echo "<h2>" . $goal['area'] . "</h2>";
		echo "<h3><small>" . $goal_num . ") </small>";
        $goal_num++; //increment goal
        echo $goal['goal'] . "</a>&nbsp;<span class=\"label label-default\">goal</span></h3>"; //output goal
        
		//Review Date
		/* $today = time(); #today's date in seconds since January 1, 1970
		$date_split = split("-",$goal['review_date']);
		$date_seconds = mktime(0,0,0,$date_split[1],$date_split[2],$date_split[0]); //since j1,1970
		 if($today >= $date_seconds && $goal['is_complete'] != 'Y') {
			echo "<p>Review date (expired):" .  "<a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id']  . "\""; 
			if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
				else echo "onClick=\"return changeStatusCompleted();\">";
				echo  $goal['review_date'] . "</a></p>";
			}
		else {
			echo " <p>Review date: <a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id']  . "\"";
			if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
			else echo "onClick=\"return changeStatusCompleted();\">";
			echo $goal['review_date'] . "</a></p>";
        } */
       

         echo "<div class=\"btn-group\">";
	//output the complete/uncomplete button...
		if($goal['is_complete'] == 'Y') {
			echo "<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setUncompleted=" . $goal['goal_id'] . "\"";
			if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
			else echo "onClick=\"return changeStatusCompleted();\"";
			echo "<button type=\"button\" class=\"btn btn-xs btn-primary\">Set Uncompleted</button></a>";
			} 
			else 
				{
                 echo "<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setCompleted=" . $goal['goal_id'] . "\"";
					if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
					else echo "onClick=\"return changeStatusCompleted();\"";
					echo "\"><button type=\"button\" class=\"btn btn-xs btn-primary\">Set Completed</button></a>";
				}
        //output the add objectives button.
		/*echo "<a href=\" . IPP_PATH . "add_objectives.php?&student_id=" . $student_id  . "&lto=" . $goal['goal_id'] . "\"";
 		if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
		else echo "onClick=\"return changeStatusCompleted();\"";
		echo "\"><button type=\"button\" class=\"btn btn-xs btn-primary\">Add Objective</a>"; */

		//output the edit button.
		/* echo "<a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id']  . "\"";
		if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
  		else echo "onClick=\"return changeStatusCompleted();\"";
		echo "\"><button type=\"button\" class=\"btn btn-xs btn-primary\">Edit</button></a>"; */

    	echo "<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id  . "&deleteLTG=" . $goal['goal_id'] . "\"";
  		if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
		else echo "onClick=\"return changeStatusCompleted();\"";
		echo "\"><button type=\"button\" class=\"btn btn-xs btn-primary\">Delete</button></a></div>";
		echo "<hr>";
		echo "</div></div></div>";//close row and column
		
		
	
    
		
		//finish...


//short term objectives

$short_term_objective_query = "SELECT * FROM short_term_objective WHERE goal_id=" . $goal['goal_id'] . " ORDER BY achieved ASC";
$short_term_objective_result = mysql_query($short_term_objective_query);
//check for error
if(!$short_term_objective_result) {
	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$short_term_objective_query'<BR>";
	$system_message=$system_message . $error_message;
	IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
	echo $system_message;
	} 
	else {
	//output this note...
	//check if we have no notes
 		if(mysql_num_rows($short_term_objective_result) <= 0 ) {
			
			echo "<div class=\"container\"><div class=\"alert alert-warning\">No Objectives Added</div></div>";
		
		}
	$obj_num=1;
	while ($short_term_objective_row = mysql_fetch_array($short_term_objective_result)) {
		echo "<div class=\"row\" id=\"objectives\"><div class=\"col-md-12\"><div class=\"container\">";
		echo "<h4><small>" . $obj_num . ")&nbsp;</small>";
		$obj_num++; //increment goal
		echo $short_term_objective_row['description'] . "&nbsp <span class=\"label label-info\">objective</span></h4>";

	//begin review date
		
		$now = time(); #today's date in seconds since January 1, 1970
		$date_split = split("-",$short_term_objective_row['review_date']);
		$date_seconds = mktime(0,0,0,$date_split[1],$date_split[2],$date_split[0]); //since j1,1970
		/* if($now > $date_seconds && $short_term_objective_row['achieved']!='Y') { //$today >= $date_seconds) {
			echo "<p>Review Date (expired)</p><a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . "\"";
			if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
			else echo "onClick=\"return changeStatusCompleted();\">";
			echo $short_term_objective_row['review_date'] . "</a></p>";
        }
		else {
			echo "<p>Review date: <a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id  . "\"";
			if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
			else echo "onClick=\"return changeStatusCompleted();\">";
			echo $short_term_objective_row['review_date'] . "</a></p>";
		} */
		//end review date
	echo "<div class=\"container\">";	
	//output the complete/uncomplete button...
	echo "<div class=\"btn-group\">";
	echo "<button class=\"btn btn-xs btn-primary\" onclick=\"toggle ()\" role=\"button\">Toggle Details</button>";	 
	if($short_term_objective_row['achieved'] == 'Y') {
		echo "<a class=\"btn btn-xs btn-primary\" href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setSTOUncompleted=" . $short_term_objective_row['uid'] . "\"";
		if (!$have_write_permission) echo "&nbsp onClick=\"return noPermission();\"";
		else echo "& nbsp onClick=\"return changeStatusCompleted();\"";
		echo "\">Set Incomplete</a>";
		
		
	} 
	else {
		echo "<a class=\"btn btn-xs btn-primary\" href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setSTOCompleted=" . $short_term_objective_row['uid'] . "\"";
		if (!$have_write_permission) echo "&nbsp onClick=\"return noPermission();\"";
		else echo "&nbsp onClick=\"return changeStatusCompleted();\"";
		echo " \">Set Completed</a>";
	}

	//output the add edit button.
	//echo "<button href=\" . IPP_PATH . \"edit short_term_objective.php?sto=\" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . /"";

	echo "<a class=\"btn btn-xs btn-primary\"" . "&nbsp; href=\"./edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . "\"" . ">";
	echo "Edit Objective</a>";
	
	//output delete button
	echo "<a class=\"btn btn-xs btn-primary\" href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&deleteSTO=" . $short_term_objective_row['uid'] . "\"";
	if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
	else echo "onClick=\"return changeStatusCompleted();\"";
	echo "\">Delete Objective</a>";
	
	echo "<a class=\"btn btn-xs btn-primary\"" . "&nbsp; href=\"./edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . "\"" . ">";
	echo "Report on Progress</a>";
	
	echo "<hr>";
	echo "</div>";
	echo "</div>";
	
	
	//output the results /assmt / etc...

	

	//output the add edit button.
	/* echo "&nbsp;<a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id  . "\"";
	*if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
	* else echo "onClick=\"return changeStatusCompleted();\"";
	* echo "\">Edit</a>";
	*/
	
	echo "<div class=\"container\" id=\"details\" style=\"display:none\">";
	//output the actual data
	if ($short_term_objective_row['assessment_procedure'] != "") {
		echo "<strong>Assessment Procedure</strong>";
		echo "<p>" .  $short_term_objective_row['assessment_procedure'] . "</p>";
	}
		//Strategies
	if ($short_term_objective_row['strategies']!=""){
		echo "<strong>Strategies</strong>" ;
		echo "<P>" . $short_term_objective_row['strategies'] . "</P>";
	}
	
	

	//Progress Review
	if ($short_term_objective_row['results_and_recommendations']!=""){
		echo "<strong>Progress Review</strong>";
		echo "<p>" . $short_term_objective_row['results_and_recommendations'] . "</p>";
	}
		//output the add edit button.
	//echo "&nbsp;<a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id  . "\"";
	//if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
	//else echo "onClick=\"return changeStatusCompleted();\"";
	//echo " class=\"small\">Edit</a>";
	echo "</div>"; //end objective details container

	
	//end output the actual data
	//end toggle
	//echo "</div>";//show/hide objectives
	echo "</div></div></div>";
	}
	
	}
}

?>
                        
<!-- END  goals -->

<!-- commented because can't find opening tag </div> -->
                         

</div> 


 
<!-- Bootstrap core JavaScript
 ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="./js/bootstrap.min.js"></script>   
<script type="text/javascript" src="./js/jquery-ui-1.10.4.custom.min.js"></script>
</BODY>
</HTML>
