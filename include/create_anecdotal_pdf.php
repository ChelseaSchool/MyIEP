<?php

/** @file
 * @brief Create PDF of anecdotals for this student's IEP
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 */


//the authorization level for this page!
//$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody


/** @remark
 *  INPUTS: $_GET['student_id'] || $_PUT['student_id']
 *
 */



$system_message = "";

//define('IPP_PATH','../');

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

/** @fn	create_anecdotals($student_id)
 * 
 * @brief creates anecdotal PDF given $student_id
 * @param $student_id
 * @todo
 * 1. Look at this more closely. There's a class in here.
 */
function create_anecdotals($student_id) {

  global $system_message,$student_row;

  $student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
  $student_result = mysql_query($student_query);
  if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {$student_row= mysql_fetch_array($student_result);}

  //get the goals...
  $anecdotal_query = "SELECT * FROM anecdotal WHERE student_id=$student_id  ORDER BY date ASC";
  $anecdotal_result = mysql_query($anecdotal_query);
  if(!$anecdotal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$anecdotal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR'); //todo investigate and expand logging; implement email alerts on certain log key words
    echo $system_message;
    exit();
  }




  //get the coding and history...
  $code= "";
  $code_text = "";
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
    }
  }


  //lets get some PDF making done...
  class IPP extends FPDF  //all this and OO too weeeeeeee  Note: example of coder notes to self cf Art of Commenting

  {
     //Page header
     function Header()
     {
        global $IPP_ORGANIZATION,$student_row,$IPP_ORGANIZATION_ADDRESS1,$IPP_ORGANIZATION_ADDRESS2,$IPP_ORGANIZATION_ADDRESS3;
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
        $this->Image(IPP_PATH . 'images/logo_pb.png',10,8,50); // todo: html folk should not icon location for customization features
        //Line break
        $this->Ln(15);
        //Set colour back
         $this->SetTextColor(153,153,153);  // Well, I'm back in black, yes I'm back in black! Ow!  Todo: note again nature of comment
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
         $this->Cell(0,3,'Anecdotal report for ' . $student_row['first_name'] . ' ' . $student_row['last_name'] . '-' . date('dS \of F Y') . ' (Page '.$this->PageNo().'/{nb})',0,1,'C');

         //output a little information on this
         $this->SetFont('Arial','i',6);
         $this->SetTextColor(153,153,153);  //greyish
         $this->SetFillColor(255,255,255);
         $this->Ln(1);
         $this->MultiCell(0,5,"Grasslands Individual Program Plan System (�2005-2006 Grasslands Public Schools)",'T','C',1); //todo: this should be a variable controlled on admin console

         //Set colour back
         $this->SetTextColor(0,0,0);  // Well, I'm back in black, yes I'm back in black!  Todo: note the dev listened to ACDC
     }
}

  //Instanciation of inherited class
  $pdf=new IPP();
  $pdf->AliasNbPages();
  $pdf->AddPage();

  //set the pdf information
  $pdf->SetAuthor(username_to_common($_SESSION['egps_username']));
  $pdf->SetCreator('Grasslands IPP System- Michael Nielsen Developer'); //todo strip the vanity of the dev
  $pdf->SetTitle('Individual Program Plan - ' . $student_row['first_name'] . ' ' . $student_row['last_name']);


  //begin pdf...
  $pdf->SetFont('Times','',20);
  $pdf->SetTextColor(220,50,50); //set the colour a loverly redish
  $pdf->Cell(30);
  $pdf->Cell(130,5,'Anecdotal Report ',0,0,'C');
  $pdf->Image(IPP_PATH . 'images/bounding_box.png',$pdf->GetX()-1,$pdf->GetY()-4);
  $mark = $pdf->GetY();
  if($code != "")
    if($code > 99) {
       $pdf->SetFont('Times','B',30);
    } else {
       $pdf->SetFont('Times','B',50);
    }
  else {
    $pdf->SetFont('Times','B',10);
    $code=" Not\nCoded";
  }
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
   $pdf->Cell(30,5,'AB Ed. Number: ',0,0);
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
   //$pdf->Cell(0,5,$current_grade,0,1);
   $pdf->Cell(0,5,"",0,1);

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


   //BEGIN Anecdotals information
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Anecdotal Information','B','L',0);
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
   $pdf->Ln(5);
   //END Anecdotals information

   //BEGIN Anecdotal reports Coding
   $pdf->SetFont('Arial','B',14);
   $pdf->SetFillColor(204,255,255);
   $pdf->SetDrawColor(0,80,180);
   $pdf->MultiCell(0,5,'Anecdotals','B','L',0);
   $pdf->Ln(5);
   $pdf->SetDrawColor(220,220,220);
   $pdf->SetFillColor(220,220,220);

   $pdf->SetFont('Arial','I',8);
   while($anecdotal_row=mysql_fetch_array($anecdotal_result)) {
     $pdf->Cell(30);
     $pdf->Cell(120,3,'Date: ' . $anecdotal_row['date'], 'B',1,'R');
     $pdf->SetFont('Arial','B',8);
     $pdf->Cell(30);
     $pdf->MultiCell(120,5,iconv('UTF-8','Windows-1252', $anecdotal_row['report']),0,'L',0); //todo note encoding here is utf-8. standardize charset
     $pdf->Ln(2);
   }

   $pdf->Ln(5);
   //END Anecdotal reports

  return $pdf;
}
?>
