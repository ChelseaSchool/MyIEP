<?php
/** @file
 * @brief 	add or edit address information
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		Filter input
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;    //everybody (do checks within document)

/**
 * INPUTS: address_id (optional if editing)
 *         target (guardian, school, ??)
 *                then included: guardian_id= ,school_id=  matching target
 *         retpage (the return page including get variables)

 */

/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message="";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/navbar.php');

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

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}


function parse_submission() {
    if(!$_GET['first_name']) return "You must supply a first name<BR>";
    if(!$_GET['last_name']) return "You must supply a last name<BR>";

    return NULL;
}

$student_id=$_GET['student_id'];

//get the required info from the listed target...
function runQuery() {

  global $target_result;

  if(isset($_GET['target'])) {
    $target_query="";
    switch($_GET['target']) {
        case "guardian":
            if(!isset($_GET['guardian_id'])) {
               $system_message = $system_message . "You have arrived at this page without supplying a valid guardian_id (" . __FILE__ . ":" . __LINE__ . ")<BR>";
               IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            } else {
               $target_query="SELECT guardian.*,guardian.first_name as guardian_first_name,guardian.last_name as guardian_last_name,guardians.*,student.first_name,student.last_name,address.* FROM guardian LEFT JOIN guardians ON guardian.guardian_id=guardians.guardian_id LEFT JOIN student ON guardians.student_id=student.student_id LEFT JOIN address ON guardian.address_id=address.address_id WHERE guardian.guardian_id=" . $_GET['guardian_id'];
            }
        break;
    }
    $target_result=mysql_query($target_query);
    if(!$target_result) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$target_query'<BR>";
        $system_message=$system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
  }
}

runQuery();

//check permissions if necessary...
$have_write_permission = false;
switch($_GET['target']) {
    case "guardian":
        while($guardian_row=mysql_fetch_array($target_result)) {
            $our_permission = getStudentPermission($guardian_row['student_id']);
            if($our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission != "ALL") {
                //we don't have permission...
                //do nothing.
            } else {
                $have_write_permission = true;
            }
        }
    break;
}

if(!$have_write_permission) {
            $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            require(IPP_PATH . 'security_error.php');
            exit();
}

//reset the mysql table pointer
mysql_data_seek($target_result,0);
$target_row=mysql_fetch_array($target_result);

//check if we are updating...
$address_id = $target_row['address_id'];
if(isset($_GET['update'])) {
    //check if we don't have an address id...if no we add a new one
    //and update guardian...
    if($address_id == '') {
        $create_address_query = "INSERT INTO address (city) VALUES ('')";
        $create_address_result = mysql_query($create_address_query);
        if(!$create_address_result) {
             $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$create_address_query'<BR>";
             $system_message=$system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        } else {
            $address_id = mysql_insert_id();
            $update_guardian_query = "UPDATE guardian SET address_id = " . $address_id . " WHERE guardian_id=" . $target_row['guardian_id'];
            $update_guardian_result = mysql_query($update_guardian_query);
            if(!$update_guardian_result) {
                 $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_guardian_query'<BR>";
                 $system_message=$system_message . $error_message;
                 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            }

        }
    }
    $update_query = "UPDATE address SET po_box='" . mysql_real_escape_string($_GET['po_box']) . "',street='" . mysql_real_escape_string($_GET['street']) . "',city='" . mysql_real_escape_string($_GET['city']) . "',province='" . mysql_real_escape_string($_GET['province']) . "',country='" . mysql_real_escape_string($_GET['country']) . "',postal_code='" . mysql_real_escape_string($_GET['postal_code']) . "',home_ph='" . mysql_real_escape_string($_GET['home_ph']) . "',business_ph='" . mysql_real_escape_string($_GET['business_ph']) . "',cell_ph='" . mysql_real_escape_string($_GET['cell_ph']) . "',email_address='" . mysql_real_escape_string($_GET['email_address']) . "' WHERE address_id=" . mysql_real_escape_string($address_id);
    $update_result = mysql_query($update_query);
    if(!$update_result) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
        $system_message=$system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
        //add a log entry
        //TO BE DONE!!
        //$system_message=$system_message . "query: '$update_query'<BR>";
        //redirect to relevant location...
        switch($_GET['target']) {
            case "guardian":
                //we need to update the guardian names...
                $update2_query = "UPDATE guardian SET last_name='" . mysql_real_escape_string($_GET['guardian_last_name']) . "',first_name='" . mysql_real_escape_string($_GET['guardian_first_name']) . "' WHERE guardian_id='" . $target_row['guardian_id'] . "' LIMIT 1";
                $update2_result = mysql_query($update2_query);
                if(!$update2_result) {
                  $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update2_query'<BR>";
                  $system_message=$system_message . $error_message;
                  IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                } else {
                 header("Location: guardian_view.php?student_id=" . $_GET['student_id']);
                 //exit();
                 //$system_message=$system_message . "query1: '$update_query'<BR><BR>";
                 //$system_message=$system_message . "query2: '$update2_query'<BR>";
                }
            break;
        }
    }


}

//reset the mysql table pointer
mysql_data_seek($target_result,0);


?> 

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
    
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }
    </SCRIPT>
