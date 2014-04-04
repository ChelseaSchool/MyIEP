<?php 
/** @file
 *  @brief Functions to make process of overhauling UI more efficient
 */

/** @fn print_bootstrap_head()
 * @brief Bootstrap Dependencies
 * 
 * Just core and Jumbotron
 */

/**fn print_bootrap_head()
 * @brief stuff for jumbotron and bootstrap.min.css to go in html head.
 * @remark doesn't require echo
 */
function print_bootrap_head(){
	$bootstrap_depends=<<<EOF
	   <!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./css/jumbotron.css" rel="stylesheet">
	<style type="text/css">body { padding-bottom: 70px; }</style>
EOF;
  echo $bootstrap_depends;
}

/** @fn print_student_navbar($student_id)
 * @brief Outputs HTML student context navbar (bootstrap)
 * @remark Remember, use echo
 * @param int $student_id
 * @return NULL|string
 */
function print_student_navbar($student_id) {
	$student_nav = <<<EOF
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="container">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" href="main.php">MyIEP</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li><a href="main.php">Home</a></li>
<li><a href="index.php">Logout</a></li>
<li><a href="about.php">About</a></li>
<li><a href="help.php">Help</a></li>
<li><a onclick="history.go(-1);">Back</a></li>
	<li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Student Records<b class="caret"></b></a>
              <ul class="dropdown-menu">
              	<li><a href="long_term_goal_view.php?student_id=$student_id">Goals</a></li>
              	<li class="divider"></li>
              	<li><a href="guardian_view.php?student_id=$student_id">Guardians</a></li>
              	<li><a href="strength_need_view.php?student_id=$student_id">Strengths &amp; Needs</a></li>
              	<li><a href="coordination_of_services.php?student_id=$student_id">Coordination of Services</a></li>
              	<li><a href="achieve_level.php?student_id=$student_id">Achievement Level</a></li>
              	<li><a href="edical_info.php?student_id=$student_id">Medical Information</a></li>
              	<li><a href="medication_view.php?student_id=$student_id">Medication</a></li>
              	<li><a href="testing_to_support_code.php?student_id=$student_id">Support Testing</a></li>
              	<li><a href="background_information.php?student_id=$student_id">Background Information</a></li>
              	<li><a href="year_end_review.php?student_id=$student_id">Year-End Review</a></li>
              	<li><a href="anecdotals.php?student_id=$student_id">Anecdotals</a></li>
              	<li><a href="assistive_technology.php?student_id=$student_id">Assistive Techology</a></li>
              	<li><a href="transition_plan.php?student_id=$student_id">Transition Plan</a></li>
              	<li><a href="accomodations.php?student_id=$student_id">Accomodations</a></li>
              	<li><a href="snapshots.php?student_id=$student_id">Snapshots</a></li></ul>
            </ul>
             
          <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php">Logout</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./manage_student.php">Students</a></li>
                <li class="divider"></li>
                <li><a href="change_ipp_password.php">Reset Password</a></li>
                <li><a href="superuser_add_goals.php">Goals Database</a></li>
                <li><a href="student_archive.php">Archive</a></li>
                <li><a href="user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              </ul>
            </li>
          </ul>
         </div>
         <!--/.nav-collapse -->
        <!--<div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form" nctype="multipart/form-data" action="jumbotron.php" method="post">
            <div class="form-group">
              <input type="text" placeholder="User Name" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control" name="PASSWORD" value="">
            </div>
            <button type="submit" value="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
EOF;
	return $student_nav;	
} 

function print_bootstrap_datepicker_depends() {

	$dependencies = <<<EOF
	<!-- Example Invokation of Datepicker -->
	<!-- input type=datepicker name="review_date" id="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd"  -->
	<!-- Bootstrap Datepicker CSS -->
	<link href="./css/datepicker.css" rel="stylesheet">
	 <!-- jQuery Libraries -->
	 <script src="//code.jquery.com/jquery-1.9.1.js"></script>
	 <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
	 
	 <script type="text/javascript" src="./js/bootstrap-datepicker.js">$('.datepicker').datepicker()</script>	
	 <!-- jQuery Intantiation -->
	 <script>
	$(function() {
	$( "#datepicker" ).datepicker();
	});
	</script>
EOF;
   echo $dependencies;
}

/** @fn print_bootsrap_js()
 *  @brief Prints JavaScript references that bootsrap relies on
 *  @remark
 *  1. Already Echoes
 *  2. Goes within HTML, but at the very bottom to increase load time
 * 
 */
function print_bootstrap_js(){
	$bootsrapjs=<<<EOF
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="./js/bootstrap.min.js"></script>   
<script type="text/javascript" src="./js/jquery-ui-1.10.4.custom.min.js"></script>
EOF;
	echo $bootsrapjs;
}

/** @fn print_general_navbar()
 * @brief Outputs HTML general context navbar (bootstrap)
 * @remark Remember, use echo
 * @param int $student_id
 * @return NULL|string
 */
function print_general_navbar() {
	$general_nav = <<<EOF
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="container">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" href="main.php">MyIEP</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li><a href="main.php">Home</a></li>
<li><a href="index.php">Logout</a></li>
<li><a href="about.php">About</a></li>
<li><a href="help.php">Help</a></li>
<li><a onclick="history.go(-1);">Back</a></li>
       
          <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php">Logout</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./manage_student.php">Students</a></li>
                <li class="divider"></li>
                <li><a href="change_ipp_password.php">Reset Password</a></li>
                <li><a href="superuser_add_goals.php">Goals Database</a></li>
                <li><a href="student_archive.php">Archive</a></li>
                <li><a href="user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              </ul>
            </li>
          </ul>
         </div>
         <!--/.nav-collapse -->
        <!--<div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form" nctype="multipart/form-data" action="jumbotron.php" method="post">
            <div class="form-group">
              <input type="text" placeholder="User Name" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control" name="PASSWORD" value="">
            </div>
            <button type="submit" value="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
EOF;
	return $general_nav;
}
?>