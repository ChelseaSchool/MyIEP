<?php

/** @file
 * @brief 	display medical information
 * @todo
 * 1. Bootstrap
 * 2. Student context navbar
 * 3. filter, spellcheck, escape
 * @remark Replaced checkSpelling() with HTML spellcheck="TRUE"
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
if(isset($_POST['add_medical_info']) && $have_write_permission) {
  $retval=parse_submission();
  if($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
    //we add the entry.
    $insert_query = "INSERT INTO medical_info (student_id,copy_in_file,is_priority,date,description,file,filename) VALUES (" . mysql_real_escape_string($student_id) . ",'";
     if(isset($_POST['report_in_file'])) $insert_query = $insert_query . "Y";
     else $insert_query = $insert_query . "N";
     if(isset($_POST['is_priority'])) $insert_query = $insert_query . "','Y";
     else $insert_query = $insert_query . "','N";
     $insert_query = $insert_query . "','" . mysql_real_escape_string($_POST['date']) . "','" . mysql_real_escape_string($_POST['description']) . "','$content','$fileName')";
     $insert_result = mysql_query($insert_query);
     if(!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . substr($insert_query,0,100) . "[truncated]'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
       //$system_message = $system_message . $insert_query . "<BR>";
        unset($_POST['date']);
        unset($_POST['description']);
        unset($_POST['report_in_file']);
        unset($_POST['is_priority']);
     }
  }
}

//check if we are deleting some entries...
if(isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_MEDICAL_INFO && $have_write_permission ) {
    $delete_query = "DELETE FROM medical_info WHERE ";
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

//check if we are setting some entries to not in file...
//if($_GET['set_not_in_file'] && $have_write_permission ) {
//    $update_query = "UPDATE coordination_of_services SET report_in_file='N' WHERE ";
//    foreach($_GET as $key => $value) {
//        if(preg_match('/^(\d)*$/',$key))
//        $update_query = $update_query . "uid=" . $key . " or ";
//    }
    //strip trailing 'or' and whitespace
//    $update_query = substr($update_query, 0, -4);
    //$system_message = $system_message . $update_query . "<BR>";
    //$update_result = mysql_query($update_query);
//    if(!$update_result) {
//        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
//        $system_message= $system_message . $error_message;
//        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
//    }
//}

//check if we are setting some entries to in file...
//if($_GET['set_in_file'] && $have_write_permission ) {
//    $update_query = "UPDATE coordination_of_services SET report_in_file='Y' WHERE ";
//    foreach($_GET as $key => $value) {
//        if(preg_match('/^(\d)*$/',$key))
//        $update_query = $update_query . "uid=" . $key . " or ";
//    }
    //strip trailing 'or' and whitespace
//    $update_query = substr($update_query, 0, -4);
    //$system_message = $system_message . $update_query . "<BR>";
    //$update_result = mysql_query($update_query);
//    if(!$update_result) {
//        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
//        $system_message= $system_message . $error_message;
//        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
//    }
//}

//get the coordination of services for this student...
$medical_query="SELECT * FROM medical_info WHERE student_id=$student_id ORDER BY is_priority ASC, date DESC";

$medical_result = mysql_query($medical_query);
if(!$medical_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$medical_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

?> 
<?php print_html5_primer(); ?>
    <TITLE><?php echo $page_title; ?></TITLE>
<?php print_bootstrap_head(); ?>
<?php print_datepicker_depends(); ?>    
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
  
</HEAD>
    <BODY>
<?php print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']); ?>
<?php print_jumbotron_with_page_name("Medical Information", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission); ?>
<div class="container">       
 <?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>

 

                        <!-- BEGIN medical table -->
                        <form name="medicalinfo" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "medical_info.php"; ?>" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                        <table class="table table-striped table-hover">
                        
                        <?php
                        

                        //print the header row...
                        echo "<tr><th>Select</th><th>uid</th><th>Date</th><th>Description</th><th>In File</th><th>Priority</th><th>File</th></tr>\n";
                        while ($medical_row=mysql_fetch_array($medical_result)) { //current...
                            echo "<tr>\n";
                            echo "<td><input type=\"checkbox\" name=\"" . $medical_row['uid'] . "\"></td>";
                            echo "<td>" . $medical_row['uid'] . "</td>";
                            echo "<td><a href=\"" . IPP_PATH . "edit_medical_info.php?uid=" . $medical_row['uid'] . "\" class=\"editable_text\">" . $medical_row['date']  ."</a></td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_medical_info.php?uid=" . $medical_row['uid'] . "\" class=\"editable_text\">" . $medical_row['description']  ."</a></td>\n";
                            echo "<td><center><a href=\"" . IPP_PATH . "edit_medical_info.php?uid=" . $medical_row['uid'] . "\" class=\"editable_text\">" . $medical_row['copy_in_file'] . "</a></center></td>\n";
                            echo "<td><center><a href=\"" . IPP_PATH . "edit_medical_info.php?uid=" . $medical_row['uid'] . "\" class=\"editable_text\">"; if($medical_row['is_priority'] == "Y") echo "<img src=\"" . IPP_PATH . "images/caution.gif" . "\" border=\"0\">"; else echo "N"; echo "</a></center></td>\n";
                            echo "<td>"; if($medical_row['filename'] =="") echo "-none-"; else echo "<a href=\"" . IPP_PATH . "get_attached.php?table=medical_info&uid=" . $medical_row['uid'] ."&student_id=" . $student_id ."\">Download</a>"; echo "</td>\n";
                            echo "</tr>\n";
                            
                        }
                        ?>
                        </table>
                        
                        <table>
                        <tr>
                        <td nowrap>
                        <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
                        </td>
                        <td>
                             <?php
                               if($permission_level <= $IPP_MIN_DELETE_MEDICAL_INFO && $have_write_permission) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"1\">";
                                }
                             ?>
                             </td>
                             </tr>
                             </table>
                          </form>
                        <!-- end medical table -->

                        
<!-- BEGIN add new entry -->
<h2>Add a New Entry</h2>
<form name="add_medical_info" enctype="multipart/form-data" action="<?php echo IPP_PATH . "medical_info.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
<input type="hidden" name="add_medical_info" value="1">
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
<div class=form-group">                          
<label>Date (YYYY-MM-DD)</label>
<input class="form-control datepicker" data-date-format="yyyy-mm-dd" type="datepicker" id="datepicker" tabindex="1" name="date" value="<?php if(isset($_POST['date'])) echo $_POST['date']; ?>">
</div>
<p>
<label>Optional File Upload (.doc,.pdf,.txt,.rtf)</label>
<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
<input type="file" tabindex="2" name="supporting_file" value="<?php if(isset($_FILES['supporting_file']['name'])) echo $_FILES['supporting_file']['name'] ?>">
</p>

<div class="form-group">
<label>Description</label>
<textarea class="form-control" spellcheck="true" name="description" tabindex="5" cols="30" rows="3" wrap="SOFT"><?php  if(isset($_POST['description'])) echo $_POST['description']; ?></textarea>
</div>

<p>
<label>Report in File</label>
<input type="checkbox" tabindex="3" name="report_in_file" <?php if(isset($_POST['report_in_file']) && $_POST['report_in_file']) echo "checked";?>>
</p>


                       
<p>
<label>Priority Entry</label>
<input type="checkbox" tabindex="4" name="is_priority" <?php if(isset($_POST['is_priority']) && $_POST['is_priority']) echo "checked";?>>
</p>
<button type="submit" name="add" value="add" class="btn btn-default">Add Entry</button>                       
</form>
                       
 <!-- END add new entry -->
                        
           
<?php print_complete_footer();?> 
        </div>
<?php print_bootstrap_js();?>
        
    </BODY>
</HTML>
