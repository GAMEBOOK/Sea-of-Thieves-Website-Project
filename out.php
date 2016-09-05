<?php
include 'core/init.php';

$server = new Server($_GET['server_id']);

if(empty($_GET['server_id']) || !$server->exists) {
	$_SESSION['error'][] = $language['errors']['server_not_found'];
} else {

	if(!$server->data->active) {
		$_SESSION['error'][] = $language['errors']['server_not_active'];
	}

	if(
		($server->data->private && !User::logged_in()) || 
		($server->data->private && User::logged_in() && $account_user_id != $server->data->user_id)
	) {
		$_SESSION['error'][] = $language['errors']['server_private'];
	}

}

if(!empty($_SESSION['error'])) redirect();

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?php echo $settings->title . ' - ' . $server->data->name; ?></title>

	<link href="../template/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="../template/css/out.css" rel="stylesheet" media="screen">
	<link href="../template/css/font-awesome.min.css" rel="stylesheet" media="screen">
	<link href="../template/images/favicon.ico" rel="shortcut icon" />

</head>
<body>

	<div id="header">

		<div id="title" class="pull-left">
			<span><?php echo $settings->title; ?></span>

			<p>
				<a href="<?php echo $settings->url . 'server/' . $server->data->server_id; ?>"><span class="label label-success">Get back</span></a>&nbsp;
				<a href="<?php echo 'http://' . $server->data->address; ?>"><span class="label label-default">Close this bar</span></a>
			</p>
		</div>

		<div class="pull-right">
			<?php if(!empty($settings->out_ads)) { ?>
				<div class="center">
					<?php echo $settings->out_ads; ?>
				</div>
			<?php } ?>
		</div>

	</div>

	<div id="content">
		<iframe width="100%" height="100%" frameborder="0" src="<?php echo 'http://' . $server->data->address; ?>" />
	</div>

</body>
</html>