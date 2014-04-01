<?php

/** @file
 * @brief 	manage authorized users
 * 
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;  //all, decide in the page

/**
 * superuser_manage_users.php -- IPP manage users main menu
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 * All rights reserved
 *
 * Created: June 06, 2005
 * By: M. Nielsen
 * Modified: June 01, 2006
 *
 */

/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message ="";
if(isset($_GET['field'])) $FIELD = $_GET['field']; ELSE $FIELD="";
if(isset($_GET['szSearchVal'])) $szSearchVal=$_GET['szSearchVal']; else $szSearchVal="";

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
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE****************
$permission_level=getPermissionLevel($_SESSION['egps_username']);
//check permission levels
if($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//check if we are duplicating...

if(isset($_POST['duplicate_x'])) {
   //we can only duplicate one so...
   $count=0;
   foreach($_POST as $key => $value) {
        if(preg_match('/^(\d)*$/',$key)) {
           $id=$key;
           $count++;
        }
   }
   if($count > 1) $system_message = "You can only duplicate one program plan at a time<BR>";
   else header("Location: " . IPP_PATH . "duplicate.php?student_id=" . $id);

}

//check if we are deleting some peeps...
//print_r ($_POST);

if(isset($_POST['delete_x'])) {
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    $delete_query = "DELETE FROM student WHERE ";
    foreach($_POST as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "student_id=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //echo $delete_query . "<-><BR>";
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
}

  //get the list of all users...
  //wonder how php handles dangling else...
  if(!isset($_GET['iLimit']))
    if(!isset($_POST['iLimit'])) $iLimit = 50;
        else $iLimit=$_POST['iLimit'];
  else $iLimit = $_GET['iLimit'];

  if(!isset($_GET['iCur']))
    if(!isset($_POST['iCur'])) $iCur = 0;
    else $iCur=$_POST['iCur'];
  else $iCur = $_GET['iCur'];

  if(!isset($_GET['szSchool']))
    if(!isset($_POST['szSchool'])) $szSchool = "ALL";
    else $szSchool=$_POST['szSchool'];
  else $szSchool = $_GET['szSchool'];

$szTotal=0;

/** @fn 	getStudents()
 *  @brief	Gets a count of students from the database that go to a member
 *  @return NULL|resource
 *  @todo	get_student_count()
 */

function getStudents() {
    global $error_message,$IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS,$permission_level,$system_message,$IPP_MIN_VIEW_LIST_ALL_STUDENTS,$iLimit,$iCur,$szSchool,$szTotalStudents;
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    //do a subquery to find our school code...easier than messing with the ugly
    //query below...
    $school_code_query="SELECT school_code FROM support_member WHERE egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "'";
    $school_code_result=mysql_query($school_code_query);
    if(!$school_code_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_code_query'<BR>";
        return NULL;
    }
    $school_code_row=mysql_fetch_array($school_code_result);
    $school_code= $school_code_row['school_code'];

    //$student_query = "SELECT student.student_id,last_name,first_name,school_history.school_code,school.* FROM student LEFT JOIN school_history ON student.student_id=school_history.student_id LEFT JOIN school ON school_history.school_code=school.school_code WHERE end_date IS NULL ";
    $student_query = "SELECT DISTINCT student.student_id,last_name,first_name,school_history.school_code,school.* FROM student LEFT JOIN support_list ON student.student_id = support_list.student_id LEFT JOIN school_history ON student.student_id=school_history.student_id LEFT JOIN school ON school_history.school_code=school.school_code WHERE ((support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND school_history.end_date IS NULL AND support_list.student_id IS NOT NULL) OR (";
    //prior to march 18/06: $student_query = "SELECT DISTINCT student.student_id,last_name,first_name,school_history.school_code,school.* FROM student LEFT JOIN support_list ON student.student_id = support_list.student_id LEFT JOIN school_history ON student.student_id=school_history.student_id LEFT JOIN school ON school_history.school_code=school.school_code WHERE (support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND support_list.student_id IS NOT NULL) OR (";
    
    if(!($IPP_MIN_VIEW_LIST_ALL_STUDENTS >= $permission_level)) { //$IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS >= $permission_level) {
          $student_query = $student_query . "school_history.school_code='$school_code' AND "; //prior to 2006-03-21: $student_query = $student_query . "school_history.school_code='$school_code' AND ";
      if($IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS < $permission_level)
        {
          //$system_message .= "debug: permission level: $IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS < $permission_level<BR><BR>";
          $student_query .= "support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND ";
        }
          $student_query .= "end_date IS NULL) ";
    } else {
        $student_query = $student_query . "end_date IS NULL) ";
    }
    if(isset($_GET['SEARCH'])) {
        switch ($_GET['field']) {
           case 'last_name':
               $student_query = $student_query . "AND student.last_name LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
           break;
           case 'first_name':
               $student_query = $student_query . "AND student.first_name LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
           break;
           case 'last_name':
               $student_query = $student_query . "AND student.last_name LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
           break;
           case 'school_name':
               $student_query = $student_query . "AND school.school_name LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
           break;
           case 'school_code':
               $student_query = $student_query . "AND school_history.school_code LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
        
        }
    }
    //added 2006-04-20: to prevent null school histories from showing up as active.
    $student_query .= ") AND EXISTS (SELECT school_history.student_id FROM school_history WHERE school_history.student_id=student.student_ID) ";
    //end added 2006-04-20
    $student_query_limit = $student_query . "ORDER BY school_history.school_code,student.last_name ASC LIMIT $iCur,$iLimit";
    $student_result_limit = mysql_query($student_query_limit);
    if(!$student_result_limit) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query_limit'<BR>";
        return NULL;
    }

    //$system_message = $system_message . "debug: " . $student_query_limit . "<BR>";

    //find the totals...
    $student_result_total = mysql_query($student_query);
    if(!$student_result_total) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
        return NULL;
    }
    $szTotalStudents =  mysql_num_rows($student_result_total);
    return $student_result_limit;
}

