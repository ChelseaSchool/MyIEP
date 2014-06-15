<?php

/** @file
 * @brief 	assistive technology perscribed by IEP
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		Filter input
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUT: $_GET['student_id']
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

//function asc2hex ($temp) {
//   $len = strlen($temp);
//   for ($i=0; $i<$len; $i++) $data.=sprintf("%02x",ord(substr($temp,$i,1)));
//   return $data;
//}


//check if we are modifying a student...
if(isset($_POST['add_asst_tech']) && $have_write_permission) {
  if($_POST['technology'] == '') {
    $system_message = $system_message . "You must supply information on the techology<BR>";
  } else {
    //we add the entry.
    $insert_query = "INSERT INTO assistive_technology (student_id,technology) VALUES (" . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_POST['technology']) . "')";
     $insert_result = mysql_query($insert_query);
     if(!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . substr($insert_query,0,200) . "[truncated]'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
       unset($_POST['technology']);
     }
     //$system_message = $system_message . $insert_query . "<BR>";
  }
}

//check if we are deleting some entries...
if(isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_ASSISTIVE_TECHNOLOGY && $have_write_permission ) {
    $delete_query = "DELETE FROM assistive_technology WHERE ";
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


//get the coordination of services for this student...
$asst_tech_query="SELECT * FROM assistive_technology WHERE student_id=$student_id";
$asst_tech_result = mysql_query($asst_tech_query);
if(!$asst_tech_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$asst_tech_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
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

          $returns=array("\n", "\r","\t");
          $tempOutput.=str_replace($returns," ",$row[0]) . ' ';

          $javascript.=trim($tempOutput).'";';
        }
      }
      $javascript.='</script><!--End popup array-->'."\n";

      // return JavaScript code
      return $javascript;
    }

    function echoJSServicesArray() {
        global $system_message;
        $techlist_query="SELECT DISTINCT `technology`, COUNT(`technology`) AS `count` FROM assistive_technology GROUP BY `technology` ORDER BY `count` DESC LIMIT 200";
        $techlist_result = mysql_query($techlist_query);
        if(!$techlist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$techlist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        } else {
            //call the function to create the javascript array...
            if(mysql_num_rows($techlist_result)) echo createJavaScript($techlist_result,"popuplist");
        }
    }
/************************ end popup chooser support funtion  ******************/

?>
<?php print_html5_primer(); ?>
   
<TITLE><?php echo $page_title; ?></TITLE>
<?php print_bootstrap_head(); ?>
    
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
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
          form=document.testing;
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
<?php print_jumbotron_with_page_name("Assistive Technology", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission); ?>

<div class="container">
<?php if ($system_message) { echo  $system_message ;}; ?>
    
<h2>Assistive Technologies <small>Scroll Down to Add Technologies</small></h2>
<!-- BEGIN assistive tech table-->

<form name="at" spellcheck="true" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "assistive_technology.php"; ?>" method="get">
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
<table class="table table-striped table-hover">

<?php


//print the header row...

echo "<tr><th>&nbsp;</th><th>uid</th><th>Technology</th></tr>\n";
//loop through current assistive technologies
while ($asst_tech_row=mysql_fetch_array($asst_tech_result)) { 
        echo "<tr>\n";
		echo "<td><input type=\"checkbox\" name=\"" . $asst_tech_row['uid'] . "\"></td>";
		echo "<td>" . $asst_tech_row['uid'] . "</td>";
	    echo "<td><a href=\"" . IPP_PATH . "edit_assistive_technology.php?uid=" . $asst_tech_row['uid'] . "\" class=\"editable_text\">" . mysql_real_escape_string($asst_tech_row['technology'])  ."</a></td>\n";
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
//if we have permissions also allow delete and set all.
if($permission_level <= $IPP_MIN_DELETE_ASSISTIVE_TECHNOLOGY && $have_write_permission) {
	echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"1\">";
}
?>
</td>
</tr>
</table>
</form>
<!-- end assistive tec table -->              
                   
                    
 <!-- BEGIN add new entry -->
<h2>Add New Assistive Technology</h2>                 
<form name="add_asst_tech" enctype="multipart/form-data" action="<?php echo IPP_PATH . "assistive_technology.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\""; ?>>                    
<input type="hidden" name="add_asst_tech" value="1">
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
<div class="form-group">                          
<label>Technology</label>
<textarea class="form-control" spellcheck="true" name="technology" tabindex="1" cols="30" rows="5" wrap="soft" onkeypress="return autocomplete(this,event,popuplist)"><?php if(isset($_POST['technology'])) echo $_POST['technology']; ?></textarea></td>

<button type="submit" name="add" value="add" class="btn btn-default">Add Assistive Technology</button>
</div>
</form>
                       
<!-- END add new entry -->



                       
                   
     
       
         
<?php print_complete_footer();?>
</div>
<?php print_bootstrap_js();?>
</BODY>
</HTML>