</HEAD>
<BODY>
        <table class="shadow" border="0" cellspacing="0" cellpadding="0" align="center">  
        <tr>
          <td class="shadow-topLeft"></td>
            <td class="shadow-top"></td>
            <td class="shadow-topRight"></td>
        </tr>
        <tr>
            <td class="shadow-left"></td>
            <td class="shadow-center" valign="top">
                <table class="frame" width=620px align=center border="0">
                    <tr align="Center">
                    <td><center><img src="<?php echo $page_logo_path; ?>"></center></td>
                    </tr>
                    <tr><td>
                    <center><?php navbar("guardian_view.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center>
                        <table width="80%" cellspacing="0" cellpadding="0"><tr><td><center><p class="header">- Edit Address -</p></center></td></tr><tr><td><center><p class="bold_text">
                        <?php
                            switch($_GET['target']) {
                               case "guardian":
                                   $target_row=mysql_fetch_array($target_result);
                                   echo "Guardian: " . $target_row['guardian_last_name'] . "," . $target_row['guardian_first_name'] . "<BR>";
                                   echo "(Guardian for " . $target_row['first_name'] . " " . $target_row['last_name'] . ")<BR>";
                                   while($target_row=mysql_fetch_array($target_result)) {
                                       echo "(for " . $target_row['first_name'] . " " . $target_row['last_name'] . ")<BR>";
                                   }
                                   //mysql pointer back to first row
                                   mysql_data_seek($target_result,0);
                                   $target_row=mysql_fetch_array($target_result);
                               break;
                            }
                        ?></p></center></td></tr></table>
                        </center>
                        <BR>
                        <center>
                        <form name="editAddress" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_address.php"; ?>" method="get">
                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                        <tr>
                          <td colspan="2">
                          <p class="info_text">Fill out and click 'Update'.</p>
                          <input type="hidden" name="target" value="<?php echo $_GET['target']; ?>">
                          <input type="hidden" name="guardian_id" value="<?php echo $_GET['guardian_id']; ?>">
                          <input type="hidden" name="student_id" value="<?php echo $_GET['student_id']; ?>">
                          </td>
                        </tr>
                        <?php
                        if($_GET['target'] == "guardian") {
                          echo "<tr>";
                          echo "<td bgcolor=\"#E0E2F2\" align=\"left\">Guardian First Name(s):</td>";
                          echo "<td bgcolor=\"#E0E2F2\">";
                          echo "<input type=\"text\" tabindex=\"1\" name=\"guardian_first_name\" size=\"30\" maxsize=\"254\" value=\"" . $target_row['guardian_first_name']  . "\">";
                          echo "</td>";
                          echo "</tr>";
                          echo "<tr>";
                          echo "<td bgcolor=\"#E0E2F2\" align=\"left\">Guardian Last Name:</td>";
                          echo "<td bgcolor=\"#E0E2F2\">";
                          echo "<input type=\"text\" tabindex=\"2\" name=\"guardian_last_name\" size=\"30\" maxsize=\"254\" value=\"" . $target_row['guardian_last_name']  . "\">";
                          echo "</td>";
                          echo "</tr>";
                          echo "<tr>";
                          echo "<td valign=\"bottom\" align=\"center\" bgcolor=\"#E0E2F2\" colspan=\"2\">&nbsp;</td>";
                          echo "</tr>";
                        }
                        ?>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Address 1 (P.O. Box):</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="3" name="po_box" size="30" maxsize="125" value="<?php if(isset($target_row['po_box'])) echo $target_row['po_box']; else if(isset($_GET['po_box'])) echo $_GET['po_box'];?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Street:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="4" name="street" size="30" maxsize="125" value="<?php if(isset($target_row['street'])) echo $target_row['street']; else if(isset($_GET['street'])) echo $_GET['street']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">City:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="5" name="city" size="30" maxsize="125" value="<?php if(isset($target_row['city'])) echo $target_row['city']; else if(isset($_GET['city'])) echo $_GET['city']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Province:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="6" name="province" size="30" maxsize="125" value="<?php if(isset($target_row['province'])) echo $target_row['province']; else if(isset($_GET['province'])) echo $_GET['province']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Country:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="7" name="country" size="30" maxsize="125" value="<?php if(isset($target_row['country'])) echo $target_row['country']; else if(isset($_GET['country'])) echo $_GET['country']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Postal Code:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="8" name="postal_code" size="30" maxsize="125" value="<?php if(isset($target_row['postal_code'])) echo $target_row['postal_code']; else if(isset($_GET['postal_code'])) echo $_GET['postal_code']; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Home Phone:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="9" name="home_ph" size="30" maxsize="125" value="<?php if(isset($target_row['home_ph'])) echo $target_row['home_ph']; else if(isset($_GET['home_ph'])) echo $_GET['home_ph']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Business Phone:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="10" name="business_ph" size="30" maxsize="125" value="<?php if(isset($target_row['business_ph'])) echo $target_row['business_ph']; else if(isset($_GET['business_ph'])) echo $_GET['business_ph']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Cell Phone:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="11" name="cell_ph" size="30" maxsize="125" value="<?php if(isset($target_row['cell_ph'])) echo $target_row['cell_ph']; else if(isset($_GET['cell_ph'])) echo $_GET['cell_ph']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Email Address:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="12" name="email_address" size="30" maxsize="125" value="<?php if(isset($target_row['email_address'])) echo $target_row['email_address']; else if(isset($_GET['email_address'])) echo $_GET['email_address']; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;&nbsp;<input tabindex="13" name="update" type="submit" value="Update"></td>
                        </tr>
                        </table>
                        </form>
                        </center>

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
            <?php navbar("guardian_view.php?student_id=$student_id"); ?>
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
    </BODY>
</HTML>