$sqlStudents=getStudents(); //$szTotalStudents contains total number of stdnts.

//get totals...

if(!$sqlStudents) {
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}


//set back vars...
$szBackGetVars="";
foreach($_GET as $key => $value) {
    $szBackGetVars = $szBackGetVars . $key . "=" . $value . "&";
}
//strip trailing '&'
$szBackGetVars = substr($szBackGetVars, 0, -1);


?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
   

    <SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to delete or duplicate:\n";
          var count = 0;
          form=document.studentlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + form.elements[x].value + " (ID #" + form.elements[x].name + ")\n";
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

      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permissions"); return false;
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
                    <center><?php navbar("main.php"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">-Students-</p></center></td></tr></table></center>

                        <center><table width="80%" border="0"><tr>
                          <td align="center">
                          <?php echo "<a href=\"" . IPP_PATH . "new_student.php?iLimit=$iLimit&iCur=$iCur&field=" . $FIELD . "&szSearchVal=" . $szSearchVal . "&SEARCH=1\"><img src=\"" . IPP_PATH  . "images/mainbutton.php?title=Add Student\" border=0 ";
                          if($permission_level > 50) echo "onClick=\"return noPermission();\"";  //Teachers and up only!
                          echo ">\n";
                          ?>
                          </td>
                        </tr>
                        </table></center>
                        <HR>

                        <!-- search fx >
                        <form enctype="multipart/form-data" action="<?php echo IPP_PATH . "manage_student.php"; ?>" method="get">
                        <center><table width="80%" cellspacing="0">
                        <tr>
                        <td align=center bgcolor="#E0E2F2">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align=center bgcolor="#E0E2F2">
                            Search:&nbsp;
                            <SELECT name="field">
                            <option value="last_name" <?php if($FIELD == "last_name") echo "selected"; ?>>Last Name
                            <option value="first_name" <?php if($FIELD == "first_name") echo "selected"; ?>>First Name
                            <option value="school_name" <?php if($FIELD == "school_name") echo "selected"; ?>>School Name
                            <option value="school_code" <?php if($FIELD == "school_code") echo "selected"; ?>>School Code
                            </SELECT>
                            &nbsp;is&nbsp;&nbsp;<input type="text" name="szSearchVal" size="15" value="<?php echo $szSearchVal;?>">&nbsp;Limit:&nbsp;<input type="text" name="iLimit" size="5" value="<?php echo $iLimit; ?>">&nbsp;<input type="submit" value="Query" name="SEARCH">
                            <p class="small_text">(Wildcards: '%'=match any '_'=match single)</p>
                        </td>
                        </tr></table></center>
                        </form>
                        <-- end search fx -->


                        <form name="studentlist" onSubmit="return deleteChecked()" enctype="multipart/form-data" action="<?php echo IPP_PATH . "manage_student.php"; ?>" method="post">
                        <center><table width="80%" border="0">
                        <?php
                        $bgcolor = "#DFDFDF";

                        //print the next and prev links...
                        echo "<tr><td colspan=\"2\">";
                        if($iCur != 0) {
                            //we have previous values...
                            echo "<a href=\"./manage_student.php?iCur=" . ($iCur-$iLimit) . "&iLimit=$iLimit&szSearch=&szSearchVal=" . $_GET['szSearchVal'] . "&field=" . $_GET['field'] . "&SEARCH=" . $_GET['SEARCH'] . "\" class=\"default\">prev $iLimit</a>";
                        } else {
                            echo "&nbsp;";
                        }
                        echo "</td><td colspan=\"2\" align=\"center\">";
                        echo "Click Username to view";
                        echo "</td>";
                        if(($iLimit+$iCur < $szTotalStudents)) {
                            echo "<td align=\"right\"><a href=\"./manage_student.php?iCur=" . ($iCur+$iLimit) . "&iLimit=$iLimit&szSearchVal=" . $_GET['szSearchVal'] . "&field=" . $_GET['field'] . "&SEARCH=" . $_GET['SEARCH'] . "\" class=\"default\">next ";
                            if( $szTotalStudents-($iCur+$iLimit) > $iLimit) {
                                echo $iLimit . "</td>";
                            } else {
                                echo ($szTotalStudents-($iCur+$iLimit)) . "</td>";
                            }
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                        echo "</tr>\n";
                        //end print next and prev links

                        //print the header row...
                        echo "<tr><td bgcolor=\"#E0E2F2\">&nbsp;</td><td align=\"center\" bgcolor=\"#E0E2F2\">UID</td><td align=\"center\" bgcolor=\"#E0E2F2\">Last Name, First Name</td><td align=\"center\" bgcolor=\"#E0E2F2\">School</td><td align=\"center\" bgcolor=\"#E0E2F2\">Permission</td></tr>\n";
                        while ($student_row=mysql_fetch_array($sqlStudents)) {
                            $current_student_permission = getStudentPermission($student_row['student_id']);
                            echo "<tr>\n";
                            $school_colour = "#". $student_row['red'] . $student_row['green'] . $student_row['blue'];
                            echo "<td bgcolor=\"$school_colour\"><input type=\"checkbox\" name=\"" . $student_row['student_id'] . "\" value=\"" . $student_row['first_name'] . " " . $student_row['last_name'] . "\"></td>";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\">" . $student_row['student_id'] . "<p></td>\n";
                            echo "<td bgcolor=\"$bgcolor\"><a href=\"" . IPP_PATH . "student_view.php?student_id=" . $student_row['student_id'] . "\" class=\"default\" ";
                            if($current_student_permission == "NONE" || $current_student_permission == "ERROR") {
                                echo "onClick=\"return noPermission();\" ";
                            }
                            echo ">" .  $student_row['last_name'] . "," . $student_row['first_name'] . "</a>";
                            if($current_student_permission == "READ" || $current_student_permission != "WRITE" || $current_student_permission != "ALL") {
                                echo "<a href=\"". IPP_PATH . "ipp_pdf.php?student_id=" . $student_row['student_id'] . "\" class=\"default\" target=\"_blank\"";
                                if($current_student_permission == "NONE" || $current_student_permission == "ERROR") {
                                echo "onClick=\"return noPermission();\" ";
                                }
                                echo "><img src=\"". IPP_PATH . "images/pdf.png\" align=\"top\" border=\"0\"></a>";
                            }
                            echo "</td>\n";
                            echo "<td bgcolor=\"$bgcolor\" class=\"row_default\"><p class=\"small_text\">" . $student_row['school_name'] . "<p></td>\n";
                            echo "<td bgcolor=\"$bgcolor\" align=\"center\" class=\"row_default\"><p class=\"small_text\">$current_student_permission<p></td>\n";
                            echo "</tr>\n";
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }

                        //print the next and prev links...
                        echo "<tr><td colspan=\"2\">";
                        if($iCur != 0) {
                            //we have previous values...
                            echo "<a href=\"./manage_student.php?iCur=" . ($iCur-$iLimit) . "&iLimit=$iLimit&szSearch=&szSearchVal=" . $_GET['szSearchVal'] . "&field=" . $_GET['field'] . "&SEARCH=" . $_GET['SEARCH'] . "\" class=\"default\">prev $iLimit</a>";
                        } else {
                            echo "&nbsp;";
                        }
                        echo "</td><td colspan=\"2\" align=\"center\">";
                        echo "&nbsp;";
                        echo "</td>";
                        if(($iLimit+$iCur < $szTotalStudents)) {
                            echo "<td align=\"right\"><a href=\"./manage_student.php?iCur=" . ($iCur+$iLimit) . "&iLimit=$iLimit&szSearchVal=" . $_GET['szSearchVal'] . "&field=" . $_GET['field'] . "&SEARCH=" . $_GET['SEARCH'] . "\" class=\"default\">next ";
                            if( $szTotalStudents-($iCur+$iLimit) > $iLimit) {
                                echo $iLimit . "</td>";
                            } else {
                                echo ($szTotalStudents-($iCur+$iLimit)) . "</td>";
                            }
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                        echo "</tr>\n";
                        //end print next and prev links

                        //print out the action buttons
                        echo "<tr><td colspan=\"5\" align=\"left\"><img src=\"" . IPP_PATH . "images/table_arrow.png\">&nbsp;With Selected: ";
                        if($permission_level <= $IPP_MIN_DELETE_STUDENT_PERMISSION)
                            echo "<INPUT TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" name=\"delete\" value=\"1\">";
                        if($permission_level <= $IPP_MIN_DUPLICATE_IPP)
                            echo "<INPUT TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Duplicate\" border=\"0\" name=\"duplicate\" value=\"1\">";
                        echo "</td></tr>\n";
                        
                        ?>
                        </table></center>
                        </form>
                        <BR>

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
            <center><?php navbar("main.php"); ?></center>
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
