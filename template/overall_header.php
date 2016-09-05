<!DOCTYPE html>
<html>
	<?php include 'includes/head.php'; ?>
	<body>
		<!-- Start Container -->
		<div class="container">

			<?php include 'includes/menu.php'; ?>
			<?php if(!isset($_GET['page'])) include 'includes/home.php'; ?>
			
			<div class="panel panel-default panel-main">
				<div class="panel-body">
					<?php display_notifications(); ?>

					<?php include 'includes/widgets/top_ads.php'; ?>
