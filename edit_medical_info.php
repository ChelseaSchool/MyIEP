<?php
/** @file
 * @brief 	edit student medical information
 * @todo
 * * datepicker
 */
 
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['student_id'] || $_PUT['student_id']
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
require_once (IPP_PATH . 'include/supporting_functions.php');

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
if(isset($_GET['uid'])) $uid= $_GET['uid'];
if(isset($_POST['uid'])) $uid = $_POST['uid'];

//get the coordination of services for this student...
$medical_row="";
$medical_query="SELECT * FROM medical_info WHERE uid=$uid";
$medical_result = mysql_query($medical_query);
if(!$medical_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$medical_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
   $medical_row=mysql_fetch_array($medical_result);
}

$student_id=$medical_row['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "Unable to get student id from medical uid. Fatal, quitting";
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

    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if(!preg_match($regexp,$_POST['date'])) return "Date must be in YYYY-MM-DD format<BR>";

     if($_POST['description'] == "") return "You must supply a description<BR>";

     if($_FILES['supporting_file']['size'] <= 0 && $_FILES['supporting_file']['name'] !="") return "Zero bytes uploaded (Most likely the file was too large and the server timed out on upload or the file was not handled properly by the server)";
     if($_FILES['supporting_file']['size'] <= 0) { $fileName=""; $tmpName="";$fileSize=0;$fileType=null; return NULL; } //handle no file upload.
     if($_FILES['supporting_file']['size'] >= 1048576) return "File must be smaller than 1MB (1048567Bytes) but is " . $_FILES['supporting_file']['size'] . "MB"; //Must be less than 1 Megabyte

     //we have a file so get the file information...
     $fileName = mysql_real_escape_string($_FILES['supporting_file']['name']);
     $tmpName  = $_FILES['supporting_file']['tmp_name'];
     $fileSize = mysql_real_escape_string($_FILES['supporting_file']['size']);
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

      switch($ext) {
         case "txt":
         case "rtf":
         case "TXT":
         case "RTF":
           //make sure we don't have binary data here.
           for($i = 0; $i < strlen($content); $i++){
              if(ord($content[$i]) > 127) { IPP_LOG("Attempted to upload binary data as txt in IPP Coordination of Services page for student #$student_id",$_SESSION['egps_username'],'ERROR'); return "Not a valid Text file: contains binary data<BR>"; }
           }
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

//check if we are modifying a student...
if(isset($_POST['edit_medical_info']) && $have_write_permission) {
  $retval=parse_submission();
  if($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
    //we add the entry.
     $insert_query = "UPDATE medical_info SET copy_in_file='";
     if($_POST['report_in_file']) $insert_query = $insert_query . "Y";
     else $insert_query = $insert_query . "N";
     $insert_query .= "',is_priority='";
     if($_POST['is_priority']) $insert_query = $insert_query . "Y";
     else $insert_query = $insert_query . "N";
     $insert_query .= "',date='" . mysql_real_escape_string($_POST['date']) . "',description='" . mysql_real_escape_string($_POST['description']) . "'";
     if($fileName != "") $insert_query = $insert_query . ",filename='$fileName',file='$content'";
     $insert_query .= " WHERE uid=$uid LIMIT 1";
     $insert_result = mysql_query($insert_query);
     if(!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . substr($insert_query,0,100) . "[truncated]'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     }
     //$system_message = $system_message . $insert_query . "<BR>";
     //redirect
      header("Location: " . IPP_PATH . "medical_info.php?student_id=" . $student_id);
  }
}

?> 
<?php print_html5_primer()?>
    <TITLE><?php echo $page_title; ?></TITLE>
    
    
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.medicalinfo;
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
<?php print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']);?>
<?php print_jumbotron_with_page_name("Edit Medical Information", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission); ?>
<?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>
<div class="container">
<!-- BEGIN add new entry -->
                        
<form name="edit_medical_info" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_medical_info.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        
<input type="hidden" name="edit_medical_info" value="1">
<input type="hidden" name="uid" value="<?php echo $uid; ?>">
<div class="form-group">
<label>Date (YYYY-MM-DD)</label>
<input class="form-control datepicker" required type="datepicker" name="date" tabindex="1" value="<?php echo $medical_row['date']; ?>">
</div>
<label>Optional File Upload (.doc,.pdf,.txt,.rtf)</label>
<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
<input type="file" tabindex="2" name="supporting_file" value="<?php echo $_FILES['supporting_file']['name'] ?>">
                           
<label>Report in File</label>                  
<input type="checkbox" tabindex="3" name="report_in_file" <?php if($medical_row['copy_in_file']=='Y') echo "checked";?>>
                           
<label>Priority Entry</label>
<input type="checkbox" tabindex="4" name="is_priority" <?php if($medical_row['is_priority']=='Y') echo "checked";?>>
<div class="form-group">                     
<label>Description</label>
<textarea class="form-control" required spellcheck="true" tabindex="5" name="description" cols="30" rows="5" wrap="SOFT"><?php echo $medical_row['description']; ?></textarea>
</div>                        
<button type="submit" class="button btn-lg btn-default">Submit</button>                        
</form>
                        
<!-- END add new entry -->

      <?php print_complete_footer(); ?>
      </div> 
      <?php print_bootstrap_js();?>                
    </BODY>
</HTML>
