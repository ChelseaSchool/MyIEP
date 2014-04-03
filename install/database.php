<?php
/* @file
 * @brief		Install wizard - Database configuration check
 * @copyright	Copyright (c) 2005 Grasslands Regional Division #6
 * @copyright	GPLv2
 * @copyright	Copyright (c) 2014 Chelsea School
 * @todo
 * 1. Bootstrap
 * 2. Strip copyright information
 */


if(is_file("../etc/init.php")) {
   define('IPP_PATH','../');
   require_once("../etc/init.php");
   if(isset($IPP_IS_CONFIGURED) && $IPP_IS_CONFIGURED) {
      die("The configuration file:" . realpath("../etc/init.php") . " already exists and the IPP_IS_CONFIGURED flag is set. For security reasons you cannot rerun this page. If you want to rerun the install please manually delete the config file.");
   }
}

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;


/**
 * Path for required files.
 */

/** @fn error_reporting(1)
 *  @param $level level int
 *  @detail The new error_reporting level. It takes on either a bitmask, or named constants. Using named constants is  strongly encouraged to ensure compatibility for future versions. As error levels are added, the range of 
 *	integers increases, so older integer-based error levels will not always behave as expected. The available error level constants and the actual meanings of these error levels are described in the predefined constants. 
 * @Return int the old error_reporting level or the current level if no level parameter is given. 
 */
error_reporting(1);

/* eGPS required files. */
//require_once(IPP_PATH . 'etc/init.php');
//require_once(IPP_PATH . 'include/db.php');
//require_once(IPP_PATH . 'include/auth.php');
//if ((int)phpversion() < 5) { require_once(IPP_PATH . 'include/fileutils.php'); } //only for pre v5
//require_once(IPP_PATH . 'include/log.php');
//require_once(IPP_PATH . 'include/navbar.php');

/** $var $system_message string
 *  @brief  $system_message set to NULL for security/sanity
 */
$system_message="";

