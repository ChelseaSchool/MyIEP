<?php
/* Notes:
 * 1. Output is not escaped
 * 2. Copyright should reflect Chelsea School: make a variable value set by admin and stored in db table with logo
 * 3. Some code is commented. It needs why? or taken out.
 * 4. Some dev notes to self. Make productive.
 * 5. Change copyright header
 * 6. include abbreviated gpl in header - see license for guidelines
*/
//the authorization level for this page!
//$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody
error_reporting(0); //no errors/warnings for this page!


/*   INPUTS: $_GET['student_id'] || $_PUT['student_id']
 *
 */

/**
 * Path for IPP required files.
 */

$system_message = "";

//define('IPP_PATH','../');

//to define the font path  (mandatory trailing slash)
//define('FPDF_FONTPATH','/var/www/ssl/ipp/layout/fonts/');
//define('FPDF_FONTPATH',IPP_PATH . 'layout/fonts/');
//$this->SetFont('chanticl.ttf','B',12);

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/fpdf/fpdf.php');
//require_once("Numbers/Roman.php"); //require pear roman numerals class

//Header('Pragma: public, no-cache');

if(isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if(!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'login.php');
        exit();
    }
} else {
    if(!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'login.php');
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

if($our_permission == "NONE") {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************
function create_pdf($student_id) {

  global $system_message,$student_row;

  $student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
  $student_result = mysql_query($student_query);
  if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {$student_row= mysql_fetch_array($student_result);}

  //get the goals...
  $long_goal_query = "SELECT * FROM long_term_goal WHERE student_id=$student_id  ORDER BY area ASC, is_complete DESC, goal ASC";
  $long_goal_result = mysql_query($long_goal_query);
  if(!$long_goal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$long_goal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    echo $system_message;
    exit();
  }

  //get the supervisor...
  $supervisor_row = "";
  $supervisor_query = "SELECT * FROM supervisor WHERE student_id=$student_id";
  $supervisor_result = mysql_query($supervisor_query);
  if(!$supervisor_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$supervisor_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //echo $system_message;
    //exit();
    //just carry on
  } else {
    $supervisor_row = mysql_fetch_array($supervisor_result);
  }

  //get the school code...
  $school_row = "";
  $school_query = "SELECT * FROM school_history LEFT JOIN school on school_history.school_code=school.school_code WHERE student_id=" . $student_id . " AND end_date IS NULL";
  $school_result = mysql_query($school_query);
  if(!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {
    $school_row = mysql_fetch_array($school_result);
  }

  //get the school code...
  $school_history_row = "";
  $school_history_query = "SELECT * FROM school_history WHERE student_id=" . $student_id . " AND end_date IS NOT NULL ORDER BY end_date DESC";
  $school_history_result = mysql_query($school_history_query);
  if(!$school_history_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_history_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get the coding and history...
  $code= "";
  $code_text = "(?)";
  $ipp_history="Unknown";
  $code_query = "SELECT * FROM coding LEFT JOIN valid_coding ON coding.code=valid_coding.code_number WHERE student_id=" . $student_id . " ORDER BY end_date ASC";
  $code_result = mysql_query($code_query);
  if(!$code_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$code_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  } else {
   if(mysql_num_rows($code_result)) {
    $code_row = mysql_fetch_array($code_result);
    $code=$code_row['code'];
    $code_text = " (" . $code_row['code_text'] . ")";
    $ipp_history= "Code " . $code_row['code'] . " from " . $code_row['start_date'] . " to ";
    if($code_row['end_date'] == "") $ipp_history .= "present";
    else $ipp_history .= $code_row['end_date'];
    while($code_row = mysql_fetch_array($code_result)) {
        $ipp_history .= "\nCode " . $code_row['code'] . " from " . $code_row['start_date'] . " to ";
        if($code_row['end_date'] == "") $ipp_history .= "present";
        else $ipp_history .= $code_row['end_date'];
    }
   }
  }

  //get the guardian information...
  $guardian_query = "SELECT * FROM guardians LEFT JOIN guardian ON guardians.guardian_id=guardian.guardian_id LEFT JOIN address ON guardian.address_id=address.address_id WHERE guardians.to_date IS NULL AND student_id=" . $student_id . " ORDER BY last_name ASC, first_name ASC";
  $guardian_result = mysql_query($guardian_query);
  if(!$guardian_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$guardian_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }


  $supervisors = "none listed";
  $supervisor_query = "SELECT * FROM supervisor LEFT JOIN support_member ON supervisor.egps_username=support_member.egps_username WHERE student_id=" . $student_id . " AND end_date IS NULL";
  $supervisor_result = mysql_query($supervisor_query);
  if(!$supervisor_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$supervisor_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  } else {
   if(mysql_num_rows($supervisor_result)) {
     $supervisors = "";
     while($supervisor_row = mysql_fetch_array($supervisor_result)) {
         $supervisors .=  $supervisor_row['first_name'] . " " . $supervisor_row['last_name'] . ", ";//username_to_common($supervisor_row['egps_username']) . ", ";
     }
     //strip off the trailing ', '
     $supervisors = substr($supervisors, 0, -2);
   }
  }

  $support_team = "none listed";
  $support_team_query = "SELECT * FROM support_list LEFT JOIN support_member ON support_list.egps_username=support_member.egps_username WHERE student_id=" . $student_id;
  $support_team_result = mysql_query($support_team_query);
  if(!$support_team_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$support_team_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  } else {
   if(mysql_num_rows($support_team_result)) {
     $support_team = "";
     while($support_team_row = mysql_fetch_array($support_team_result)) {
         $support_team .= $support_team_row['first_name'] . " " . $support_team_row['last_name'] . ", ";//username_to_common($support_team_row['egps_username']) . ", ";
     }
     //strip off the trailing ', '
     $support_team = substr($support_team, 0, -2);
   }
  }

  //get strengths...
  $strengths = "none listed";
  $strength_query = "SELECT * FROM area_of_strength_or_need WHERE strength_or_need='Strength' AND is_valid='Y' AND student_id=" . $student_id . " ORDER BY description ASC";
  $strength_result = mysql_query($strength_query);
  if(!$strength_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$strength_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    $strengths = "-error-";
    //just carry on
  } else {
   if(mysql_num_rows($strength_result)) {
     $strengths = "";
     $count=1;
     while($strength_row = mysql_fetch_array($strength_result)) {
         $strengths .= $count . ") " . $strength_row['description'] . "\n\n";
         $count++;
     }
     //strip off the trailing '\n'
     $strengths = substr($strengths, 0, -1);
   }
  }

  //get strengths...
  $needs = "none listed";
  $needs_query = "SELECT * FROM area_of_strength_or_need WHERE strength_or_need='Need' AND is_valid='Y' AND student_id=" . $student_id . " ORDER BY description ASC";
  $needs_result = mysql_query($needs_query);
  if(!$needs_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$needs_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    $needs = "-error-";
    //just carry on
  } else {
   if(mysql_num_rows($needs_result)) {
     $needs = "";
     $count=1;
     while($needs_row = mysql_fetch_array($needs_result)) {
         $needs .= $count . ") " . $needs_row['description'] . "\n\n";
         $count++;
     }
     //strip off the trailing '\n'
     $needs = substr($needs, 0, -1);
   }
  }

  //get medical information...
  $medical_query = "SELECT * FROM medical_info WHERE student_id=" . $student_id . " ORDER BY is_priority ASC, date DESC";
  $medical_result = mysql_query($medical_query);
  if(!$medical_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$medical_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get medications...
  $medication_query = "SELECT * FROM medication WHERE end_date IS NULL AND student_id=" . $student_id . " ORDER BY start_date DESC";
  $medication_result = mysql_query($medication_query);
  if(!$medication_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$medication_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get testing to support coding information...
  $testing_query = "SELECT * FROM testing_to_support_code WHERE student_id=" . $student_id . " ORDER BY date DESC";
  $testing_result = mysql_query($testing_query);
  if(!$testing_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$testing_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get background information...
  $background_query = "SELECT * FROM background_info WHERE student_id=" . $student_id . " ORDER BY type ASC";
  $background_result = mysql_query($background_query);
  if(!$background_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$background_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get current level of performance and achievement...
  $cla_query = "SELECT * FROM performance_testing WHERE student_id=" . $student_id . " ORDER BY date DESC";
  $cla_result = mysql_query($cla_query);
  if(!$cla_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$cla_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get coordination of services...
  $services_query = "SELECT * FROM coordination_of_services WHERE student_id=" . $student_id . " ORDER BY date DESC";
  $services_result = mysql_query($services_query);
  if(!$services_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$services_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get accommodations...
  $accommodations_query = "SELECT * FROM accomodation WHERE student_id=" . $student_id . " ORDER BY end_date DESC";
  $accommodations_result = mysql_query($accommodations_query);
  if(!$accommodations_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$accommodations_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get transition plans...
  $transition_query = "SELECT * FROM transition_plan WHERE student_id=" . $student_id . " ORDER BY date DESC";
  $transition_result = mysql_query($transition_query);
  if(!$transition_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$transition_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }

  //get assistive technology...
  $at_query = "SELECT * FROM assistive_technology WHERE student_id=" . $student_id . " ORDER BY technology ASC";
  $at_result = mysql_query($at_query);
  if(!$at_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$at_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    //just carry on
  }


  //lets get some PDF making done...
  class IPP extends FPDF  //all this and OO too weeeeeeee

  {
    
     function Header()
     {
        global $PDF_LOGO_PATH,$IPP_ORGANIZATION,$student_row,$IPP_ORGANIZATION_ADDRESS1,$IPP_ORGANIZATION_ADDRESS2,$IPP_ORGANIZATION_ADDRESS3;
        //Set a colour
        $this->SetTextColor(153,153,153);  //greyish
        //Arial bold 15
        $this->SetFont('Arial','B',12);
        //Move to the right
        $this->Cell(60);
        //out organization
        $this->Ln();
        $this->Cell(60);
        $this->Cell(0,5,$IPP_ORGANIZATION,'B',1,'R');
        //$this->Ln();
        $this->SetFont('Arial','I',5);
        $this->Cell(60,0,'',0,0,'');
        $this->Cell(0,5,$IPP_ORGANIZATION_ADDRESS1 . ', ' . $IPP_ORGANIZATION_ADDRESS2 . ', ' . $IPP_ORGANIZATION_ADDRESS3,0,0,'R');
        //Logo
        $this->Image($PDF_LOGO_PATH,10,8,50);
        //Line break
        $this->Ln(15);
        //Set colour back
         $this->SetTextColor(153,153,153);  // Well, I'm back in black, yes I'm back in black! Ow!
     }

     //Page footer
     function Footer()
     {
         global $student_row;
         //Set a colour
         $this->SetTextColor(153,153,153);  //greyish
         //Position at 1.5 cm from bottom
         $this->SetY(-10);
         //Arial italic 8
         $this->SetFont('Arial','I',8);
         //Page number
         $this->Cell(0,3,'Individualized Education Program for ' . $student_row['first_name'] . ' ' . $student_row['last_name'] . '-' . date('dS \of F Y') . ' (Page '.$this->PageNo().'/{nb})',0,1,'C');

         //output a little information on this
         $this->SetFont('Arial','i',6);
         $this->SetTextColor(153,153,153);  //greyish
         $this->SetFillColor(255,255,255);
         $this->Ln(1);
         $this->MultiCell(0,5,"MYIEP System Copyright 2014 Chelsea School | �2005-2007 Grasslands Public Schools | licensed under the Gnu Public License",'T','C',1);

         //Set colour back
         $this->SetTextColor(0,0,0);  // Well, I'm back in black, yes I'm back in black!
     }
}

  //Instanciation of inherited class
  $pdf=new IPP();
  $pdf->AliasNbPages();
  $pdf->AddPage();

  //set the pdf information
  $pdf->SetAuthor(username_to_common($_SESSION['egps_username']));
  $pdf->SetCreator('MyIEP Special Education Program Management');
  $pdf->SetTitle('Individual Program Plan - ' . $student_row['first_name'] . ' ' . $student_row['last_name']);


  //begin pdf...
  $pdf->SetFont('Times','',20);
  $pdf->SetTextColor(220,50,50); //set the colour a loverly redish
  $pdf->Cell(30);
  $pdf->Cell(130,5,'  Program Plan ',0,0,'C');
  //$pdf->Image(IPP_PATH . 'images/bounding_box.png',$pdf->GetX()-1,$pdf->GetY()-4);
  $mark = $pdf->GetY();
  /* if(isset($code) && is_numeric($code)) {
    $pdf->SetFont('Times','B',50);
    if($code > 99) $pdf->SetFont('Times','B',30);   
    if($code >999) $pdf->SetFont('Times','B',25);
    if($code >9999) $pdf->SetFont('Times','B',20);
  }else { */
    $pdf->SetFont('Times','B',10);
    //$code=$code; //
    $code="Not Coded";
 // } 
  $pdf->SetTextColor(0,51,0);  //grey
  $pdf->SetFillColor(240,240,240);  // white
  $pdf->SetDrawColor(0,0,0); //blueish
  $pdf->Cell(19,14,$code,0,1);

  //move back
  $pdf->SetY($mark);
  $pdf->Ln(10);
  $pdf->SetFont('Times','B',15);
  $pdf->SetTextColor(220,50,50); //set the colour a loverly redish
  $pdf->Cell(0,0,'- '. $student_row['first_name'] . " " . $student_row['last_name'] . ' -',0,0,'C');

  //Set colour back
  $pdf->Ln(15);
  $pdf->SetTextColor(0,0,0);  // Well, I'm back in black, yes I'm back in black! Ow!

   //Begin student information
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Student Information','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(0,0,0);

   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Student Name: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(50,5,iconv('UTF-8','Windows-1252', $student_row['first_name']) . ' ' . iconv('UTF-8','Windows-1252', $student_row['last_name']),0,0);
   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Student Number: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(0,5,iconv('UTF-8','Windows-1252', $student_row['prov_ed_num']),0,1);

   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Date of Birth: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(50,5,$student_row['birthday'],0,0);
   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Current Grade: ',0,0);
   $pdf->SetFont('Arial','I',10);
   switch ($student_row['current_grade']) {
          case '-1':
            $pdf->Cell(50,5,"District Program",0,0);
            break;
          case '0':
            $pdf->Cell(50,5,"Kindergarten or Pre-K",0,0);
            break;
          default:
            $pdf->Cell(50,5,$student_row['current_grade'],0,0);
   }
   $pdf->Cell(0,5,$current_grade,0,1);

   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Gender: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $gender="Unknown";
   if($student_row['gender']=='M') $gender="Male";
   if($student_row['gender']=='F') $gender="Female";
   $pdf->Cell(50,5,$gender,0,0);

   //find the age
   function get_age_by_date($yyyymmdd)
   {
    global $system_message;
    $bdate = explode("-", $yyyymmdd);
    $dob_month=$bdate[1]; $dob_day=$bdate[2]; $dob_year=$bdate[0];
    if (checkdate($dob_month, $dob_day, $dob_year)) {
        $dob_date = "$dob_year" . "$dob_month" . "$dob_day";
        $age = floor((date("Ymd")-intval($dob_date))/10000);
        if (($age < 0) or ($age > 114)) {
            return $age . "<BR> -->Age warning: Negative or Zero (check D.O.B)<--";
        }
        return $age;
    }
    return "-unknown-";
   }
   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Current Age: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(0,5,get_age_by_date($student_row['birthday']),0,1);

   //$pdf->Cell(10);
   //$pdf->SetFont('Arial','B',10);
   //$pdf->Cell(50,5,'Date of Birth: ',0,0);
   //$pdf->SetFont('Arial','I',10);
   //$pdf->Cell(0,5,$student_row['birthday'],0,1);

   $pdf->Ln(5);
   //End student information

   //Begin School Information
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'School Information','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(0,0,0);

   $pdf->Cell(10);
   $top_bounding_box = $pdf->GetY();
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Current School: ',0,0);
   $pdf->SetFont('Arial','I',10);
   if($school_row['school_name'] == '') {
     $pdf->Cell(50,5,"Archived Student",0,1);
   } else {
     $pdf->Cell(50,5,$school_row['school_name'] . ' (' . $school_row['school_code'] . ')',0,0);
     $pdf->Cell(10);
     $pdf->SetFont('Arial','B',10);
     $pdf->Cell(30,5,'Since: ',0,0);
     $pdf->SetFont('Arial','I',10);
     $pdf->Cell(0,5,$school_row['start_date'],0,1);

     $pdf->Cell(10);
     $pdf->SetFont('Arial','B',10);
     $pdf->Cell(30,5,'Address: ',0,0);
     $pdf->SetFont('Arial','I',10);
     $pdf->MultiCell(0,5,$school_row['school_address'],0,1);

   }
   //school history:
   $pdf->SetDrawColor(220,220,220);
   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(160,5,'School History','B',1);
   $pdf->SetFont('Arial','I',10);
   $pdf->Ln(2);


   $pdf->SetDrawColor(220,220,220);
   $pdf->SetFillColor(220,220,220);
   $pdf->SetFont('Arial','I',8);
   if(mysql_num_rows($school_history_result) <= 0) {
      $pdf->Cell(30);
      $pdf->MultiCell(120,5,'No history entered',0,'C',0);
   }
   while($school_history_row=mysql_fetch_array($school_history_result)) {
     $pdf->Cell(30);
     $school_history_date = $school_history_row['start_date'];
     if(!$school_history_row['end_date']) $school_history_date .=  " to present";
     else $school_history_date .= " to " . $school_history_row['end_date'];
     $pdf->Cell(120,3, $school_history_date, 'B',1,'R');
     $pdf->SetFont('Arial','B',8);
     $pdf->Cell(30);
     if($school_history_row['address'] == "") $address="Unknown";
     else $address= $school_history_row['school_address'];
     $pdf->MultiCell(120,5,$school_history_row['school_name'],0,'L',1);
     $pdf->SetFont('Arial','I',8);
     if($school_history_row['school_address'] != "") {
       $pdf->Cell(30);
       $pdf->Cell(30,5,'Address:',0,0,'J',0);
       $patterns[0] = '/,\n/';
       $patterns[1] = '/,.\n/';
       $patterns[2] = '/\n/';
       $replacements[0] = ', ';
       $replacements[1] = ', ';
       $replacements[2] = ', ';
       $pdf->MultiCell(90,5,preg_replace($patterns,$replacements,$school_history_row['school_address']),0,'L',0);
       $pdf->Cell(30);$pdf->Cell(120,0,'','B');
       $pdf->Ln(1);
     }

     if($school_history_row['grades'] != "") {
       $pdf->Cell(30);
       $pdf->Cell(30,5,'Grades:',0,0,'J',0);
       $pdf->MultiCell(90,5,$school_history_row['grades'],0,'L',0);
       $pdf->Cell(30);$pdf->Cell(120,0,'','B');
       $pdf->Ln(1);
     }

     if($school_history_row['accommodations'] != "") {
       $pdf->Cell(30);
       $pdf->Cell(30,5,'Accommodations:',0,0,'J',0);
       $pdf->MultiCell(90,5,iconv('UTF-8','Windows-1252', $school_history_row['accommodations']),0,'L',0);
       $pdf->Cell(30);$pdf->Cell(120,0,'','B');
       $pdf->Ln(1);
     }

     $ipp_present_text = "";
     switch($school_history_row['ipp_present']) {
        case 'Y':
          $ipp_present_text = "IPP present at this school";
        break;
        case 'N':
          $ipp_present_text = "IPP not present at this school";
        break;
        default:
          $ipp_present_text = "Unknown if IPP present at this school";
     }
     $pdf->Cell(30);
     $pdf->Cell(30,5,'IPP Present:',0,0,'J',0);
     $pdf->MultiCell(90,5,$ipp_present_text,0,'L',0);
     $pdf->Cell(30);$pdf->Cell(120,0,'','B');
     $pdf->Ln(3);
   }

   $pdf->Ln(1);
   $pdf->Cell(10);
   $pdf->SetDrawColor(220,220,220);
   $pdf->Cell(160,0,'','T',0,1);
   $pdf->Ln(1);

   $pdf->Ln(5);
   //End School Information

   //BEGIN IPP information
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Individual Program Plan Information','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(0,0,0);

   $pdf->Cell(10);
   $top_bounding_box = $pdf->GetY();
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Current Date: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $school_year = "";
   if(date('n') < 9) {
      $school_year= (date('Y') - 1) . "-" . date('Y');
   } else {
      $school_year=  date('Y') . "-" . (date('Y') + 1);
   }

   $pdf->Cell(50,5,date('l dS \of F Y'),0,0);
   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'School Year: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(0,5,$school_year,0,1);

   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Current Code: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(0,5,$code . $code_text,0,1);

   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Supervisor(s): ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->MultiCell(0,5,iconv('UTF-8','Windows-1252', $supervisors),0,1);

   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'Support Team: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->MultiCell(0,5,iconv('UTF-8','Windows-1252', $support_team),0,1);

   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(30,5,'IPP History: ',0,0);
   $pdf->SetFont('Arial','I',10);
   $pdf->MultiCell(0,5,$ipp_history,0,1);

   $pdf->Ln(5);
   //END IPP information


   //BEGIN Guardian Information
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Guardians','B','L',0);
   $pdf->Ln(5);

   $pdf->SetDrawColor(220,220,220);
   while($guardian_row = mysql_fetch_array($guardian_result)) {
     $pdf->Cell(10);
     $pdf->SetFont('Arial','B',10);
     $pdf->Cell(60,5,iconv('UTF-8','Windows-1252', $guardian_row['first_name']) . ' ' . iconv('UTF-8','Windows-1252', $guardian_row['last_name']),'T',0);
     $pdf->SetFont('Arial','I',10);
     $guardian_info="";
     if($guardian_row['home_ph'] != "")
       $guardian_info = "Home Phone: " . iconv('UTF-8','Windows-1252', $guardian_row['home_ph']) . "\n";
     if($guardian_row['business_ph'] != "")
        $guardian_info .=  "Business Phone: " . iconv('UTF-8','Windows-1252', $guardian_row['business_ph']) . "\n";
     if($guardian_row['cell_ph'] != "")
        $guardian_info .=  "Cell Phone: " . iconv('UTF-8','Windows-1252', $guardian_row['cell_ph']) . "\n";
     if($guardian_row['email_address'] != "")
        $guardian_info .=  "Email: " . iconv('UTF-8','Windows-1252', $guardian_row['email_address']) . "\n";
     if($guardian_row['po_box'] != "")
        $guardian_info .=  iconv('UTF-8','Windows-1252', $guardian_row['po_box']) . "\n";
     if($guardian_row['street'] != "")
        $guardian_info .=  "Street: " . iconv('UTF-8','Windows-1252', $guardian_row['street']) . "\n";
     if($guardian_row['city'] != "")
        $guardian_info .=  "City: " . iconv('UTF-8','Windows-1252', $guardian_row['city']) . "\n";
     if($guardian_row['province'] != "")
        $guardian_info .=  "Province: " . iconv('UTF-8','Windows-1252', $guardian_row['province']) . "\n";
     if($guardian_row['country'] != "")
        $guardian_info .=  "Country: " . iconv('UTF-8','Windows-1252', $guardian_row['country']) . "\n";
     if($guardian_row['postal_code'] != "")
        $guardian_info .=  "Postal Code: " . iconv('UTF-8','Windows-1252', $guardian_row['postal_code']) . "\n";

     if($guardian_info == "") $guardian_info="no information available";
     $pdf->MultiCell(100,5,$guardian_info,'T','L',0);
     $pdf->Ln(1);
   }
   $pdf->Cell(10);
   $pdf->Cell(160,0,'','T');

   $pdf->Ln(5);
   //END Guardian Information

   //BEGIN Background Information
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Background Information','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(220,220,220);
   $pdf->SetFillColor(220,220,220);

   $pdf->SetFont('Arial','I',8);
   while($background_row=mysql_fetch_array($background_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,'' . $background_row['date'], 'B',1,'R');
     $pdf->SetFont('Arial','B',8);
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,$background_row['type'],'T','L',1);
     $pdf->SetFont('Arial','I',8);
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $background_row['description']),0,'L',0);
     $pdf->Ln(2);
   }

   $pdf->Ln(5);
   //END Background Information


   //BEGIN Accommodations (2x m's 2x c's)
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Accommodations','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(220,220,220);
   $pdf->SetFillColor(220,220,220);

   $pdf->SetFont('Arial','I',8);
   while($accommodations_row=mysql_fetch_array($accommodations_result)) {
     $pdf->Cell(30);
     $accommodations_date = $accommodations_row['start_date'] . "-";
     if(!$accommodations_row['end_date']) $accommodations_date .=  " Current";
     $pdf->Cell(120,3, $accommodations_date, 'B',1,'R');
     $pdf->SetFont('Arial','B',8);
     $pdf->Cell(30);
     if($accommodations_row['subject'] == "") $subject="Unspecified";
     else $subject= $accommodations_row['subject'];
     $pdf->MultiCell(120,5,"Subject/Area: " . iconv('UTF-8','Windows-1252',$subject),0,'L',1);
     $pdf->SetFont('Arial','I',8);
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252',$accommodations_row['accomodation']),0,'J',0);  //yeah, spelling is wrong...so what.
     $pdf->Ln(2);
   }

   $pdf->Ln(5);
   //END Accommodations

   //BEGIN Strengths & Needs information
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Strengths & Needs','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(0,0,0);

   $pdf->SetDrawColor(220,220,220);
   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(60,5,'Strengths:','T',0);
   $pdf->SetFont('Arial','I',10);
   if($strengths == "") $strengths="no information available";
   $pdf->MultiCell(100,5,iconv('UTF-8','Windows-1252', $strengths),'T','L',0);
   $pdf->Ln(1);
   $pdf->Cell(10);
   $pdf->SetDrawColor(220,220,220);
   //$pdf->Cell(160,0,'','T',0,1);
   $pdf->Ln(1);

   $pdf->SetDrawColor(220,220,220);
   $pdf->Cell(10);
   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(60,5,'Needs:','T',0);
   $pdf->SetFont('Arial','I',10);
   if($needs == "") $needs="no information available";
   $pdf->MultiCell(100,5,iconv('UTF-8','Windows-1252', $needs),'T','L',0);
   $pdf->Ln(1);
   $pdf->Cell(10);
   $pdf->SetDrawColor(220,220,220);
   $pdf->Cell(160,0,'','T');

   $pdf->Ln(5);
   //END Strengths and Needs

   //BEGIN Medical Information
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Medical','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(0,0,0);

   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(10);
   $pdf->Cell(15,5,'Medical Conditions that Impact Schooling:', 0,1);
   $pdf->Ln(1);
   $pdf->SetFont('Arial','I',8);
   $pdf->SetDrawColor(220,220,220);
   if(mysql_num_rows($medical_result) < 1) {
       $pdf->Cell(30);$pdf->MultiCell(120,5,'No Known Medical Conditions','TB','C',0);$pdf->Ln(2);
   }
   while($medical_row=mysql_fetch_array($medical_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,'Date: ' . $medical_row['date'],'B',1,'R');
     //$pdf->Ln(3);
     //check if this is a priority entry...
     if($medical_row['is_priority'] == 'Y') {
        $pdf->Image(IPP_PATH . 'images/caution.png',($pdf->GetX())+23,$pdf->GetY(),5);
     }
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $medical_row['description']),'B','J',0);
     $pdf->Ln(2);
   }

   $pdf->SetFont('Arial','B',10);
   $pdf->Cell(10);
   $pdf->Cell(15,5,'Current Medications:', 0,1);
   $pdf->Ln(1);
   $pdf->SetFont('Arial','I',8);
   while($medication_row=mysql_fetch_array($medication_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,'Started: ' . $medication_row['start_date'],'B',1,'R');
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $medication_row['medication_name']) . ' (Doctor: ' . iconv('UTF-8','Windows-1252', $medication_row['doctor']) . ')' ,'B','J',0);
     $pdf->Ln(2);
   }

   $pdf->Ln(5);
   //END Medical Information

   //BEGIN Testing to Support Coding
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Testing to Support Coding (Assessment)','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(220,220,220);
   $pdf->SetFillColor(220,220,220);

   $pdf->SetFont('Arial','I',8);
   while($testing_row=mysql_fetch_array($testing_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,'Date: ' . $testing_row['date'], 'B',1,'R');
     $pdf->SetFont('Arial','B',8);
     $pdf->Cell(30);
     if($testing_row['administered_by'] == "") $admin_by = "-unknown-";
       else $admin_by = $testing_row['administered_by'];
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $testing_row['test_description']) . ' (Administered by: ' . iconv('UTF-8','Windows-1252', $admin_by) . ')','T','L',1);
     $pdf->SetFont('Arial','I',8);
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $testing_row['recommendations']),0,'L',0);
     $pdf->Ln(2);
   }

   $pdf->Ln(5);
   //END Testing to Support Coding

   //BEGIN Current Level of Performance and Achievement
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Current Level of Performance & Achievement','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(220,220,220);
   $pdf->SetFillColor(220,220,220);

   $pdf->SetFont('Arial','I',8);
   while($cla_row=mysql_fetch_array($cla_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,'Date: ' . $cla_row['date'], 'B',1,'R');
     $pdf->SetFont('Arial','B',8);
     $pdf->Cell(30);
     //if($testing_row['administered_by'] == "") $admin_by = "-unknown-";
     //  else $admin_by = $testing_row['administered_by'];
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $cla_row['test_name']),0,'L',1);
     $pdf->SetFont('Arial','I',8);
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $cla_row['results']),0,'J',0);
     $pdf->Ln(2);
   }

   $pdf->Ln(5);
   //END Current Level of Performance and Achievement

   //BEGIN Coordination of Services
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Co-ordination of Services','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(220,220,220);
   $pdf->SetFillColor(220,220,220);

   $pdf->SetFont('Arial','I',8);
   while($services_row=mysql_fetch_array($services_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,'Date: ' . $services_row['date'], 'B',1,'R');
     $pdf->SetFont('Arial','B',8);
     $pdf->Cell(30);
     //if($testing_row['administered_by'] == "") $admin_by = "-unknown-";
     //  else $admin_by = $testing_row['administered_by'];
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $services_row['agency']),0,'L',1);
     $pdf->SetFont('Arial','I',8);
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $services_row['description']),0,'J',0);
     $pdf->Ln(2);
   }

   $pdf->Ln(5);
   //END Coordination of Services

   //BEGIN Assistive Technology
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Assistive Technology','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(220,220,220);
   $pdf->SetFillColor(220,220,220);

   $pdf->SetFont('Arial','I',8);
   while($at_row=mysql_fetch_array($at_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,' ','B',1,'R');
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $at_row['technology']) ,'B','J',0);
     $pdf->Ln(2);
   }
   $pdf->Ln(5);
   //END Assistive Technology

    //begin goal overview...
    $pdf->SetFont('Arial','B',14);
    $pdf->SetFillColor(204,255,255);
    $pdf->SetDrawColor(0,80,180);
    $pdf->MultiCell(0,5,'Goals Overview','B','L',0);
    $pdf->Ln(5);
    $pdf->SetDrawColor(0,0,0);
    $goal_row=mysql_fetch_array($long_goal_result);
    while($goal_row) {
      $pdf->SetFont('Arial','B',10);
      $current_area=$goal_row['area'];
      //output the current area:
      $pdf->Cell(10);
      $pdf->Cell(0,5,'Area: ' . iconv('UTF-8','Windows-1252', $goal_row['area']),0,1);
      $pdf->Ln(2);
      while($current_area==$goal_row['area']) {
         //output this goal
         $pdf->SetFont('Arial','',8);
         $x = $pdf->GetX(); $y = $pdf->GetY();
         if($goal_row['is_complete'] == 'Y') {
           $pdf->Image(IPP_PATH . 'images/checkmark_black.png',$x+12,$y,3);
         } else {
           $pdf->Image(IPP_PATH . 'images/arrow_black.png',$x+12,$y,3);
         }
         $pdf->Cell(15);
         $pdf->SetFont('Arial','B',8);
         $pdf->Cell(8,5,'Goal: ',0,0);
         $pdf->SetFont('Arial','',8);
         $pdf->MultiCell(150,5,preg_replace("/\\n/", " ",iconv('UTF-8','Windows-1252', $goal_row['goal'])),0,1);
         //add the objectives...
         $objective_query = "SELECT * FROM short_term_objective WHERE goal_id='" . mysql_real_escape_string($goal_row['goal_id']) . "' ORDER BY achieved ASC";
         $objective_result = mysql_query($objective_query);
         if(!$objective_result) {
            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objective_query'<BR>";
            $system_message=$system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         }
         $pdf->SetFont('Arial','',8);
         $pdf->SetFillColor(204,255,255);
         $pdf->SetDrawColor(0,80,180);
         while ($objective_row=mysql_fetch_array($objective_result)) {
             $pdf->Cell(15);
             $x = $pdf->GetX(); $y = $pdf->GetY();
             if($objective_row['achieved'] == 'Y') {
                $pdf->Image(IPP_PATH . 'images/checkmark_black.png',$x+6,$y,3);
             } else {
                $pdf->Image(IPP_PATH . 'images/arrow_black.png',$x+6,$y,3);
             }
             $pdf->Cell(10);
             $pdf->MultiCell(130,5,preg_replace("/\\n/", " ", iconv('UTF-8','Windows-1252', $objective_row['description'])),0,1);
         }
         $pdf->SetDrawColor(0,0,0);
         $goal_row=mysql_fetch_array($long_goal_result);
      }
    }

    $pdf->Ln(5);
    //end overview

    //move the pointer back...
    if(mysql_num_rows($long_goal_result)) { mysql_data_seek($long_goal_result,0); }
    //begin details...
    $pdf->SetFont('Arial','B',14);
    $pdf->SetFillColor(204,255,255);
    $pdf->SetDrawColor(0,80,180);
    $pdf->MultiCell(0,5,'Goals Details','B','L',0);
    $pdf->Ln(3);
    $pdf->SetDrawColor(0,0,0);

    $goal_row=mysql_fetch_array($long_goal_result);
    while($goal_row) {
      $pdf->SetFont('Arial','BU',14);
      $current_area=$goal_row['area'];
      //output the current area:
      $pdf->Cell(5);
      $pdf->Cell(0,5,$goal_row['area'],0,1);
      $pdf->Ln(5);
      while($current_area==$goal_row['area']) {
         //output this goal
         $pdf->SetFont('Arial','',14);
         $x = $pdf->GetX(); $y = $pdf->GetY();
         if($goal_row['is_complete'] == 'Y') {
           $pdf->Image(IPP_PATH . 'images/checkmark_black.png',$x+8,$y,3);
         } else {
           $pdf->Image(IPP_PATH . 'images/arrow_black.png',$x+8,$y,3);
         }
         $pdf->Cell(12);
         $pdf->SetFont('Arial','B',14);
         $pdf->Cell(15,5,'Goal: ',0,0);
         $pdf->SetFont('Arial','',14);
         $pdf->MultiCell(150,5,iconv('UTF-8','Windows-1252', $goal_row['goal']),0,1);
         //add the objectives...
         $objective_query = "SELECT * FROM short_term_objective WHERE goal_id='" . mysql_real_escape_string($goal_row['goal_id']) . "' ORDER BY achieved ASC";
         $objective_result = mysql_query($objective_query);
         if(!$objective_result) {
            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objective_query'<BR>";
            $system_message=$system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         }
         $pdf->SetFont('Arial','',12);
         $pdf->SetDrawColor(220,220,220);
         $pdf->SetFillColor(220,220,220);
         while ($objective_row=mysql_fetch_array($objective_result)) {
             $pdf->SetFont('Arial','',10);
             $pdf->Ln(5);
             $pdf->Cell(10);
             $x = $pdf->GetX(); $y = $pdf->GetY();
             if($objective_row['achieved'] == 'Y') {
                $pdf->Image(IPP_PATH . 'images/checkmark_black.png',$x+6,$y,3);
             } else {
                $pdf->Image(IPP_PATH . 'images/arrow_black.png',$x+6,$y,3);
             }
             $pdf->Cell(10);
             $pdf->MultiCell(0,5,iconv('UTF-8','Windows-1252', $objective_row['description']),0,1);
             if($objective_row['strategies'] != "") {

               //we print the 'strategies'
               $pdf->SetFont('Arial','B',10);
               $pdf->Ln(2);
               $pdf->Cell(30);
               $pdf->Cell(15,5,'Strategies:', 0,1);
               $pdf->SetFont('Arial','I',10);
               $pdf->Cell(30);
               $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $objective_row['strategies']),'TB','J',0);
             }

             if($objective_row['assessment_procedure'] != "") {
               //we print the 'assessement procedure'
               $pdf->SetFont('Arial','B',10);
               $pdf->Ln(2);
               $pdf->Cell(30);
               $pdf->Cell(15,5,'Assessment Procedure:', 0,1);
               $pdf->SetFont('Arial','I',10);
               $pdf->Cell(30);
               $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $objective_row['assessment_procedure']),'TB','J',0);
             }

             if($objective_row['results_and_recommendations'] != "") {
               //we print the 'results and recommendations'
               $pdf->SetFont('Arial','B',10);
               $pdf->Ln(2);
               $pdf->Cell(30);
               $pdf->Cell(15,5,'Progress Review:', 0,1);
               $pdf->SetFont('Arial','I',10);
               $pdf->Cell(30);
               $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $objective_row['results_and_recommendations']),'TB','J',0);
             }
             //$pdf->SetFont('Arial','',8);
             //$pdf->Cell(30);
             //$output = "Strategies:\n\n" . preg_replace("/\\n/", " ",$objective_row['strategies']) . "\n\nResults & Recommendations\n\n" . preg_replace("/\\n/", " ", $objective_row['results_and_recommendations']);
             //$pdf->MultiCell(150,5,$output,1,'L',1);
             //$pdf->SetFont('Arial','',10);
             //$pdf->Cell(0);
         }
         $pdf->SetDrawColor(0,0,0);
         $pdf->Ln(5);
         $goal_row=mysql_fetch_array($long_goal_result);
      }
    }
    $pdf->Ln(5);
    //end details


   //begin transition plan
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Transition Plans (Year End Recommendations):','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(0,0,0);

   $pdf->SetFont('Arial','I',8);
   $pdf->SetDrawColor(220,220,220);
   while($transition_row=mysql_fetch_array($transition_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,'Date: ' . $transition_row['date'],'B',1,'R');
     //$pdf->Ln(3);
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $transition_row['plan']),'B','J',0);
     $pdf->Ln(2);
   }
   //$pdf->Ln(5);
   //end transition plan

   //begin signature page
   $pdf->AddPage();
   $pdf->SetFont('Times','',20);
   $pdf->SetTextColor(220,50,50); //set the colour a loverly redish
   $pdf->Cell(30);
   $pdf->Cell(130,15,'Signatures',0,1,'C');
   $pdf->SetFont('Times','B',15);
   $pdf->SetTextColor(220,50,50); //set the colour a loverly redish
   $pdf->Cell(0,0,'- '. $student_row['first_name'] . " " . $student_row['last_name'] . ' -',0,0,'C');

   //disclaimer...
   $pdf->SetTextColor(255,0,0);
   $pdf->SetFont('Arial','BI',10);
   $pdf->Ln(8);
   $pdf->Cell(15);
   $pdf->MultiCell(160,5,'I understand and agree with the information contained in this Program Plan',0,1,'L');

   //Set colour back
   $pdf->Ln(5);
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->SetTextColor(0,0,0);
   $pdf->MultiCell(0,5,'Signatures (Review Date 1)','BI','L',0);
   $pdf->Ln(12);
   $pdf->SetDrawColor(0,0,0);
   $pdf->SetTextColor(153,153,153);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(5); $pdf->Cell(80,5,'Parent/Guardian                     Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'Coordinator/Teacher                 Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'Principal                                 Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'Student                                     Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'[                                          ]   Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'[                                          ]     Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'[                                          ]   Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'[                                          ]     Date','T',1,'C');
  
   //$pdf->Cell(50); $pdf->Cell(73,5,'Principal','T',1,'C');
   //$pdf->Ln(8);
   //$pdf->Cell(50);$pdf->Cell(73,5,'Signature Date','T',0,'C');

   $pdf->Ln(8);
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->SetTextColor(0,0,0);
   $pdf->MultiCell(0,5,'Signatures (Review Date 2)','BI','L',0);
   $pdf->Ln(12);
   $pdf->SetDrawColor(0,0,0);
   $pdf->SetTextColor(153,153,153);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(5); $pdf->Cell(80,5,'Parent/Guardian                     Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'Coordinator/Teacher                 Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'Principal                                 Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'Student                                     Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'[                                          ]   Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'[                                          ]     Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'[                                          ]   Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'[                                          ]     Date','T',1,'C');
  

   $pdf->Ln(8);
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->SetTextColor(0,0,0);
   $pdf->MultiCell(0,5,'Signatures (Review Date 3)','BI','L',0);
   $pdf->Ln(12);
   $pdf->SetDrawColor(0,0,0);
   $pdf->SetTextColor(153,153,153);
   $pdf->SetFont('Arial','I',10);
   $pdf->Cell(5); $pdf->Cell(80,5,'Parent/Guardian                     Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'Coordinator/Teacher                 Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'Principal                                 Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'Student                                     Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'[                                          ]   Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'[                                          ]     Date','T',1,'C');
   $pdf->Ln(8);
   $pdf->Cell(5); $pdf->Cell(80,5,'[                                          ]   Date','T',0,'C');$pdf->Cell(14,5,'',0);$pdf->Cell(80,5,'[                                          ]     Date','T',1,'C');
  
   //end signature page

  return $pdf;
}
  //$pdf->Output();

  //Determine a temporary file name in the current directory
  //echo IPP_PATH . "../temp_pdf/";
  //$file=basename(tempnam('/var/www/ssl/ipp/temp_pdf','tmp'));
  //Save PDF to file
  //$pdf->Output($file);
  //JavaScript redirection
  //echo "<HTML><SCRIPT>document.location='getpdf.php?f=$file';</SCRIPT></HTML>";

  //exit();
?>
