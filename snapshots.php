<?php

/** @file
 * @brief Stores PDF IEP of current moment for a student in the database.
 * @todo 
 * 1. comment on code. Understand db operations.
 * 2. Recreate UI/UX (theme)
 * 3. But eliminate tables.
 * 
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['student_id'] || $_PUT['student_id']
 *
 */

/**
 * Path for IPP required files.
 */

/**
 * Cleared to protect from external input.
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
require_once IPP_PATH . 'include/create_pdf.php';
require_once 'include/supporting_functions.php';

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
if(isset($_GET['student_id'])) $student_id= mysql_real_escape_string($_GET['student_id']);
if(isset($_POST['student_id'])) $student_id = mysql_real_escape_string($_POST['student_id']);

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

//check if we are modifying a student...
if($_POST['take_snapshot']) {
    $pdf=create_pdf($student_id);
    //$pdf->Output();
    //we add the entry.
    $insert_query = "INSERT INTO snapshot(student_id,date,file,filename) VALUES (" . mysql_real_escape_string($student_id) . ",NOW(),'" . mysql_real_escape_string($pdf->Output("ignored",'S')) . "','IPP-" . $student_row['first_name'] . " " . $student_row['last_name'] . " " . date("F-d-Y") . ".pdf')";
    $insert_result = mysql_query($insert_query);
     if(!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . substr($insert_query,0,100) . "[truncated]'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     }
}

//check if we are deleting some entries...
if($_GET['delete_x'] && $permission_level <= $IPP_MIN_DELETE_SNAPSHOT && ($our_permission == "ASSIGN" || $our_permission="ALL") ) {
    $delete_query = "DELETE FROM snapshot WHERE ";
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
    //$system_message = $system_message . $delete_query;
}

//get the coordination of services for this student...
$snapshot_query="SELECT * FROM snapshot WHERE student_id=$student_id ORDER BY date DESC";

$snapshot_result = mysql_query($snapshot_query);
if(!$snapshot_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$snapshot_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
print_html5_primer();
?> 
<HEAD>
<?php print_bootstrap_head(); ?>
    
    <!--script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script -->
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to delete the following:\n";
          var count = 0;
          form=document.snapshots;
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
    <?php 
    print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']);
    print_jumbotron_with_page_name("Snapshots", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission);
    ?>
    <div class="container">
    <?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>
    <div class="row">
 
 
        <div class="col-md-6"> 
                        
                        <!-- BEGIN add new entry -->
                        <h2>Add Snapshot</h2>
                        
                        <form name="add_snapshot" enctype="multipart/form-data" action="<?php echo IPP_PATH . "snapshots.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0">
                        <tr>
                          <td>
                           <input type="hidden" name="take_snapshot" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>
                        <tr>
                           <td valign="center" align="center" bgcolor="#E0E2F2" class="row_default"><center><button class="btn btn-md btn-success" type="submit" name="snapshot" value="Take Snapshot">Take Snapshot</button>
                           </center></td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END add new entry -->
</div>
    <div class="col-md-6"> 
                        <!-- BEGIN snapshot table -->
                        <h2>Snapshot History</h2>
                        <form name="snapshots" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "snapshots.php"; ?>" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                        
                        <table class="table table-striped table-hover" border="0" cellpadding="0" cellspacing="1">
                        <?php
                        

                        //print the header row...
                        echo "<tr><th>Select</th><th>uid</th><th>Snapshot Date and Time</th><th>File</th></tr>\n";
                        while ($snapshot_row=mysql_fetch_array($snapshot_result)) { //current...
                            echo "<tr>\n";
                            echo "<td><input type=\"checkbox\" name=\"" . $snapshot_row['uid'] . "\"></td>";
                            echo "<td>" . $snapshot_row['uid'] . "</td>";
                            echo "<td>" . $snapshot_row['date']  ."</td>\n";
                            echo "<td>"; if($snapshot_row['filename'] =="") echo "-error-"; else echo "<a href=\"" . IPP_PATH . "get_attached.php?table=snapshot&uid=" . $snapshot_row['uid'] ."&student_id=" . $student_id . "\">View <img src=\"" . IPP_PATH . "images/pdf.png" . "\" border=\"0\"></a>"; echo "</center></td>\n";
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
                          </td>
                        </tr>
                        </table></center>
                        </form>
                        <!-- end snapshot table -->

                        </div></div>
        </div>
 <footer><?php print_complete_footer(); ?></footer>
 <?php print_bootstrap_js(); ?>
 
    </BODY>
</HTML>
