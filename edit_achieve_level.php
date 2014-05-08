<?php
/** @file
 * @brief 	modify student achievement level
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
 * 1. Filter input/escape output
 * 2. Priority UI overhaul
 * 3. fix permission in jumbotron: should display $our_permission
 */ 
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['uid'] || $_POST['uid']
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
require_once(IPP_PATH . 'include/supporting_functions.php'); //added second sprint
require_once(IPP_PATH . 'include/config.inc.php'); //added second sprint


no_cash(); //experiment for second sprint

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

//get the perf stuffs for this student...
$performance_row="";
$performance_query="SELECT * FROM performance_testing WHERE uid=$uid";
$performance_result = mysql_query($performance_query);
if(!$performance_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$performance_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
   $performance_row=mysql_fetch_array($performance_result);
}

$student_id=$performance_row['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "Cannot determine student id from achievement level UID. Fatal, quitting";
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

    if(!$_POST['test_name']) return "You must supply a test name<BR>";
    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if(!preg_match($regexp,$_POST['date'])) return "Date must be in YYYY-MM-DD format<BR>";

     if($_FILES['supporting_file']['size'] <= 0 && $_FILES['supporting_file']['name'] !="") return "Zero bytes uploaded (The file was not handled properly by the server. Most likely the file was too large and the server timed out on upload)";
     if($_FILES['supporting_file']['size'] <= 0) { $fileName=""; $tmpName="";$fileSize=0;$fileType=null; return NULL; } //handle no file upload.
     if($_FILES['supporting_file']['size'] >= 1048576) return "File must be smaller than 1MB (1048567Bytes) but is " . $_FILES['supporting_file']['size'] . "MB"; //Must be less than 1 Megabyte

     //we have a file so get the file information...
     $fileName = mysql_real_escape_string($_FILES['supporting_file']['name']);
     $tmpName  = $_FILES['supporting_file']['tmp_name'];
     $fileSize = mysql_real_escape_string($_FILES['supporting_file']['size']);
     //$fileType = mysql_real_escape_string($_FILES['supporting_file']['type']);
     $fileType = "";

     if(is_uploaded_file($tmpName)){
       $ext =explode('.', $fileName);
       $ext = $ext[count($ext)-1];
     } else {
       return "Security problem: file does not look like an uploaded file<BR>";
     }

      $fp      = fopen($tmpName, 'rb');
      if(!$fp) return "Unable to open temporary upload file $tmpname<BR>";
      $content = fread($fp, filesize($tmpName));
      $content = mysql_real_escape_string($content);
      fclose($fp);

      //return $fileType . "<-filetype<BR>";

      switch($ext) {
         case "txt":
         case "rtf":
         case "TXT":
         case "RTF":
           //make sure we don't have binary data here.
           for($i = 0; $i < strlen($content); $i++){
              if(ord($content[$i]) > 127) { IPP_LOG("Attempted to upload binary data as txt in IPP Coordination of Services page for student #$student_id",$_SESSION['egps_username'],'ERROR'); return "Not a valid Text file: contains binary data<BR>"; }
           }
           $content=mysql_real_escape_string($content);
           $fileType="text/plain";
         break;
         case "pdf":
         case "PDF":
          if(strncmp("%PDF-",$content,5) != 0) { IPP_LOG("Attempted to upload file not recognized as PDF in first few bytes in IPP Coordination of Services page for student #$student_id",$_SESSION['egps_username'],'ERROR'); return "File does not appear to be a valid PDF file<BR>"; }
          $fileType="application/pdf";
         break;
         case "doc":
         case "DOC":
         //check for 0xD0CF (word document magic number)
         for($i=0;$i < 2; $i++) {
            $msg = $msg . $content[$i];
         }
         $msg = "0x" . bin2hex($msg);
         if($msg != "0xd0cf") { IPP_LOG("Attempted to upload file not recognized as MS Word Document in IPP Coordination of Services page for student #$student_id",$_SESSION['egps_username'],'ERROR'); return "File does not appear to be a valid MS Word Document file<BR>"; }
         $fileType="application/msword";
         break;
         default:
           return "File extension '$ext' on '$fileName' is not a recognized type please upload only MS Word, Plain Text, or PDF documents<BR>";
     }

     return NULL;
}

//check if we are modifying an achievement level
if(isset($_POST['edit_performance_testing']) && $have_write_permission) {
  $retval=parse_submission();
  if($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
    //we add the entry.
    $update_query = "UPDATE performance_testing SET test_name='" . mysql_real_escape_string($_POST['test_name']) . "',date='" . mysql_real_escape_string($_POST['date']) . "',results='" . mysql_real_escape_string($_POST['results']) . "'";
     if($fileName != "") $update_query = $update_query . ",filename='$fileName',file='$content'";
     $update_query = $update_query . " WHERE uid=$uid LIMIT 1";
     $update_result = mysql_query($update_query);
     if(!$update_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . substr($update_query,0,300) . "[truncated]'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
        //redirect
        header("Location: " . IPP_PATH . "achieve_level.php?student_id=" . $student_id);
     }
  }
}

?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    
    
    <script src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <SCRIPT>
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.performancetesting;
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
    <link rel="stylesheet" href="css/smoothness/jquery-ui-1.10.4.custom.css">
	<script src="js/jquery-2.1.1.js"></script>
	<script src="js/smoothness/jquery-ui-1.10.4.custom.js"></script>
	<script> 
	$(function() {
	$( "#datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
	});
	</script>
</HEAD>
    <BODY>
    <?php print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']) ; ?>
    <?php print_jumbotron_with_page_name('Edit Achievement Level', $student_row['first_name'] . " " . $student_row['last_name'] , $our_permission); ?>
    <?php if ($system_message) { echo $system_message;} ?> 
    <div class=row>
    <div class=container>
    <h2><?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?>: <small>Edit Achievement Level</small></h2>
     <!-- BEGIN add new entry -->
                        
<form role="form" name="edit_performance_testing" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_achieve_level.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
<div class=col-md-6>                      
<div class="form-group">
<input type="hidden" name="edit_performance_testing" value="1">
<input type="hidden" name="uid" value="<?php echo $uid; ?>">
</div>  


<div class="form-group">
	<label>Test Name</label>
	<input type="text" spellcheck="TRUE" tabindex="1" name="test_name" size="30" maxsize="255" value="<?php echo $performance_row['test_name']; ?>">
</div>
<div class="form-group">
	<label>Date: (YYYY-MM-DD)</label>
	<input id="datepicker" class="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd" type="datepicker" tabindex="2" name="date" value="<?php echo $performance_row['date']; ?>">
</div>
<div class="form-group">
<label>Optional File Upload <small>(.doc,.pdf,.txt,.rtf)</small></label>
<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
<input type="file" tabindex="3" name="supporting_file" value="<?php echo $_FILES['supporting_file']['name'] ?>">
</div>
</div><!-- End Left Column -->
<div class=col-md-6>
<div class="form-group">  
<label>Results</label>
<p>
<textarea autofocus spellcheck="true" name="results" tabindex="4" rows="8" cols="30" wrap="soft"><?php echo $performance_row['results']; ?></textarea>
</p>
</div>

<div class="form-group">
<button type="submit" name=update class="btn btn-default">Submit</button>
<!--  <input type="submit" tabindex="5" name="Update" value="Update">-->
</div>
</div><!-- Close column -->
</form>
 <!-- END edit entry -->
</div><!-- Container -->
 </div>
                     
            
    <?php print_complete_footer(); ?>
	<?php print_bootstrap_js(); ?></div>
    </BODY>
</HTML>
