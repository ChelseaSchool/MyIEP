<?php

/** @file
 * @brief 	About the application and developers; link to Chelsea School
 * @authors		Rik Goldman <rgoldman@chelseaschool.edu, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @copyright 	2014 Chelsea School
 * @copyright 	2005 Grasslands Regional Division #6
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 */

/**
 * @mainpage
 * MyIEP Documentation
 *
 * @todo 1. Deploy new functions
 *       2. Move js (Javascript files) into JS folder and change all references
 *       3. Move css out of style folder and into css folder (standard)
 */

// the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; // anybody

/**
 * Path for IPP required files.
 */

define('IPP_PATH', './');
require_once 'etc/init.php';
require_once 'include/supporting_functions.php';

header('Pragma: no-cache'); // don't cache this page!

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="About MyIEP">
<meta name="author" content="Rik Goldman">
<link rel="shortcut icon" href="./assets/ico/favicon.ico">

<title>MyIEP</title>

<!-- Bootstrap core CSS -->
<link href="./css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="./css/jumbotron.css" rel="stylesheet">

<!-- Just for debugging purposes. Don't actually copy this line! -->
<!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>


    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span> <span
                        class="icon-bar"></span> <span class="icon-bar"></span> <span
                        class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="main.php">MyIEP</a>
            </div>
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li><a href="main.php" title="Return Home"><span
                            class="glyphicon glyphicon-home"></span></a></li>
                    <li class="active"><a href="about.php" title="About MyIEP"><span
                            class="glyphicon glyphicon-info-sign"></span></a></li>
                    <li><a href="sprint_feedback.php" title="Leave User Feedback"><span
                            class="glyphicon glyphicon-envelope"></span></a></li>
                    <li><a href="help.php" title="Some Help Here"><span
                            class="glyphicon glyphicon-question-sign"></span></a></li>
                    <li><a href="index.php" title="Logout of MyIEP"><span
                            class="glyphicon glyphicon-off"></span></a></li>
                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown"><a href="#" class="dropdown-toggle"
                        data-toggle="dropdown">Administration <b class="caret"></b></a>
                        <ul class="dropdown-menu">

                            <li><a href="./manage_student.php">Students</a></li>
                            <li class="divider"></li>
                            <li><a href="change_ipp_password.php">Reset Password</a></li>
                            <!-- <li><a href="superuser_add_goals.php">Goals Database</a></li>-->
                            <li><a href="./student_archive.php">Archive</a></li>
                            <li><a href="./user_audit.php">Audit</a></li>
                            <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                            <li><a href="school_info.php">Manage Schools</a></li>
                            <li><a href="superuser_view_logs.php">View Logs</a></li>

                        </ul></li>
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
    <div class="jumbotron">
        <div class="container">
            <h1>About MyIEP</h1>
            <p>
                MyIEP (Version
                <?php echo $IPP_CURRENT_VERSION; ?>
                ) was originally developed as IEP-IPP through the coordinated
                efforts of many people at Grasslands Public Schools.
            </p>
            <p>
                MyIEP is currently under development by students, faculty, and
                administrators working in coordination at <a
                    href="http://chelseaschool.edu">Chelsea School</a> in Hyattsville,
                MD.
            </p>
            <p>
                <a class="btn btn-lg btn-primary" href="main.php" role="button">Return
                    to MyIEP &raquo;</a>
            </p>
        </div>
    </div>
    <!-- /container -->
    <div class="container">

        <!-- Row 1 -->
        <div class="row">
            <!-- Left column -->
            <div class="col-md-4">
                <h1>What's New</h1>
                <h3>Bug Fixes</h3>
                <ul>
                    <li>Backslashes accumulatin in progress reports for student
                        objectives is resolved.</li>
                    <li>Strengths &amp; Weaknesses narratives were truncated when
                        pasted from certain word processors. We believe this is resolved
                        for the most common characters.</li>
                </ul>
                <h3>New Features</h3>
                <ul>
                    <li>Navigation Icons on most pages</li>
                    <li>Almost entirely best-practice security methods implemented</li>
                    <li>Request a new password directly from the logon page - a new,
                        temporary, password will be sent to your email address of record.</li>
                    <li>jQuery Date Picker</li>
                    <li>User Interface/User Experience Improvements</li>
                    <p>The new user interface is responsive (adjusts intelligently to
                        the size a browser window).</p>
                    <ul>
                        <li>Main Menu</li>
                        <li>Most Student Information Pages, including Goals and Objectives</li>
                        <li>Most administration pages</li>
                    </ul>
                </ul>
            </div>
            <!-- Middle column -->
            <div class="col-md-4">
                <h1>Credits</h1>
                <h3>MyIEP</h3>
                <p>Rik Goldman, Sabre Goldman, Jason Banks, Alex, Tristan, Micah,
                    Paul, Kenny, Stephen, Jonathan, James, Bryan, Andre, Lucas.</p>
                <p>
                    <a class="btn btn-default"
                        href="https://github.com/ChelseaSchool/MyIEP" role="button">MyIEP
                        Source Code on Github &raquo;</a>
                </p>
                <h3>Legacy Code</h3>
                <p>M. Nielson</p>
                <p>
                    <a class="btn btn-default" href="http://www.iep-ipp.com/"
                        role="button">Nielson's Legacy Source Code (IEP-IPP)</a>
                </p>
            </div>
            <!-- Right column -->
            <div class="col-md-4">
                <h1>Copyright</h1>
                <h3>MyIEP</h3>
                <p>&copy; 2014 Chelsea School</p>

                <address>
                    <strong>Chelsea School</strong><br> 2970 Belcrest Center Drive -
                    Suite 300<br> Hyattsville, Maryland 20782
                </address>
                <p>
                    <a class="btn btn-default" href="http://chelseaschool.edu"
                        role="button">Chelsea School &raquo;</a>
                </p>

                <h3>Legacy Code</h3>
                <p>
                    &copy; 2004 <a href="http://www.grasslands.ab.ca/Schools.php"
                        target="_blank">Grasslands Regional Public Schools #6</a>
                </p>
            </div>
        </div>
        <!-- End Row -->
        <!-- New Row -->
        <div class="row">
            <!--  Left Column -->
            <div class="col-md-4">
                <h1>License</h1>
                <p>
                    This program is <a href="#" data-toggle="modal"
                        data-target="#freesoftware" title="What is Meant by Free Software"><em>free
                            software</em></a>; you can redistribute it and/or modify it under
                    the terms of the GNU General Public License as published by the
                    Free Software Foundation; either version 2 of the License, or (at
                    your option) any later version.

                </p>
                <p>This program is distributed in the hope that it will be useful,
                    but WITHOUT ANY WARRANTY; without even the implied warranty of
                    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
                    General Public License for more details.</p>
                <p>You should have received a copy of the GNU General Public License
                    along with this program; if not, write to:</p>
                <address>
                    <p>
                        <strong>The Free Software Foundation, Inc.</strong>
                    </p>
                    <p>
                        59 Temple Place, Suite 330, Boston, <abbr title="Massachussetes">MA</abbr>
                        02111-1307
                    </p>
                    <p>USA</p>
                </address>
                <p>
                    <a href="https://www.gnu.org/philosophy/free-sw.html"
                        target="_blank" type="button" class="btn btn-default">Read "What
                        is Free Software"</a>&nbsp;<a class="btn btn-default"
                        href="http://www.gnu.org/licenses/gpl-2.0.html" role="button">GPLv2
                        License &raquo;</a>
                </p>
            </div>
            <!-- Middle Column, Second Row -->
            <div class="col-md-4">
                <h1>Backlog (To Do)</h1>
                <ul>
                    <li>Continue to eliminate repeated code blocks</li>
                    <li>Modify authentication mechanism</li>
                    <li>Thorough code documenation in PHPDocumentor codeblocks</li>
                    <li>Complete bootstrap UI across all pages - almost there</li>
                    <li>Standardize variable and function names</li>
                    <li>Filter and escape within authenticated pages: Outside pages are
                        secure</li>
                    <li>HTML5 Form Enhancement (color code for success completing a
                        field)</li>
                    <li>Better error catching</li>
                    <li>Form receipts</li>
                    <li>More communication via system email</li>
                </ul>
            </div>
            <!-- Second Row, Right Column -->
            <div class="col-md-4">
                <h1>Development Process</h1>
                <p>Developed by students in Information Systems Management and Web
                    Design &amp; Development courses at Chelsea School.</p>
                <ul>
                    <li>Developed using Scrum Framework (an <a
                        href="http://agilemanifesto.org/" title="Agile Manifesto">Agile</a>
                        methodology)
                    </li>
                    <li>HTML5 &amp; CSS3</li>
                    <li>Vagrant &amp; Virtualbox Development Environment</li>
                    <li>Source Code Management (SCM) with Git and Github</li>
                    <li>Apache web-server administration</li>
                    <li>Data processing with PHP</li>
                    <li>Data storage with MySQL</li>
                    <li>Active web pages with jQuery and JavaScript</li>
                    <li>Responsive design (in progress) with Bootstrap</li>
                </ul>
                <p>
                    <a href="http://agilemanifesto.org/principles.html"
                        title="Agile Principles" class="btn btn-default" role="button">Our
                        Shared Development Values</a>
                </p>

            </div>
        </div>

        <!-- Free Software Model -->

        <div class="modal fade" id="freesoftware" tabindex="-1" role="dialog"
            aria-labelledby="basicModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="myModalLabel">What is Meant by Free
                            Software</h4>
                    </div>
                    <div class="modal-body">
                        <p>Free software, software libre, or libre software is computer
                            software that is distributed along with its source code, and is
                            released under a software license that guarantee users the
                            freedom to run the software for any purpose as well as to study,
                            adapt/modify, and distribute the original software and the
                            adapted/changed versions.</p>

                        <p>Free software is often developed collaboratively by volunteer
                            computer programmers.</p>
                        <p>
                            Free software differs from <em>proprietary software</em> (such as
                            Microsoft Windows), which to varying degrees does not give the
                            user freedoms to study, modify and share the software, and
                            threatens users with legal penalties if they do not conform to
                            the terms of restrictive software licenses.
                        </p>
                        <p>Proprietary software is usually sold as a binary executable
                            program without access to the source code, which prevents users
                            from modifying and patching it, and results in the user becoming
                            dependent on software companies (vendor lock-in) to provide
                            updates and support.</p>
                        <p>
                            Free software is also distinct from <em>freeware</em>, which does
                            not require payment for use, but includes software where the
                            authors or copyright holders of freeware have retained all of the
                            rights to the software, so that it is not necessarily permissible
                            to reverse engineer, modify, or redistribute freeware.

                        </p>
                        <p>
                            Thus, free software is primarily <em>a matter of liberty, not
                                price</em>: users are free to do whatever they want with it –
                            this includes the freedom to redistribute the software
                            free-of-charge, or to sell it (or related services such as
                            support or warranty) for profit.
                        </p>
                        <p class="pull-right">--Wikipedia</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap core JavaScript
    ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="js/jquery-2.1.0.min.js" type="text/javascript"></script>
        <script src="./js/bootstrap.min.js" type="text/javascript"></script>
        <hr>
        <?php print_complete_footer(); ?>

    </div>
</BODY>
</HTML>
