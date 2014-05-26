<?php

/** @file
 * @brief 	progress review
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		to be determined
 */
  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['student_id'] || $_PUT['student_id']
 *
 */

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
require_once(IPP_PATH . 'include/fpdf/fpdf.php');

//Header('Pragma: public, no-cache');
if (strstr($_SERVER['HTTP_USER_AGENT'],"MSIE 5.5")) { // had to make it MSIE 5.5 because if 6 has no "attachment;" in it it defaults to "inline"
    $attachment = "";
} else {
    $attachment = "attachment;";
}

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
    echo $system_message;
    exit();
    //just carry on
} else {
    $supervisor_row = mysql_fetch_array($supervisor_result);
}

//lets get some PDF making...
class year_end_review extends FPDF  //all this and OO too weeeeeeee

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
        $this->Image(IPP_PATH . 'images/logo_pb.png',10,8,50);
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
         $this->Cell(0,3,'IEP Progress Summary for ' . $student_row['first_name'] . ' ' . $student_row['last_name'] . ' (Page '.$this->PageNo().'/{nb})',0,1,'C');

         //output a little information on this
         $this->SetFont('Arial','i',6);
         $this->SetTextColor(153,153,153);  //greyish
         $this->SetFillColor(255,255,255);
         $this->Ln(1);
         $this->MultiCell(0,5,"Generated by Grasslands Public Schools Individual Program Plan (System �2005-2006 Grasslands Public Schools)",'T','C',1);

         //Set colour back
         $this->SetTextColor(0,0,0);  // Well, I'm back in black, yes I'm back in black! Ow!
     }
}

  //Instanciation of inherited class
  $pdf=new year_end_review();
  $pdf->AliasNbPages();
  $pdf->AddPage();

  $pdf->SetFont('Times','B',15);
  $pdf->SetTextColor(220,50,50); //set the colour a loverly redish
  $pdf->Cell(0,5,'IEP Progress Summary for ' . $student_row['first_name'] . ' ' . $student_row['last_name'],0,1,'C');
  //Set colour back
  $pdf->Ln(10);
  $pdf->SetTextColor(0,0,0);  // Well, I'm back in black, yes I'm back in black! Ow!


  $pdf->SetFont('Arial','I',8);
  $pdf->Cell(0,5,date('l dS \of F Y') ,0,1);
  $pdf->Cell(0,5,'Supervisor: ' . preg_replace("/\./", " ",$supervisor_row['egps_username']),0,1);
  $pdf->Cell(0,5,'Document Generated by: ' . preg_replace("/\./", " ",$_SESSION['egps_username']),0,1);
  $pdf->Ln(10);
  $pdf->SetFont('Arial','',12);
  if(!mysql_num_rows($long_goal_result)) {
    $pdf->Cell(0,5,'As of ' . date('F Y') . ', ' . $student_row['first_name'] . ', has not yet completed any goals' ,0,1);
  } else {
    //we have some goals completed...
    $pdf->MultiCell(0,5,'As of ' . date('F Y') . ', ' . $student_row['first_name'] . ', is progressing in the following area(s). Checkmarks indicate achieved objectives.' ,0,1);
    $pdf->Ln(10);

    //begin overview...
    $pdf->SetFont('Arial','B',14);
    $pdf->SetFillColor(204,255,255);
    $pdf->SetDrawColor(0,80,180);
    $pdf->MultiCell(0,5,'Progress Overview','B','L',0);
    $pdf->Ln(5);
    $pdf->SetDrawColor(0,0,0);
    $goal_row=mysql_fetch_array($long_goal_result);
    $top_overview= $pdf->GetY();
    while($goal_row) {
      $pdf->SetFont('Arial','B',10);
      $current_area=$goal_row['area'];
      //output the current area:
      $pdf->Cell(10);
      $pdf->Cell(0,5,'Area: ' . $goal_row['area'],0,1);
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
         $pdf->MultiCell(150,5,preg_replace("/\\n/", " ",$goal_row['goal']),0,1);
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
             $pdf->MultiCell(130,5,preg_replace("/\\n/", " ", $objective_row['description']),0,1);
         }
         $pdf->SetDrawColor(0,0,0);
         $goal_row=mysql_fetch_array($long_goal_result);
      }
    }
    $bottom_overview = $pdf->GetY();
    //temp output
    //$pdf->MultiCell(130,5,$top_overview . '-' . $bottom_overview,1,1);
    //output a box
    $pdf->Rect(18,$top_overview,173, $bottom_overview - $top_overview); //$bottom_overview-$top_overview);
    $pdf->Ln(5);
    //end overview

    //move the pointer back...
    mysql_data_seek($long_goal_result,0);
    //begin details...
    $pdf->SetFont('Arial','B',14);
    $pdf->SetFillColor(204,255,255);
    $pdf->SetDrawColor(0,80,180);
    $pdf->MultiCell(0,5,'Progress Details','B','L',0);
    $pdf->Ln(3);
    $pdf->SetDrawColor(0,0,0);

    $goal_row=mysql_fetch_array($long_goal_result);
    while($goal_row) {
      $pdf->SetFont('Arial','BU',12);
      $current_area=$goal_row['area'];
      //output the current area:
      $pdf->Cell(0,5,$goal_row['area'],0,1);
      $pdf->Ln(5);
      while($current_area==$goal_row['area']) {
         //output this goal
         $pdf->SetFont('Arial','',12);
         $x = $pdf->GetX(); $y = $pdf->GetY();
         if($goal_row['is_complete'] == 'Y') {
           $pdf->Image(IPP_PATH . 'images/checkmark_black.png',$x+8,$y,3);
         } else {
           $pdf->Image(IPP_PATH . 'images/arrow_black.png',$x+8,$y,3);
         }
         $pdf->Cell(12);
         $pdf->SetFont('Arial','B',12);
         $pdf->Cell(12,5,'Goal: ',0,0);
         $pdf->SetFont('Arial','',12);
         $pdf->MultiCell(0,5,$goal_row['goal'],0,1);
         //add the objectives...
         $objective_query = "SELECT * FROM short_term_objective WHERE goal_id='" . mysql_real_escape_string($goal_row['goal_id']) . "' ORDER BY achieved ASC";
         $objective_result = mysql_query($objective_query);
         if(!$objective_result) {
            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objective_query'<BR>";
            $system_message=$system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         }
         $pdf->SetFont('Arial','',10);
         $pdf->SetFillColor(204,255,255);
         $pdf->SetDrawColor(0,80,180);
         while ($objective_row=mysql_fetch_array($objective_result)) {
             $pdf->Ln(5);
             $pdf->Cell(10);
             $x = $pdf->GetX(); $y = $pdf->GetY();
             if($objective_row['achieved'] == 'Y') {
                $pdf->Image(IPP_PATH . 'images/checkmark_black.png',$x+6,$y,3);
             } else {
                $pdf->Image(IPP_PATH . 'images/arrow_black.png',$x+6,$y,3);
             }
             $pdf->Cell(10);
             $pdf->MultiCell(0,5,preg_replace("/\\n/", " ", $objective_row['description']),0,1);
             //we print the 'results and recommendations' and 'strategies'
             $pdf->SetFont('Arial','B',8);
             $pdf->Ln(2);
             $pdf->Cell(28);
             $pdf->Cell(15,3,'Strategies:', 0,1);
             $pdf->SetFont('Arial','I',8);
             $pdf->Cell(30);
             $pdf->MultiCell(120,5,preg_replace("/\\n/", " ",$objective_row['strategies']),1,'L',1);

             $pdf->SetFont('Arial','B',8);
             $pdf->Ln(2);
             $pdf->Cell(28);
             $pdf->Cell(15,3,'Results and Recommendations:', 0,1);
             $pdf->SetFont('Arial','I',8);
             $pdf->Cell(30);
             $pdf->MultiCell(120,5,preg_replace("/\\n/", " ",$objective_row['results_and_recommendations']),1,'L',1);

             $pdf->SetFont('Arial','',10);
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
    //end overview

  }
  //for($i=1;$i<=40;$i++)
  //   $pdf->Cell(0,10,'Printing line number '.$i,0,1);
  //@#$@#$% this stupid thing took me hours...need this for SSL file
//send in IE (6?) becuase the @#$@#$in thing automatically decides
//the headers.
header("Pragma: ");
header("Cache-Control: ");

header("Content-Length: " . strlen($pdf->Output("ignored",'S')));
header("Content-type: application/pdf");
$filename="Progress Review";
header("Content-disposition: $attachment filename=\"{$filename}\"");

$pdf->Output();

  exit();
?> 

