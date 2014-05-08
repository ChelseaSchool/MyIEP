<?php

/** @file
 * @brief 	IEP access per user
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Filter input
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0; //super admin only currently



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

$user_id="";
if(isset($_GET['user_id'])) $stude_id= $_GET['student_id'];
if(isset($_POST['user_id'])) $user_id = $_POST['student_id'];


//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}


//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

if(isset($_POST['username'])) {
  $user_query = "SELECT support_list.*,student.first_name,student.last_name FROM support_list LEFT JOIN student ON support_list.student_id=student.student_id WHERE egps_username='" . mysql_real_escape_string($_POST['username']) . "' AND student.student_id IS NOT NULL ORDER BY student.first_name";
  $user_result = mysql_query($user_query);
  if(!$user_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$user_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } 
}

if(1==0 && isset($_POST['add_accomodation']) && $have_write_permission) {
  $retval=parse_submission();
    if($retval != null ) {
        $system_message .= $retval . "<BR>";
    } else { 
           $add_query = "INSERT INTO accomodation (student_id,accomodation,start_date,end_date,subject,file,filename) VALUES (" . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_POST['accomodation']) . "',NOW(),NULL,'" . mysql_real_escape_string($_POST['subject']) . "','$content','$fileName')";
           $add_result = mysql_query($add_query);
           if(!$add_result) {
              $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
              $system_message=$system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
             unset($_POST['accomodation']);
             unset($_POST['subject']);
           }
    //   }
    }
   $system_message = $system_message . $add_query . "<BR>";
}


/******************** popup chooser support function ******************/
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
        global $system_message;
        $acclist_query="SELECT DISTINCT egps_username FROM support_list WHERE 1 ORDER BY egps_username ASC";
        $acclist_result = mysql_query($acclist_query);
        if(!$acclist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$acclist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        } else {
            //call the function to create the javascript array...
            echo createJavaScript($acclist_result,"popuplist");
        }
    }
/************************ end popup chooser support funtion  ******************/

?> 
<?php print_html5_primer();?>
    <TITLE><?php echo $page_title; ?></TITLE>
        
    <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
    <script language="javascript" src="<?php echo IPP_PATH . "include/autocomplete.js"; ?>"></script>
    <script language="javascript" src="<?php echo IPP_PATH . "include/autocomplete.js"; ?>"></script>
    <?php
       //output the javascript array for the chooser popup
       echoJSServicesArray();
    ?>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "delete_supervisor=";
          var szConfirmMessage = "Are you sure you want to modify/delete IPP Access(s):\n";
          var count = 0;
          form=document.accomodationhistory;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + "ID #" + form.elements[x].name + "\n";
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
    <?php 
    print_general_navbar();
    print_lesser_jumbotron("User Audit", $permission_level);
    ?>
       
<div class="container">                
<?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                       
 <!-- BEGIN choose user -->
                       
<form name="chooseuser" enctype="multipart/form-data" action="<?php echo IPP_PATH . "user_audit.php"; ?>" method="post" >

                        
<h2>Find Users Support List</h2>
<div class="form-group">
<label>Username</label>
<input type="text" tabindex="1" name="username" size="40" maxsize="255" value="<?php echo $_POST['username']; ?>" onkeypress="return autocomplete(this,event,popuplist)">
</div>                 
<button class="btn btn-large btn-regular type="submit" tabindex="2" ="Check" value="Check">Check</button>
</form>
                        
<!-- END choose user -->

                        <!-- BEGIN audit table -->
                        <form name="accomodationhistory" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "accomodations.php"; ?>" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                        <table class="table table-striped table-hover">

                        <?php
                        
                        if (isset($_POST['username'])) echo "<center>" . mysql_real_escape_string($_POST['username']) . " is a support member to the following students</center><BR>";
                        //print the header row...
                        echo "<tr><th bgcolor=\"#F4EFCF\">&nbsp;</th><th align=\"center\" bgcolor=\"#F4EFCF\">Student Last Name</th><th align=\"center\" bgcolor=\"#F4EFCF\">Student First Name</th><th align=\"center\" bgcolor=\"#F4EFCF\">Permission</th><th align=\"center\" bgcolor=\"#F4EFCF\">Students Current School</th></tr>\n";
                        while (isset($user_result) && $user_row=mysql_fetch_array($user_result)) { //current...
                            //try to get this students current school...
                            $school_query = "SELECT * FROM school_history where student_id=" . $user_row['student_id'] . " AND end_date IS NULL";
                            $school_result = mysql_query($school_query);
                            if(!$school_result) {
                                 $school_row =  array("school_name" => "error");
                                 $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
                                 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                            } else {if(!mysql_num_rows($school_result)) $school_row=array("school_name" => "-archived student-"); else $school_row=mysql_fetch_array($school_result);}

                            echo "<tr>\n";
                            echo "<td bgcolor=\"#F4EFCF\"><input type=\"checkbox\" name=\"" . $user_row['uid'] . "\"></td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "student_view.php?student_id=" . $user_row['student_id'] . "\" class=\"editable_text\">" . $user_row['last_name']  ."</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "student_view.php?student_id=" . $user_row['student_id'] . "\" class=\"editable_text\">" . $user_row['first_name'] . "</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><a href=\"" . IPP_PATH . "modify_ipp_permission.php?student_id=" . $user_row['student_id'] . "\" class=\"editable_text\">" . $user_row['permission'] . "</a></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><center>" . $school_row['school_name'] . "</center></td>";
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }
                        ?>
                        <tr>
                          <td colspan="6" align="left">
                             <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected: 
                             </td>
                             <td>
                             <?php
                                if($have_write_permission) {
                                    echo "<INPUT NAME=\"set_not_active\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=End\" border=\"0\" value=\"set_not_active\">";
                                    echo "<INPUT NAME=\"set_continue\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Continue\" border=\"0\" value=\"set_continue\">";
                                }
                                //if we have permissions also allow delete and set all.
                                if($permission_level <= $IPP_MIN_DELETE_ACCOMODATION && $have_write_permission) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"delete\">";
                                }
                             ?>
                             </td>
                             </tr>
                             </table>
                          </td>
                        </tr>
                        </table></center>
                        </form>
                        <!-- end audit table -->

                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center">
            </td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
        <center></center>
        <?php print_bootstrap_js();?>
    </BODY>
</HTML>
