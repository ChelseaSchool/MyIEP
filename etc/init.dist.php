<?php

/** @file
 *  @brief init.php template used for installation process
 *  
 *  init.php is checked for everytime at launch to determine whether install wizard has to run
 * @remarks
 * #Summary: further configuration and customization at install time
 * 1. long term: install wizard should allow administrator to set these values
 * 2. Make sane values here
 * 3. Make copyright comment that follows license recommendation
*/
    //***************USER CONFIGURABLE**************
    //Do we need to run the wizard?
    $IPP_IS_CONFIGURED = FALSE;

    //page stuff
    $page_title = "MyIEP Special Education Program Management";
    $IPP_PAGE_ROOT = "http://192.168.0.103"; //the root page.

    //path to large logo for branding see the braning documentation to
    //customize these. Typically upload a new image to images folder with
    //a different name (this will allow you to just untar/gz any upgrades
    //and not have to rebrand).
    $page_logo_path = IPP_PATH . "images/banner.png"; //main banner ~640 x 90px
    $PDF_LOGO_PATH = IPP_PATH . "images/logo_pb.png"; //logo on pdf ~524 x 137px

    $IPP_ORGANIZATION = "Your School District"; //organization name
    $IPP_ORGANIZATION_ADDRESS1 = "123 - 1st Avenue";
    $IPP_ORGANIZATION_ADDRESS2 = "Brooks, Alberta";
    $IPP_ORGANIZATION_ADDRESS3 = "T1R 1L2";

    //user database
    $mysql_user_database = "test";
    $mysql_user_username = "test";
    $mysql_user_password = "test";
    $mysql_user_host = "192.168.0.102";
    $mysql_user_table = "users";
    $mysql_user_select_login = "login_name";
    $mysql_user_append_to_login = "";                  //if  we use email
                                                       // addys to verify
                                                       // login names via pop blank for nothing.

    $mysql_user_select_password = "unencrypted_password";

    //to use a different user database (ie an existing common source):
    $mysql_data_database = "test";
    $mysql_data_username = "test";
    $mysql_data_password = "test";
    $mysql_data_host = "192.168.0.102";

    //Email related. If you enable email notifications
    //the system uses the usernames for this system
    //appended to the $append_to_username field.
    $enable_email_notification = TRUE; //true if you want to get ipp handovers notices, etc
    $append_to_username = "";  //if we use email addresses
    $email_reply_address = "no-reply@fully.qualified.domain.name"; //Address to send the email from
    $mail_host = "localhost";

    //security
    $IPP_TIMEOUT = "60"; //minutes to timeout a login.
    
    //colours
    $IPP_BGCOLOUR1 = "#E0E2F2";   //the main background colour

    $IPP_CONFIGURATION_VERSION="1.0.006"; //This config file version
    //minimum permission levels 0 is toplevel...100 is everybody
    /*
     *  0 Super Administrator
     * 10 Administrator
     * 20 Assistant Administrator
     * 30 Principal
     * 40 Vice Principal
     * 50 Teaching Staff
     * 60 Teaching Assistant
     * 100 Login Only
     *
     */

    $IPP_MIN_DELETE_STUDENT_PERMISSION = 0; //super-administrator only
    $IPP_MIN_DELETE_GUARDIAN_NOTES = 50; //Teaching Staff and up with write permission.
    $IPP_MIN_DELETE_SUPERVISOR_PERMISSION = 30; //Principals and up with write permission
    $IPP_MIN_DELETE_STRENGTH_NEED_PERMISSION = 50; //Teaching staff and up with write permission
    $IPP_MIN_DELETE_STUDENT_CODING_PERMISSION = 50; //Teaching staff and up with write permission
    $IPP_MIN_DELETE_COORDINATION_OF_SERVICES = 50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_PERFORMANCE_TESTING = 50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_MEDICAL_INFO = 50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_MEDICATION_PERMISSION = 50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_TESTING_TO_SUPPORT_CODE = 50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_GRADES_REPEATED_PERMISSION= 50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_AREA_PERMISSION=20; //Asst Admin and up (Delete global program area).
    $IPP_MIN_VIEW_LIST_ALL_STUDENTS=20; //Can see all students in system Assistant Admin & up
    $IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS=50; //can see all local students teachers and up
    $IPP_MIN_DELETE_OBJECTIVE_PERMISSION=30; //Principals and up with write permission
    $IPP_MIN_DELETE_PROGRAM_AREA=50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_ASSISTIVE_TECHNOLOGY=50; //Teaching staff and up with write permission
    $IPP_MIN_DELETE_TRANSITION_PLAN = 50; //Teaching staff and up with write permission
    $IPP_MIN_DELETE_ACCOMODATION = 50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_SNAPSHOT = 50; //Teaching staff and up with assign permission
    $IPP_MIN_DELETE_ANECDOTAL = 60; //Teaching Assistants and up with write permission
    $IPP_MIN_DELETE_BACKGROUND_INFORMATION_PERMISSION = 50; //Teaching staff and up with write permission.
    $IPP_MIN_DELETE_BUG_PERMISSION = 0; //Super Admin (bug management);
    $IPP_MIN_EDIT_BUG_PERMISSION = 0; //Super Admin (bug management);
    $IPP_MIN_DELETE_SCHOOL_HISTORY = 50; //Teaching staff and up with write permissions.
    $IPP_MIN_DELETE_SCHOOL = 0; //super admin can delete a school
    $IPP_MIN_EDIT_SCHOOL = 0; //super admin can edit school information.
    $IPP_MIN_DELETE_CODE = 0; //super admi can delete a code
    $IPP_MIN_DUPLICATE_IPP = 50; //Teaching staff and up can duplicate IPP's they have read permission to

    require_once("version.php");
?>
