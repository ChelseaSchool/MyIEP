<?php
/**@file
 * @brief 	revise or edit student demographics
 * @todo    modify bootstrap button to btn-success?
 */
 
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['uid']  or $_POST['uid']
 *
 */

/**
 * Path for IPP required files.
 */

$system_message = "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/navbar.php';
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
if(isset($_POST['uid'])) $uid=$_POST['uid'];
else $uid=$_GET['uid'];
//run query first then validate...
//get the values for this student...
$info_row="";
$info_query="SELECT * FROM background_info WHERE uid=" . mysql_real_escape_string($uid);
$info_result = mysql_query($info_query);
if(!$info_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$info_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
   $info_row = mysql_fetch_array($info_result);
}

$student_id=$info_row['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "This entry has generated a 'null' student id, fatal error- quitting. Query='$info_query'";
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
if(isset($_POST['edit_info']) && $have_write_permission) {
   //minimal testing of input...
     if($_POST['type'] == "") $system_message = $system_message . "You must choose a type<BR>";
     else {
         $edit_query = "UPDATE background_info SET type='" . mysql_real_escape_string($_POST['type']) . "',description='" . mysql_real_escape_string($_POST['description']) . "' WHERE uid=" . mysql_real_escape_string($_POST['uid']) . " LIMIT 1";
         $edit_result = mysql_query($edit_query);
         if(!$edit_result) {
           $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$edit_query'<BR>";
           $system_message=$system_message . $error_message;
           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         } else {
           //redirect here...
           header("Location: " . IPP_PATH . "background_information.php?student_id=" . $student_id);
         }
     }

   //$system_message = $system_message . $add_query . "<BR>";
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
$enum_options_type = mysql_enum_values("background_info","type");

print_html5_primer();
print_bootstrap_head();
?> 
    <SCRIPT LANGUAGE="JavaScript">
      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
    </SCRIPT>
</HEAD>
    <BODY>
    <?php 
    print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']);
    print_jumbotron_with_page_name("Edit Background Information", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission)
    ?>
    <div class="container"> 
    <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>


     <!-- BEGIN edit background info -->
                       
     <form name="edit_background_info" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_background_information.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
     <p>Edit and click "Submit"</p>
     <div class="form-group">
     <input type="hidden" name="edit_info" value="1">
     <input type="hidden" name="uid" value="<?php echo $info_row['uid'];?>">
                         
     <label>Type</label>
     <select class="form-control" name="type" tabindex="1">
     <option value="">-Choose-</option>
     <?php foreach($enum_options_type as $i => $value) {
        echo "<option value=\"$value\"";
        if ($value == $info_row['type']) echo " selected";
           echo ">$value</option>";
        }
      ?>
      </select>
                         
      <label>Description</label>
      <textarea class="form-control" spellcheck="true" name="description" tabindex="2" cols="30" rows="5" wrap="soft"><?php echo $info_row['description'];?></textarea>
       </div> 
      <p><button class="btn btn-md btn-success" tabindex="3" type="submit" name="Update" value="Update">Submit</button></p>
                     
                        </form>
                        
                        <!-- END edit bg info -->

                        </div>
                        
           
        <footer><?php print_complete_footer(); ?></footer>
        <?php print_bootstrap_js(); ?>
    </BODY>
</HTML>