header('Pragma: no-cache'); //don't cache this page!
if(isset($_POST['db_host'])) {
  //we are creating the datase
  $link = mysql_connect($_POST['db_host'],$_POST['db_username'],$_POST['db_password']);
  if($link == FALSE) {
    $system_message = "Could not connect to database: for the following reason: '" . mysql_error() . "'<BR>\n";
    } else {
        $db_user = mysql_select_db($_POST['db_name']);
        if(!$db_user) {
           $system_message = "Could not select database: '" . $mysql_user_database . "' for the following reason: '" . mysql_error() . "'</BR>\n";
        } else {
            //we are good to go ahead...
            if(isset($_POST['db_populate']) && $_POST['db_populate']=="on") {
              // $system_message .= "Populating<BR>";
             
               $file_content = file("default.sql");
               $sql="";

               $FAIL = FALSE;
               foreach($file_content as $sql_line){
                 if(trim($sql_line) != "" && strpos($sql_line, "--") === false){
                   $sql .= $sql_line;
                   if(preg_match("/;[\040]*\$/", $sql_line)){
                     $result = mysql_query($sql) or die(mysql_error());
                    // if(!$result) { $system_message .= "Cannot populate the database: " . mysql_error() . "<BR>"; $FAIL=TRUE; }
                     $sql = "";
                   }
                 }
               }

               //we need to update the configuration file...
               $file=file("../etc/init.php");
               if(!$file) $system_message .= "Cannot open init.php configuration file. You will need to manually edit this file<BR>";
               else {
                 $updated_file = "";
                 foreach($file as $line) {
                  // echo "Line: " . $line . "<BR>";
                   if(preg_match('/mysql_user_host/',$line)) {$line = "\$mysql_user_host = \"" . $_POST['db_host'] . "\";\n";}
                   if(preg_match('/mysql_data_host/',$line)) {$line = "\$mysql_data_host = \"" . $_POST['db_host'] . "\";\n";}
                   if(preg_match('/mysql_user_database/',$line)) {$line = "\$mysql_user_database = \"" . $_POST['db_name'] . "\";\n";}
                   if(preg_match('/mysql_data_database/',$line)) {$line = "\$mysql_data_database = \"" . $_POST['db_name'] . "\";\n";}
                   if(preg_match('/mysql_data_username/',$line)) {$line = "\$mysql_data_username= \"" . $_POST['db_username'] . "\";\n";}
                   if(preg_match('/mysql_user_username/',$line)) {$line = "\$mysql_user_username = \"" . $_POST['db_username'] . "\";\n";}
                   if(preg_match('/mysql_user_password/',$line)) {$line = "\$mysql_user_password = \"" . $_POST['db_password'] . "\";\n";}
                   if(preg_match('/mysql_data_password/',$line)) {$line = "\$mysql_data_password = \"" . $_POST['db_password'] . "\";\n";}
                   $updated_file .= $line;
                   //echo $line . "<BR>";
                 }
               $handle=fopen("../etc/init.php","w");
                  if(!$handle) { $fail=TRUE; echo "Cannot open init.php configuration file for writing (" . realpath("../etc/init.php") . "). You will need to manually edit this file<BR>";} else {
                      fwrite($handle,$updated_file);
                      fclose($handle);
                   }

                  header("Location: config.php");
               }
             } else { 
              //$system_message .= "NOT POPULATED<BR>" . $_POST['db_populate'];
              //we need to update the configuration file...
               $file=file("../etc/init.php");
               if(!$file) $system_message .= "Cannot open init.php configuration file. You will need to manually edit this file<BR>";
               else {
                 $updated_file = "";
                 foreach($file as $line) {
                  // echo "Line: " . $line . "<BR>";
                   if(preg_match('/mysql_user_host/',$line)) {$line = "\$mysql_user_host = \"" . $_POST['db_host'] . "\";\n";}
                   if(preg_match('/mysql_data_host/',$line)) {$line = "\$mysql_data_host = \"" . $_POST['db_host'] . "\";\n";}
                   if(preg_match('/mysql_user_database/',$line)) {$line = "\$mysql_user_database = \"" . $_POST['db_name'] . "\";\n";}
                   if(preg_match('/mysql_data_database/',$line)) {$line = "\$mysql_data_database = \"" . $_POST['db_name'] . "\";\n";}
                   if(preg_match('/mysql_data_username/',$line)) {$line = "\$mysql_data_username= \"" . $_POST['db_username'] . "\";\n";}
                   if(preg_match('/mysql_user_username/',$line)) {$line = "\$mysql_user_username = \"" . $_POST['db_username'] . "\";\n";}
                   if(preg_match('/mysql_user_password/',$line)) {$line = "\$mysql_user_password = \"" . $_POST['db_password'] . "\";\n";}
                   if(preg_match('/mysql_data_password/',$line)) {$line = "\$mysql_data_password = \"" . $_POST['db_password'] . "\";\n";}
                   $updated_file .= $line;
                   //echo $line . "<BR>";
                 }
                 $handle=fopen("../etc/init.php","w");
                  if(!$handle) { $fail=TRUE; echo "Cannot open init.php configuration file for writing (" . realpath("../etc/init.php") . "). You will need to manually edit this file<BR>";} else {
                     fwrite($handle,$updated_file);
                     fclose($handle);
                  }
 
                 header("Location: config.php");
               }
             }
        } 
    }
}
?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE>MyIEP Installation</TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "../layout/greenborders.css";
        -->
    </style>
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
                    <td><center><img src="../images/banner.png"></center></td>
                    </tr>
                    <tr><td>
                    &nbsp;
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                         <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>
                        <center><table><tr><td><center><p class="header">- Installation Database Configuration -</p></center></td></tr></table></center>
        <BR><center><table width="80%" border="0"><tr><td>
                
                <form enctype="multipart/form-data" action="./database.php" method="post">
                <center><table width="80%" border="0">
                  <tr>
                    <td>Database Host</td>
                    <td><input type="text" name="db_host" value="<?php if(isset($_POST['db_host'])) echo $_POST['db_host']; else echo "localhost"; ?>"></td>
                  </tr>
                  <tr>
                    <td>Database Name</td>
                    <td><input type="text" name="db_name" value="<?php if(isset($_POST['db_name'])) echo $_POST['db_name']; else echo "ipp"; ?>"></td>
                  </tr>
                  <tr>
                    <td>Database Username</td>
                    <td><input type="text" name="db_username" value="<?php if(isset($_POST['db_username'])) echo $_POST['db_username']; else echo "ipp"; ?>"></td>
                  </tr>
                  <tr>
                    <td>Database Password</td>
                    <td><input type="password" name="db_password" value="<?php if(isset($_POST['db_password'])) echo $_POST['db_password']; else echo ""; ?>"></td>
                  </tr>
                  <tr>
                    <td>Populate Database (uncheck if this is an upgrade)</td>
                    <td><input type="checkbox" name="db_populate" <?php if(!isset($_POST['db_populate'])) echo "CHECKED"; else echo "CHECKED"?>></td>
                  </tr>
                </table><input name="create" class="sbutton" type="submit" value="Next"></center>
                </form>
                </td></tr></table></center>
                        </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center">&nbsp;</td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
        
    </BODY>
</HTML>