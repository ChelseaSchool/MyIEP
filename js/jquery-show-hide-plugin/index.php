<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Papermashup.com | Show Hide Plugin</title>
<link href="../style.css" rel="stylesheet" type="text/css" />
<style>

body{
font-family:Verdana, Geneva, sans-serif;
font-size:14px;}

#slidingDiv, #slidingDiv_2{
	height:300px;
	background-color: #99CCFF;
	padding:20px;
	margin-top:10px;
	border-bottom:5px solid #3399FF;
	display:none;
}



</style>


</head>

<body>


<?php include '../includes/header.php';
 $link = '| <a href="http://papermashup.com/jquery-show-hide-plugin/">Back To Tutorial</a>';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery-2.1.0.min.js" type="text/javascript"></script>
<script src="showHide.js" type="text/javascript"></script>
<script type="text/javascript">

$(document).ready(function(){


   $('.show_hide').showHide({			 
		speed: 1000,  // speed you want the toggle to happen	
		easing: '',  // the animation effect you want. Remove this line if you dont want an effect and if you haven't included jQuery UI
		changeText: 1, // if you dont want the button text to change, set this to 0
		showText: 'View',// the button text to show when a div is closed
		hideText: 'Close' // the button text to show when a div is open
					 
	}); 


});

</script>

 <a href="#" class="show_hide" rel="#slidingDiv">View</a><br />
    <div id="slidingDiv">
        Fill this space with really interesting content.
    </div>
    
    
    
 <a href="#" class="show_hide" rel="#slidingDiv_2">View</a><br />
    <div id="slidingDiv_2">
        Fill this space with really interesting content.
    </div> 
    
    

</div>

<?php include '../includes/footer.php';?>

</body>
</html>