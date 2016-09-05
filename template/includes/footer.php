<?php
echo $language['misc']['language'];

foreach($languages as $language_name) {
	echo ' <a href="index.php?language=' . $language_name . '">' . $language_name . '</a> &nbsp;&nbsp; ';
}

?>

<br />

<a href="terms-of-service">Terms of Service</a> - <a href="privacy-policy">Privacy Policy</a> - <a href="disclaimer">Disclaimer</a> - <a href="contact">Contact</a>

<br />

<?php echo 'Copyright &copy; ' . date("Y") . ' ' . $settings->title . '. All rights reserved. Powered by <a href="http://codecanyon.net/user/GrohsFabian/portfolio">Grohs Fabian</a>'; ?>