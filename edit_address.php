<?php
/** @file
 * @brief 	add or edit address information
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		
 * 1. Filter input
 * 2. Nielson left unfinished functionality in comments
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
mysql_data_seek($target_result, 0);

print_html5_primer();
print_bootstrap_head();
?> 



    

    
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }
    </SCRIPT>
</HEAD>
<BODY>
<?php 
print_student_navbar($student_id, $target_row['first_name'] . " " . $target_row['last_name']);
print_jumbotron_with_page_name("Edit Contact Information", $target_row['first_name'] . " " . $target_row['last_name'], $our_permission);
?>       
                    
<div class="container">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        
                        <h2>
                        <?php
                            switch($_GET['target']) {
                               case "guardian":
                                   $target_row=mysql_fetch_array($target_result);
                                   echo "Guardian: " . $target_row['guardian_last_name'] . ", " . $target_row['guardian_first_name'] . " <small>";
                                   echo "(Guardian for " . $target_row['first_name'] . " " . $target_row['last_name'] . ")</small></h2>" ;
                                   while($target_row=mysql_fetch_array($target_result)) {
                                       echo "(for " . $target_row['first_name'] . " " . $target_row['last_name'] . ") ";
                                   }
                                   //mysql pointer back to first row
                                   mysql_data_seek($target_result,0);
                                   $target_row=mysql_fetch_array($target_result);
                               break;
                            }
                        ?>
                        </h2>
                        
                        
                        <h4>Fill out and click "Submit" to revise contact information.</h4>
                        <form name="editAddress" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_address.php"; ?>" method="get">
                          <input type="hidden" name="target" value="<?php echo $_GET['target']; ?>">
                          <input type="hidden" name="guardian_id" value="<?php echo $_GET['guardian_id']; ?>">
                          <input type="hidden" name="student_id" value="<?php echo $_GET['student_id']; ?>">
                          </td>
                        </tr>
                        <?php
                        if($_GET['target'] == "guardian") {
                          echo "<div class=\"form-group\">";
                          echo "<label for guardian_first_name>Guardian First Name(s)</label>";
                          echo "<input required class=\"form-control\" type=\"text\" autocomplete=\"off\" tabindex=\"1\" name=\"guardian_first_name\" value=\"" . $target_row['guardian_first_name']  . "\">";

                          echo "<label>Guardian Last Name</label>";

                          echo "<input required class=\"form-control\" type=\"text\" tabindex=\"2\" name=\"guardian_last_name\" value=\"" . $target_row['guardian_last_name']  . "\">";
                    
                        }
                        ?>
                        
                          <label for po_box>Address 1 (P.O. Box)</label>
                            <input class="form-control" type="text" tabindex="3" name="po_box" size="30" maxsize="125" value="<?php if(isset($target_row['po_box'])) echo $target_row['po_box']; else if(isset($_GET['po_box'])) echo $_GET['po_box'];?>">
                          
                          <label>Street</label>
                            <input class="form-control" type="text" tabindex="4" name="street" size="30" maxsize="125" value="<?php if(isset($target_row['street'])) echo $target_row['street']; else if(isset($_GET['street'])) echo $_GET['street']; ?>">
                       
                         <label>City</label></td>
                        
                            <input class="form-control" type="text" tabindex="5" name="city" size="30" maxsize="125" value="<?php if(isset($target_row['city'])) echo $target_row['city']; else if(isset($_GET['city'])) echo $_GET['city']; ?>">
                          
                        <label>Province or Burrough</label>
                         <input class="form-control" type="text" tabindex="6" name="province" size="30" maxsize="125" value="<?php if(isset($target_row['province'])) echo $target_row['province']; else if(isset($_GET['province'])) echo $_GET['province']; ?>">
                          
                         <label>Country</label>
                          
                            <input type="text" class="form-control" tabindex="7" name="country" size="30" maxsize="125" value="<?php if(isset($target_row['country'])) echo $target_row['country']; else if(isset($_GET['country'])) echo $_GET['country']; ?>">
                          
                          <label>Postal Code</label>
                          
                            <input class="form-control" type="text" tabindex="8" name="postal_code" size="30" maxsize="125" value="<?php if(isset($target_row['postal_code'])) echo $target_row['postal_code']; else if(isset($_GET['postal_code'])) echo $_GET['postal_code']; ?>">
                          
                            
                       <label>Home Phone</label>
                        
                            <input class="form-control" type="tel" tabindex="9" name="home_ph" size="30" maxsize="125" value="<?php if(isset($target_row['home_ph'])) echo $target_row['home_ph']; else if(isset($_GET['home_ph'])) echo $_GET['home_ph']; ?>">
                          
                          <label>Business Phone</label>
                         
                            <input class="form-control" type="tel" tabindex="10" name="business_ph" size="30" maxsize="125" value="<?php if(isset($target_row['business_ph'])) echo $target_row['business_ph']; else if(isset($_GET['business_ph'])) echo $_GET['business_ph']; ?>">
                          
                          <label>Cell Phone</label>
                          
                            <input class="form-control" type="tel" tabindex="11" name="cell_ph" size="30" maxsize="125" value="<?php if(isset($target_row['cell_ph'])) echo $target_row['cell_ph']; else if(isset($_GET['cell_ph'])) echo $_GET['cell_ph']; ?>">
                          
                          <label>Email Address</label>
                   
                            <input class="form-control" type="email" tabindex="12" name="email_address" size="30" maxsize="125" value="<?php if(isset($target_row['email_address'])) echo $target_row['email_address']; else if(isset($_GET['email_address'])) echo $_GET['email_address']; ?>">
                            </div>
                            <p><button class="btn btn-primary" tabindex="13" name="update" type="submit" value="Update">Submit</button></p>
                        
                        </form>

                        </div>
                       
         
            
              
       
           
           
            
        <footer><?php print_bootstrap_js(); ?></footer>
    </BODY>
</HTML>
