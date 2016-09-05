<?php
include '../core/init.php';
include '../core/functions/recaptchalib.php';

/* Define the captcha variable */
$captcha = recaptcha_check_answer ($settings->private_key, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);

/* Check for any errors */
$result = $database->query("SELECT `id` FROM `points` WHERE `type` = 1 AND `server_id` = {$_SESSION['server_id']} AND `ip` = '{$_SERVER['REMOTE_ADDR']}' AND `timestamp` > UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)");

if($result->num_rows) {
	$errors[] = $language['errors']['already_voted'];
}
if(!$captcha->is_valid) {
	$errors[] = $language['errors']['captcha_not_valid'];
}

if(empty($errors)) {

	/* Update the votes in the database */
	$database->query("INSERT INTO `points` (`type`, `server_id`, `ip`, `timestamp`) VALUES (1, {$_SESSION['server_id']}, '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP())");
	$database->query("UPDATE `servers` SET `votes` = `votes` + 1 WHERE `server_id` = {$_SESSION['server_id']}");

	echo "success";
} else echo output_errors($errors);
?>