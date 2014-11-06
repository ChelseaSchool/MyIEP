<?php

/** @file
 * @brief 	display student's strengths and needs
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * 1. Filter input
 * 2. Why not set whole header with PHP, rather than just pragma?
 * 3. Set form character set (UTF-8)
 * 4. Make all pages UTF-8 in html header or php
 * 5. Priority Bootstrap UI overhaul
 * 
 */  

error_reporting(0);

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/** @remark page takes $_GET['student_id'] as input
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
//* @todo make authenication check a function with productive parameters
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
//* @todo confirm input should be a function
$student_id="";
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "You've entered this page without supplying a valid student id. Fatal, quitting";
   exit();
}

//check permission levels
//* @todo permission level check should be a function
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
if(isset($_GET['add_strength_or_need']) && $have_write_permission) {
   //minimal testing of input...
     if($_GET['strength_or_need'] == "") $system_message = $system_message . "You must choose either strength or need<BR>";
     else {
       $add_query = "INSERT INTO area_of_strength_or_need (student_id, strength_or_need,description,is_valid) VALUES (" . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_GET['strength_or_need']) . "','" . mysql_real_escape_string($_GET['description']) . "','Y')";
       $add_result = mysql_query($add_query);
       if(!$add_result) {
         $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
         $system_message=$system_message . $error_message;
         IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
       }
       //reset the variables...
       $_GET['strength_or_need'] = "";
     }
   //$system_message = $system_message . $add_query . "<BR>";
}

//check if we are deleting some entries...
if(isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_STRENGTH_NEED_PERMISSION && $have_write_permission ) {
    $delete_query = "DELETE FROM area_of_strength_or_need WHERE ";
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
if(isset($_GET['set_not_current_x']) && $have_write_permission ) {
    $modify_query = "UPDATE area_of_strength_or_need SET is_valid='N' WHERE ";
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

//check if we are setting some entries ongoing field='is_valid'...
if(isset($_GET['set_current_x'])  && $have_write_permission ) {
    $modify_query = "UPDATE area_of_strength_or_need SET is_valid='Y' WHERE ";
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




//get the strengths/needs for this student...
$strength_query="SELECT * FROM area_of_strength_or_need WHERE student_id=$student_id ORDER BY is_valid ASC, strength_or_need ASC";
$strength_result = mysql_query($strength_query);
if(!$strength_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$strength_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

/** @fn
 *  @brief		very unclear
 *  @description	Why is code a function?
 *  @todo
 *  1. Find out what calls this function and what it is intended to do
 *  2. Rename this function so the name is effective and productive
 *  
 */

//get enum fields for area...
function mysql_enum_values($tableName,$fieldName)
{
  $result = mysql_query("DESCRIBE $tableName");

  //then loop:
  while($row = mysql_fetch_array($result))
  {
   ereg('^([^ (]+)(\((.+)\))?([ ](.+))?$',$row['Type'],$fieldTypeSplit);
   //split type up into array
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

  /* if the funciton makes it this far, then it either
    did not find an enum/set field type, or it
	failed to find the the fieldname, so exit FALSE! */
  return FALSE;

}
$enum_options_area = mysql_enum_values("area_of_strength_or_need","area");

?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    
    
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

<?php print_bootstrap_head(); ?>
</HEAD>
    <BODY>
<?php print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']); ?>    
<?php print_jumbotron_with_page_name("Strengths &amp; Needs", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission); ?>
       

<?php if ($system_message) { echo "<p align=\"center\">" . $system_message . "</p>";} ?>
<div class="container">
<h2>Strengths and Needs <small>View and Edit</small></h2>
                        <!-- BEGIN strength/needs table -->
                        <form name="strengthneedslist" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "strength_need_view.php"; ?>" method="get">
                        
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                        
                        
                        
                        
                        <?php
                        

                        //print the header row...
                        echo "<table width=80% class=\"table table-striped table-hover\" align=\"center\">\n
 								<tr>\n
 								<th>Select</th>\n
 								<th>UID</th>\n
 								<th>Type</th>\n
 								<th>Description (click to edit)</th>\n
 								<th>Ongoing</th>\n
 								</tr>\n";
                        while ($strength_row=mysql_fetch_array($strength_result)) { //current...
                            echo "<tr>\n";
                            echo "<td align=\"center\"><input class=\"form-control\" type=\"checkbox\" name=\"" . $strength_row['uid'] . "\"></td>\n";
                            echo "<td>" . $strength_row['uid'] . "</td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_strength_need.php?uid=" . $strength_row['uid'] . "\">" . $strength_row['strength_or_need']  . "</a></td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_strength_need.php?uid=" . $strength_row['uid'] . "\">" . $strength_row['description'] .  "</a></td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_strength_need.php?uid=" . $strength_row['uid'] . "\">" . $strength_row['is_valid']. "</a></td>\n";
                            echo "</tr>\n";
                          
                        }
                        echo "</table>"
                        ?>
                         <table class="table" width="80%" align="center">
                             <tr>
                             <td>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
                             </td>
                             <td>
                             <?php
                                if($have_write_permission) {
                                    echo "<INPUT NAME=\"set_not_current\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Not+Ongoing\" border=\"0\" value=\"1\">";
                                    echo "<INPUT NAME=\"set_current\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Ongoing\" border=\"0\" value=\"1\">";
                                }
                                //if we have permissions also allow delete and set all.
                                if($permission_level <= $IPP_MIN_DELETE_STRENGTH_NEED_PERMISSION && $have_write_permission) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"1\">";
                                }
                             ?>
                             </td>
                             </tr>
                            
                        </table>
                        </form>
                        <!-- end strength/needs table -->                       
                 

                        <!-- BEGIN add supervisor -->
                        <h2>Strengths and Needs <small>Edit and Click to Add</small></h2>
                        <form name="add_strength_or_need" enctype="multipart/form-data" action="<?php echo IPP_PATH . "strength_need_view.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        
                          
                           <input class="form-control" type="hidden" name="add_strength_or_need" value="1">
                           <input class="form-control" type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          
                         
                     
                      <div class="form-group">
                           <label>Add Strength or Need</label>
                           <select class="form-control" name="strength_or_need" tabindex="1">
                                   <option value="">Select Strength or Need</option>
                                   <option value="Strength" <?php if(isset($_GET['strength_or_need']) && $_GET['strength_or_need'] == 'Strength') echo "SELECTED"; ?>>Strength</option>
                                   <option value="Need" <?php if(isset($_GET['strength_or_need']) && $_GET['strength_or_need'] == 'Need') echo "SELECTED"; ?>>Need</option>
                           </select>
                    </div>
                        <div class="form-group">
                           <label>Description</label>
                           <textarea class="form-control" spellcheck="true" name="description" tabindex="2" cols="30" rows="3" wrap="soft"><?php if(isset($_GET['description'])) echo $_GET['description']; ?></textarea></div></td>
                     	   <button class="btn btn-default" type="submit" tabindex="3" name="add" value="add">Add Strength or Need</button>
                     		
                     		</div>
                      </form>
                     
                        <!-- END add supervisor -->

                        

                        

        <?php print_complete_footer(); ?>
        <?php print_bootstrap_js(); ?>
                        </div>
    </BODY>
</HTML>
