<?php
/** @file
 * @brief 	login page
 *
 * Index page and loging page - perhaps should be separated. Login form and about MyIEP summary.
 *
 * @copyright 	2014 Chelsea School
 *
 * @copyright 	2005 Grasslands Regional Division #6
 *
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 *
 * @author		M. Nielson
 *
 * @todo
 * #. add alert for failed login attempt
 * #. link to support url
 * #. limit failed login attempts
 * #. session security
 * #. limit password resets
 * #. docblock style comments
 *
 */

//ini_set('display_errors',1);
//error_reporting(E_ALL);
if (! defined('IPP_PATH')) {
    define('IPP_PATH', './');
}
// check if we are running install wizard
if (! is_file("./etc/init.php")) {
    include_once IPP_PATH . 'install/index.php';
    exit();
}
/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/supporting_functions.php';

logout();
header('Pragma: no-cache'); // don't cache this page!

if (isset($MESSAGE))
    $MESSAGE = $MESSAGE;
else
    $MESSAGE = "";
if (isset($LOGIN_NAME))
    $LOGIN_NAME = $LOGIN_NAME;
else
    $LOGIN_NAME = "";

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="Rik Goldman">
<link rel="shortcut icon" href="./assets/ico/favicon.ico">
<TITLE><?php echo $page_title; ?></TITLE>
<SCRIPT>
function msieversion()
{
    var ua = window.navigator.userAgent
    var msie = ua.indexOf ( "MSIE " )

    if ( msie > 0 )      // If Internet Explorer, return version number
        return parseInt (ua.substring (msie+5, ua.indexOf (".", msie )))
        else                 // If another browser, return 0
        return 0

}
</SCRIPT>

<!-- Bootstrap core CSS -->
<link href="./css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->

<link href="./css/jumbotron.css" rel="stylesheet">




</HEAD>
<BODY>
    <!-- Jumbo Stock Nav -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span> <span
                        class="icon-bar"></span> <span class="icon-bar"></span> <span
                        class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"> MyIEP</a>
            </div>
            <div class="navbar-collapse collapse">
                <form class="navbar-form navbar-right" role="form"
                    action="<?php echo IPP_PATH . 'main.php'; ?>" method="post">
                    <div class="form-group">
                        <input name="LOGIN_NAME" autofocus required autocomplete="off"
                            type="text" placeholder="User Name" name="LOGIN_NAME"
                            value="<?php echo $LOGIN_NAME;?>" class="form-control"
                            value="<?php echo $LOGIN_NAME;?>">
                    </div>
                    <div class="form-group">
                        <input name=PASSWORD required autocomplete="off" type="password"
                            placeholder="Password" class="form-control" name="PASSWORD"
                            value="" placeholder="Password">
                    </div>
                    <button type="submit" name="sign-in" value="sign-in"
                        class="btn btn-success">Sign in</button>

                </form>
            </div>
            <!--/.navbar-collapse -->
        </div>
    </div>



    <!-- End Navbar -->

    <div class="jumbotron">
        <div class="container">
            <noscript>
                <div class="alert alert-block alert-danger">
                    <a href="#" class="close" data-dismiss="alert"> &times;</a>
                    <p>JavaScript is required to make use of MyIEP. It looks like
                        JavaScript is disabled in your browser. Please enable JavaScript
                        to continue.</p>
                </div>
            </noscript>
            <!-- Detect IE -->
            <SCRIPT LANGUAGE="javascript">
   if ( msieversion() >= 1 )

      document.write ( "<div class=&quot; well alert alert-block alert-danger&quot;><a href=# class=&quot;close&quot; data-dismiss=&quot;alert&quot;>&times;</a><p>Internet Explorer is your detected browser. Please note that MyIEP is <strong>not</strong> designed with support for Internet Explorer.</p></div>" );
   </SCRIPT>

<?php



if (isset($system_message)) {
        echo "<div class=\"alert alert-block alert-danger\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>
            <strong>" . $system_message . "</strong> You may contact an administrator or try resetting your credentials using
            the Reset Password button below.
            </div>";
}
?>

 <h1>About MyIEP</h1>
            <p>MyIEP (Version <?php echo $IPP_CURRENT_VERSION; ?>) was originally developed as IEP-IPP through the coordinated efforts of many people at Grasslands Public Schools.</p>
            <p>
                MyIEP is under development by students, faculty, and administrators
                at <a href="http://chelseaschool.edu">Chelsea School</a> in
                Hyattsville, MD.
            </p>
            <h1>Stuck?</h1>
            <p>
                <a class="btn btn-primary btn-lg" href="new_credentials.php"
                    role="button">Reset Password &raquo;</a>
            </p>
        </div>
        <!-- end container -->
    </div>
    <!-- End Jumbotron -->
    <div class="container">

        <!-- Row 1 -->
        <div class="row">
            <!-- Left column -->
            <div class="col-md-4">
                <h1>What's New</h1>
                <h3>Bug Fixes</h3>
                <ul>
                    <li>Backslashes accumulating in progress reports for student
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
                        the size a browser window).

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
                        role="button">Nielson's Legacy Source Code (IEP-IPP) &raquo;</a>
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
                        target="_blank">Grasslands Regional Public Schools #6 &raquo;</a>
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

                <p>This program is distributed in the hope that it will be useful,
                    but WITHOUT ANY WARRANTY; without even the implied warranty of
                    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
                    General Public License for more details.

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
                        is Free Software" &raquo;</a>&nbsp;<a class="btn btn-default"
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
                    Design &amp; Development courses at Chelsea School.

                <ul>
                    <li>Developed using Scrum Framework (an <a
                        href="http://agilemanifesto.org/" title="Agile Manifesto">Agile</a>
                        methodology)
                    </li>
                    <li>HTML5 &amp; CSS3</li>
                    <li>Vagrant &amp; Virtualbox Development Environment</li>
                    <li>Source Code Management (SCM) with Git and <a
                        href="http://github.com">Github</a></li>
                    <li>Apache web-server administration</li>
                    <li>Data processing with PHP</li>
                    <li>Data storage with MySQL</li>
                    <li>Active web pages with jQuery and JavaScript</li>

                    <li><a href="#" data-toggle="tooltip" data-placement="bottom"
                        title="Responsive web design (RWD) is a web design approach aimed at crafting sites to provide an optimal viewing experience—easy reading and navigation with a minimum of resizing, panning, and scrolling—across a wide range of devices (from mobile phones to desktop computer monitors).">Responsive
                            design</a> (in progress) with <a href="http://getbootstrap.com">Bootstrap
                            3</a></li>
                </ul>
                <p>
                    <a href="http://agilemanifesto.org/principles.html"
                        title="Agile Principles" class="btn btn-default" role="button">Our
                        Shared Development Values &raquo;</a>
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
                            computer programmers.

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
        <hr>
<?php print_complete_footer(); ?>
    <!-- Bootstrap core JavaScript
    ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="js/jquery-2.1.0.min.js"></script>
        <script src="js/bootstrap.min.js"></script>

</BODY>
</HTML>
