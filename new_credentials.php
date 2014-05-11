<?php

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody


$_POST=array();
if (session_name("Credential Reset")) {
	die ("Please do not make repeated attempts to create access credentials for this system. Click <a href=\"index.php\">here</a> to exit to the main page.");
}		





require_once 'include/supporting_functions.php';

no_cash();

print_html5_primer();
print_bootstrap_head();
?>
<script src="js/jquery-2.1.1.js"></script>
<title>Request New Credentials</title>
<script type="text/javascript">
$(document).ready(function() {
	$('#validate').hide();
})
	
$(document).ready (function() {
	$('.email').change(function() {
		var email1 = $('#email1').val();
		var email2 = $('#email2').val();
		var match = "";
		match = (email1 == email2);
		if (match) {
			$('#mustmatch').hide();
			$('#validate').show();	
		}
		
	});	
	
});

</script>
</head>
<body>
<!-- Jumbo Stock Nav --> 
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">MyIEP</a>
          </div>
        <div class="navbar-collapse collapse">
          <!-- <form class="navbar-form navbar-right" role="form" action="<?php echo IPP_PATH . 'main.php'; ?>" method="post">
            <div class="form-group">
              <input name="LOGIN_NAME" autofocus required autocomplete="off" type="text" placeholder="User Name" name="LOGIN_NAME" value="<?php echo $LOGIN_NAME;?>" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input name=PASSWORD required autocomplete="off" type="password" placeholder="Password" class="form-control" name="PASSWORD" value="" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-success">Sign in</button>
            
          </form>-->
        </div><!--/.navbar-collapse -->
      </div>
    </div>    
    
       
        
 <!-- End Navbar -->

<div class="jumbotron">
<h1>Password Reset Request</h1>
<h2>User Validation</h2>       
</div><!-- End Jumbotron -->
<div class="container">

<form method="post" name="person_confirmer" role="form" enctype="multipart/form-data" action="new_credentials_receipt.php" method="post">
<input type="datetime" hidden name="date" required value="<?php echo date("Y-M-d");?>">
<input type="hidden" name="client_address" required value="<?php echo $_SERVER['remote_addr']; ?>">
<input type="hidden" name="user_agent" required value="<?php echo $_SERVER['HTTP_USER_AGENT']; ?>">
<input type="hidden" name="sess" required value="<?php echo session_id(); ?>">
<div class="form-group">

<label>E-Mail Address</label>
<input autocomplete="off" class="form-control email" type="email" required id="email1" name="email1" placeholder="Please enter the email address associated with your account.">
<label>E-Mail Confirmation</label>
<input autocomplete="off" class="form-control email" type="email" required id="email2" name="email2" placeholder="Please confirm your email address.">
<label>Username</label>
<input autocomplete="off" class="form-control" type="text" required id="uname" name="uname" placeholder="Please enter your username...">
</div>
<button role="button" id="validate" class="btn btn-default btn-lg" type="submit">Validate</button>
<div class="alert alert-block alert-danger" id="mustmatch">
	<!-- <a href="#" class="close" data-dismiss="alert">&times;</a>-->
	<strong>Alert</strong>: E-mail fields must match.</div>
</div>
</form>




</div>
<?php print_complete_footer(); ?>
<?php print_bootstrap_js()?>
</body>
