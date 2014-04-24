<?php

/** @file
 * @brief view student's medications
 * @todo
 * 1. Filter
 * 2. Escape
 * 3. bootstrap
 * 4. Navigation
 * @remark
 * 1. Broke sometime 14.4.9
 */
 
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody check within



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
//require_once(IPP_PATH . 'include/config.inc.php');
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

//check if we are adding...
if(isset($_GET['add_medication']) && $have_write_permission) {
   //minimal testing of input...
   if($_GET['medication_name'] == "") $system_message = $system_message . "You must give a medication name<BR>";
   else {
     $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
     if(!preg_match($regexp,$_GET['start_date'])) { $system_message = $system_message . "Date must be in YYYY-MM-DD format<BR>"; }
     else {
       //one more check...is this student currently on this medication?
       //check for duplicates....
       $check_query="SELECT * FROM medication where student_id=" . mysql_real_escape_string($student_id) . " AND end_date IS NULL and medication_name='" . mysql_real_escape_string($_GET['medication_name']) . "'";
       $check_result = mysql_query($check_query); //just ignore the errors...like who cares anyways hey?
       if(mysql_num_rows($check_result) > 0) $system_message = $system_message . "This student is listed as currently on this medication<BR>";
       else {
          $add_query = "INSERT INTO medication (student_id,medication_name,doctor,start_date,end_date) VALUES (" . mysql_real_escape_string($_GET['student_id']) . ",'" . mysql_real_escape_string($_GET['medication_name']) . "','" . mysql_real_escape_string($_GET['doctor']) . "','" . mysql_real_escape_string($_GET['start_date']) . "',NULL)";
          //$system_message = $system_message . $add_query . "<BR>";
          $add_result = mysql_query($add_query);
          if(!$add_result) {
            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
            $system_message=$system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
          }
          //reset the variables...
          $_GET['medication_name'] = "";
          $_GET['doctor'] = "";
          $_GET['start_date'] = "";
       }
     }
   }
   //$system_message = $system_message . $add_query . "<BR>";
}

//check if we are deleting some entries...
if(isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_MEDICATION_PERMISSION && $have_write_permission ) {
    $delete_query = "DELETE FROM medication WHERE ";
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

//check if we are setting some entries not ongoing (field='is_valid')...
if(isset($_GET['set_ended_x']) && $have_write_permission ) {
    $modify_query = "UPDATE medication SET end_date=NOW() WHERE ";
    foreach($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $modify_query = $modify_query . "uid=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $modify_query = substr($modify_query, 0, -4);
    //$system_message = $system_message . $delete_query . "<BR>";
    $modify_result = mysql_query($modify_query);
    if(!$modify_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$modify_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
}

//get the medication for this student...
$medication_query="SELECT * FROM medication WHERE student_id=$student_id ORDER BY end_date ASC, start_date DESC";
$medication_result = mysql_query($medication_query);
if(!$medication_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$medication_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    
    
    
    <SCRIPT>
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
    <?php print_bootstrap_head(); ?>
</HEAD>
    <BODY>
   <?php print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']) ; ?>
   <?php print_jumbotron_with_page_name("Medication", $student_row['first_name'] . " " . $student_row['last_name'], $permission_level) ; ?> )
        
   <div class="container">
   <?php if ($system_message) { echo $system_message; } ?>
   
        
<div class="row">
<div class="col-md-6">  
                        <!-- BEGIN Medication List-->
                        <h2>Medication <small>(click to edit)</small></h2>
                        <form spellcheck="true" name="medicationlist" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "medication_view.php"; ?>" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                       
                        <table class="table table-striped table-hover">
                       
                        <?php
                        

                        //print the header row...
                        echo "<tr>\n
 						<th>Not Used</td>\n
 						<th>UID</td>\n
 						<th>Medication</td>\n
 						<th>Doctor</td>\n
 						<th>Start Date</td>\n
 						<th>End Date</td>\n
 						</tr>\n";
                        while ($medication_row=mysql_fetch_array($medication_result)) { //current...
                            echo "<tr>\n";
                            echo "<td><input type=\"checkbox\" name=\"" . $medication_row['uid'] . "\"></td>";
                            echo "<td>" . $medication_row['uid'] . "</td>";
                            echo "<td><a href=\"" . IPP_PATH . "edit_medication.php?uid=" . $medication_row['uid'] . "\" class=\"editable_text\">" . clean_in_and_out($medication_row['medication_name'])  ."</a></td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_medication.php?uid=" . $medication_row['uid'] . "\" class=\"editable_text\">" . $medication_row['doctor']  ."</a></td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_medication.php?uid=" . $medication_row['uid'] . "\" class=\"editable_text\">" . $medication_row['start_date'] . "</a></td>\n";
                            echo "<td><center><a href=\"" . IPP_PATH . "edit_medication.php?uid=" . $medication_row['uid'] . "\" class=\"editable_text\">"; if($medication_row['end_date']) echo $medication_row['end_date']; else echo "-current-"; echo "</a></center></td>\n";
                            echo "</tr>\n";
                         
                        }
                        ?>
                        <tr>
                          <td colspan="6" align="left">
                             <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:&nbsp;
                             </td>
                             <td>
                             <?php
                                if($have_write_permission) {
                                    echo "<INPUT NAME=\"set_ended\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=End\" border=\"0\" value=\"1\">";
                                }
                                //if we have permissions also allow delete and set all.
                                if($permission_level <= $IPP_MIN_DELETE_MEDICATION_PERMISSION && $have_write_permission) {
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
                        <!-- Medications Table -->
</div>

<div class="col-md-6">       
<!-- BEGIN add medication -->
     <h2>Add Medication <small>(and click "add medication")</small></h2>                 
<form role="form" name="add_medication" enctype="multipart/form-data" action="<?php echo IPP_PATH . "medication_view.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>

<div class="form-group">
<input type="hidden" class="form-control" name="add_medication" class="form-control" value="1">
<input type="hidden" class="form-control" name="student_id" class="form-control" value="<?php echo $student_id; ?>">
</div>                         
<div class="form-group">           
<label>Medication Name</label>
<input class="form-control" type="text" tabindex="1" name="medication_name" value="<?php if(isset($_GET['medication_name'])) echo $_GET['medication_name']; ?>" size="30" maxsize="254">
</div>

                    
<div class="form-group"> 

<label>Doctor</label>
                            
<input class="form-control" type="text" tabindex="2" name="doctor" value="<?php if(isset($_GET['doctor'])) echo $_GET['doctor']; ?>" size="30" maxsize="254">
</div>
<div class="form-group">
<label>Medication Start Date (YYYY-MM-DD)</label>                   
<input class="form-control" type=datepicker name="review_date" id="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd" tabindex="3" value="<?php if(isset($_GET['start_date'])) echo $_GET['start_date']; ?>">
</div>                          
<button type="submit" class="btn btn-default" type="submit" tabindex="4" value="add">Add Medication</button>                        
</form></div>

</div>
<!-- END  supervisor -->

                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
         
<?php print_complete_footer(); ?>

<!-- Bootstrap core JavaScript
 ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="./js/bootstrap.min.js"></script>   
<script type="text/javascript" src="./js/jquery-ui-1.10.4.custom.min.js"></script>
<?php print_datepicker_depends(); ?>   
</BODY>
</HTML>