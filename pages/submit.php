<?php
User::check_permission(0);
include 'core/functions/recaptchalib.php';

$address =  $name = $country_code = $description = $youtube_link = null;

if(!empty($_POST)) {

	/* Define some variables */
	$address = parse_url($_POST['address']);
	@$new_address = $address['host'] . $address['path'];
	$date = new DateTime();
	$date = $date->format('Y-m-d H:i:s');
	$active = $status = '1';
	$private = ($settings->new_servers_visibility) ? '0' : '1';
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$image = (empty($_FILES['image']['name']) == false) ? true : false;
	$country_code = (country_check(0, $_POST['country_code'])) ? $_POST['country_code'] : 'US';
	$youtube_link = filter_var($_POST['youtube_id'], FILTER_SANITIZE_STRING);
	$youtube_id = youtube_url_to_id($youtube_link);
	$description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
	
	$captcha = recaptcha_check_answer ($settings->private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	$allowed_extensions = array('jpg', 'jpeg', 'gif');
	$required_fields = array('address', 'name', 'category_id');

	/* Get category data */
	$category = new StdClass;

	$stmt = $database->prepare("SELECT `category_id`, `name`, `url` FROM `categories` WHERE `category_id` = ?");
	$stmt->bind_param('s', $_POST['category_id']);
	$stmt->execute();
	bind_object($stmt, $category);
	$stmt->fetch();
	$stmt->close(); 
	
	/* Determine if category exists */
	if($category !== NULL) {
		$category->exists = true;
	} else {
		$category = new StdClass;
		$category->exists = false;
	}
	if(!$category->exists) {
		$_SESSION['error'][] = $language['errors']['category_not_found'];
	}
	
	/* Check for the required fields */
	foreach($_POST as $key=>$value) {
		if(empty($value) && in_array($key, $required_fields) == true) {
			$_SESSION['error'][] = $language['errors']['marked_fields_empty'];
			break 1;
		}
	}

	/* Check for banner image errors */
	if($image == true) {
		$image_file_name		= $_FILES['image']['name'];
		$image_file_extension	= explode('.', $image_file_name);
		$image_file_extension	= strtolower(end($image_file_extension));
		$image_file_temp		= $_FILES['image']['tmp_name'];
		$image_file_size		= $_FILES['image']['size'];
		list($image_width, $image_height)	= getimagesize($image_file_temp);

		if(in_array($image_file_extension, $allowed_extensions) !== true) {
			$_SESSION['error'][] = $language['errors']['incorrect_file_type'];
		}
		if($image_file_size > $settings->cover_max_size) {
			$_SESSION['error'][] = sprintf($language['errors']['image_size'], formatBytes($settings->cover_max_size));
		}
	}

	/* More checks */
	if(!$captcha->is_valid) {
		$_SESSION['error'][] = $language['errors']['captcha_not_valid'];
	}
	if(strlen($name) > 64 || strlen($name) < 3) {
		$_SESSION['error'][] = $language['errors']['server_name_length'];
	}
	if(strlen($description) > 2560) {
		$_SESSION['error'][] = $language['errors']['description_too_long'];
	}
	if(User::x_exists('address', $new_address, 'servers')) {
		$_SESSION['error'][] = $language['errors']['address_already_exists'];
	}
	if(!filter_var($_POST['address'], FILTER_VALIDATE_URL)) {
		$_SESSION['error'][] = $language['errors']['address_incorrect'];
	}

	/* If there are no errors, add the server to the database */
	if(empty($_SESSION['error'])) {

		/* Banner process */
		if($image == true) {

			/* Generate new name for image */
			$image_new_name = md5(time().rand()) . '.' . $image_file_extension;

			/* Resize if needed & upload the image */
			if($image_width != '468' || $image_height != '60') {
				resize($image_file_temp, 'user_data/server_banners/' . $image_new_name, '468', '60');
			} else {
				move_uploaded_file($image_file_temp, 'user_data/server_banners/' . $image_new_name);	
			}

		}

		$image_name = ($image == true) ? $image_new_name : '';

		/* Add the server to the database as private */
		$stmt = $database->prepare("INSERT INTO `servers` (`user_id`, `category_id`, `address`, `private`, `active`, `date_added`, `image`, `name`, `country_code`, `youtube_id`, `description`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param('sssssssssss',  $account_user_id, $category->category_id, $new_address, $private, $active, $date, $image_name, $name, $country_code, $youtube_id, $description);
		$test = $stmt->execute();
		$stmt->close();

		/* Set the success message and redirect */
		$_SESSION['success'][] = $language['messages']['server_added'];
		redirect('my-list');
	}

display_notifications();

}


initiate_html_columns();

?>


<h3><?php echo $language['headers']['submit']; ?></h3>

<form action="" method="post" role="form" enctype="multipart/form-data">

	<div class="form-group">
		<label><?php echo $language['forms']['server_name']; ?></label>
		<input type="text" name="name" class="form-control" value="<?php echo $name; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_address']; ?> *</label>
		<input type="text" name="address" class="form-control" value="<?php echo $address; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_category']; ?> *</label>
		<select name="category_id" class="form-control">
			<?php 
			$result = $database->query("SELECT `category_id`, `name` FROM `categories` WHERE `parent_id` = '0'  ORDER BY `name` ASC");
			while($category = $result->fetch_object()) {
				echo '<option value="' . $category->category_id . '">' . $category->name . '</option>'; 

				$subcategory_result = $database->query("SELECT `category_id`, `name` FROM `categories` WHERE `parent_id` = {$category->category_id} ORDER BY `name` ASC");
				while($subcategory = $subcategory_result->fetch_object()) {
					echo '<option value="' . $subcategory->category_id . '">--' . $subcategory->name . '</option>'; 

				}
			}
			?>	
		</select>
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_banner']; ?></label><br />
		<p class="help-block"><?php echo $language['forms']['server_banner_help']; ?></p>
		<input type="file" name="image" class="form-control" />
	</div>

	<hr />


	<div class="form-group">
		<label><?php echo $language['forms']['server_country']; ?></label>
		<select name="country_code" class="form-control">
			<?php country_check(1, $country_code); ?>
		</select>
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_youtube_id']; ?></label>
		<p class="help-block"><?php echo $language['forms']['server_youtube_id_help']; ?></p>
		<input type="text" name="youtube_id" class="form-control" value="<?php echo $youtube_link; ?>" />
	</div>

	<div class="form-group">
		<label><?php echo $language['forms']['server_description']; ?></label>
		<p class="help-block"><?php echo $language['forms']['server_description_help']; ?></p>
		<textarea name="description" class="form-control" rows="6"><?php echo $description; ?></textarea>
	</div>

	<div class="form-group">
		  <?php echo recaptcha_get_html($settings->public_key); ?>
	</div>

	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-default col-lg-4"><?php echo $language['forms']['submit']; ?></button><br /><br />
	</div>

</form>