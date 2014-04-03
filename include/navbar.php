<?php
/** @file
 *  @brief		Legacy navbar function included in other files
 *  @todo
 *  1. Refactor: Navbar doesn't need to be isolated from other included functions
 *  2. Refactor: Create a new navbar function for new UI/UX
 *  @copyright 2014 Chelsea School
 *  @copyright Grasslands Public Schools #6
 *  @copyright License: GPLv2
 *  @author	M. Nielson  
 */

   /** @fn navbar($szBack="main.php")
    *  @brief Creates the navbar in the legacy code; includes user name, student, and access level
    * 
    * @param $szBack
    */ 

	function navbar($szBack="main.php") {
        // outputs the navigation bar
        global $student_id,$our_permission;

        echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\"><tr>";
        //home button
        echo "<td width=\"60\" class=\"navbar_left\"><a href=\"" . IPP_PATH . "main.php\" tabindex=\"-1\"><img src=\"" . IPP_PATH . "images/homebutton.png\" border=\"0\"></a></td>";
        echo "<td width=\"60\" class=\"navbar_middle\">";
        //back button
        echo "<a href=\"" . IPP_PATH . "$szBack\" tabindex=\"-1\">";
        echo "<img src=\"" . IPP_PATH . "images/back-arrow.png\" border=\"0\"></a></td>";
        echo "<td width=\"60\" align=\"right\" class=\"navbar_middle\"><a href=\"" . IPP_PATH . "about.php\" tabindex=\"-1\"><img src=\"" . IPP_PATH . "images/about.png\" border=\"0\"></a></td>";
        echo "<td valign=\"middle\" align=\"center\" class=\"navbar_middle\">Logged&nbsp;in&nbsp;as:&nbsp;<b>" . $_SESSION['egps_username'] . "</b><BR>";
        if($our_permission != "") echo "Page&nbsp;access&nbsp;rights:&nbsp;<b>$our_permission</b>";
        echo "</td>";
        //student view button
        if(!$student_id)
        echo "<td width=\"60\" class=\"navbar_middle\"><img src=\"" . IPP_PATH . "images/studentinfo-inactive.png\" border=\"0\"></a></td>";
        else echo "<td width=\"60\" class=\"navbar_middle\"><a href=\"" . IPP_PATH . "student_view.php?student_id=$student_id\" tabindex=\"-1\"><img src=\"" . IPP_PATH . "images/studentinfo.png\" border=\"0\"></a></td>";
        //echo "<img src=\"" . IPP_PATH . "images/studentinfo.png\" border=\"0\"></a></td>";
        
        if(!$student_id)
        echo "<td width=\"60\" class=\"navbar_middle\"><img src=\"" . IPP_PATH . "images/view_ipp_60x60-inactive.png\" border=\"0\"></a></td>";
        else
        echo "<td width=\"60\" align=\"right\" class=\"navbar_middle\"><a href=\"" . IPP_PATH . "ipp_pdf.php?student_id=$student_id\" target=\"_blank\" tabindex=\"-1\"><img src=\"" . IPP_PATH . "images/view_ipp_60x60.png\" border=\"0\"></a></td>";
        echo "<td width=\"60\" align=\"right\" class=\"navbar_right\"><a href=\"" . IPP_PATH . "\" tabindex=\"-1\"><img src=\"" . IPP_PATH . "images/logout.png\" border=\"0\"></a></td>";
        echo "</tr></table>";
        return;
    }
?>
