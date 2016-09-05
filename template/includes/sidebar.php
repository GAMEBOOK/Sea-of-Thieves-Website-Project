<?php 

if(!isset($_GET['page']) || (isset($_GET['page']) && (($_GET['page'] == 'category' && $category_exists) || $_GET['page'] == 'list'))) {
	include 'widgets/servers_filter.php';
	include 'widgets/categories.php';
}

if(!empty($settings->side_ads)) echo $settings->side_ads;

?>