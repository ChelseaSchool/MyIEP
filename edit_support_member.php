<?php
/** @file
 * @brief 	perhaps define student's IEP team
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo
 * #. Not accessible from frontend
 * #. UI overhaul barely started
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody


/*   INPUTS: $_GET['student_id'] or PUT
 *
 */



$system_message = "";

/**
 * Path for IPP required files.
 */

define('IPP_PATH','./');

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/navbar.php';
require_once IPP_PATH . 'include/mail_functions.php';
require_once IPP_PATH . 'include/supporting_functions.php';


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
} else {
   $student_id = mysql_real_escape_string($student_id);
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

//get the support list for this student...
$support_row="";
$support_query="SELECT * FROM support_list LEFT JOIN support_member ON support_list.egps_username=support_member.egps_username WHERE student_id=$student_id AND support_list.egps_username='" . mysql_real_escape_string($_GET['username']) . "'";
$support_result = mysql_query($support_query);
if(!$support_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$support_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
    $support_row = mysql_fetch_array($support_result);
}

//check if we are modifying...
if($have_write_permission && isset($_GET['modify'])) {
    //check if we are trying to update the permission but don't have the rights...
    if($_GET['permission'] != $support_row['permission'] && !($our_permission == "ALL" || $our_permission == "ASSIGN")) {
        $system_message = $system_message . "You don't have the permission level necessary to modify permission levels<BR>";
    } else {
       if(($_GET['permission'] == "ALL" || $_GET['permission'] == "ASSIGN") && !($our_permission == "ALL")) {
         $system_message = $system_message . "You must be set with 'ALL' level permission to grant 'ASSIGN' permissions and higher";
       } else {
         //we need to update the information here...
         $update_query = "UPDATE support_list SET support_area='" . mysql_real_escape_string($_GET['support_area']) . "', permission='" . mysql_real_escape_string($_GET['permission']) . "' WHERE student_id=$student_id AND egps_username='" . mysql_real_escape_string($_GET['username']) . "'";
         $update_result = mysql_query($update_query);
         if(!$update_result) {
              $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
              $system_message= $system_message . $error_message;
              IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         } else {
            if(isset($_GET['mail_notification'])) {
              mail_notification(mysql_real_escape_string($_GET['username']),"This email has been sent to you to notify you that your permission levels for " . $student_row['first_name'] . " " . $student_row['last_name'] . "'s IPP on the $IPP_ORGANIZATION online individual program plan system have been changed to " . mysql_real_escape_string($_GET['permission']) . " access.");
            }
            //we need to redirect back to main...
            header("Location: " . IPP_PATH . "student_view.php?student_id=$student_id");
         }
       }
    }
}

//redo the query...one day should come up with a more efficient method...
//get the support list for this student...
$support_row="";
$support_query="SELECT * FROM support_list LEFT JOIN support_member ON support_list.egps_username=support_member.egps_username WHERE student_id=$student_id AND support_list.egps_username='" . mysql_real_escape_string($_GET['username']) . "'";
$support_result = mysql_query($support_query);
if(!$support_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$support_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
    $support_row = mysql_fetch_array($support_result);
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
        global $system_message,$student_id;
        //get a list of all available goal categories...
        $catlist_query="SELECT name FROM typical_long_term_goal_category WHERE is_deleted='N' ORDER BY name ASC";
        $catlist_result=mysql_query($catlist_query);
        if(!$catlist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$catlist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            return;
        } else {
             //call the function to create the javascript array...
             echo createJavaScript($catlist_result,"popuplist");
        }
    }
/************************ end popup chooser support funtion  ******************/

?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <?php print_bootstrap_head(); ?>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
    <script language="javascript" src="<?php echo IPP_PATH . "include/autocomplete.js"; ?>"></script>
     <?php
       //output the javascript array for the chooser popup
       echoJSServicesArray();
     ?>
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
        <?php print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']); ?>
        <?php print_jumbotron_with_page_name("Edit Support Member", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission); ?>
        <div class="container">
        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>
        
        <!-- BEGIN add supervisor -->
                        
        <form name="edit_support_member" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_support_member.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
        <h3>Edit <small>Click "Modify" to Submit</small></h3>
                           <input type="hidden" name="modify" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                           <input type="hidden" name="username" value="<?php echo $_GET['username']; ?>">
        
        <h4>Modify User: <small><?php echo $_GET['username']; ?></small></h4>
        <div class="form-group">
        <label for="permission">Set Access Permissions to <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?></label>
        <select class="form-control" name="permission">
            <option value="">-Choose-</option>
            <option value="READ" <?php if($support_row['permission'] == 'READ') echo "SELECTED"; ?>>Read (Read Only)</option>
            <option value="WRITE" <?php if($support_row['permission'] == 'WRITE') echo "SELECTED"; ?>>Write (Read and Write)</option>
            <option value="ASSIGN" <?php if($support_row['permission'] == 'ASSIGN') echo "SELECTED"; ?>>Assign (Read,Write,Assign others permissions)</option>
            <option value="ALL" <?php if($support_row['permission'] == 'ALL') echo "SELECTED"; ?>>All (Unlimited permission)</option>
        </select>
        </div>
        
        <p><label for="support_area">Support Area</label></p>
        
        <p><input type="text" size="40" name="support_area" onkeypress="return autocomplete(this,event,popuplist)" value="<?php echo $support_row['support_area'];?>">&nbsp;<img align="top" src="<?php echo IPP_PATH . "images/choosericon.png"; ?>" height="17" width="17" border=0 onClick="popUpChooser(this,document.all.support_area);"></p>
        <div class="form-group">
        <label for="mail_notification">Send email notification</label>
        <p><input type="checkbox" <?php if(!isset($_POST['ACTION']) || (isset($_POST['ACTION']) && isset($_POST['mail_notification']))) echo "checked"; ?> name="mail_notification"></p>
        </div>
        <p><input class="btn btn-success" type="submit" name="modify" value="Modify"></p>
        
        
        
                
        
<?php print_complete_footer(); ?>
<?php print_bootstrap_js(); ?>
    </BODY>
    
</HTML>
