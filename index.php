<?php
require_once('config.inc.php');
require_once('secure.inc.php');
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<?php require('head.php') ?>
	<title>QuickAuth | A user system which supports OAuth</title>
</head>
<body>
<div class="wrapper">
	<?php require('header.php'); ?>
	<?php require('modals.php'); ?>
	<div class="container">
		<div class="page-header text-center">		
			<h2>QuickAuth for shortlink</h2>	
			<p><a class="btn btn-primary btn-lg" href="<?= BASE_URL ?>/register?getstarted">Get started</a></p>		
		</div>
	</div> <!-- /container -->
	<!--This div exists to avoid footer from covering main body-->
	<div class="push"></div>
</div>
<?php require('footer.php'); ?>
</body>
</html>