<?php

/* Check if category exists and the GET variable is not empty*/
if(empty($_GET['url']) || !$category_exists) {
	$_SESSION['error'][] = $language['errors']['category_not_found'];
	User::get_back();
}

initiate_html_columns();
?>

<div class="panel panel-default panel-fixed" <?php if(!empty($category->image)) echo 'style="background: url(\'user_data/category_covers/' . $category->image . '\');"'; ?>>
	<div class="panel-body" style="position:relative;">
		<h1 class="shadow inline">
			<?php echo $category->name; ?>
		</h1>
		<?php 
		if(User::logged_in() && User::is_admin($account_user_id)) { 
			echo '<span class="pull-right">';
				category_admin_buttons($category->category_id,  $token->hash);
			echo '</span>';
		} 
		?>
		<p class="shadow"><?php echo $category->description; ?></p>
	</div>
</div>

<?php
/* Initiate the servers list class */
$servers = new Servers($category->category_id);

/* Set a custom no servers message */
$servers->no_servers = $language['messages']['no_category_servers'];

/* Make it so it will display only the active and the servers which are not private */
$servers->additional_where("AND `private` = '0' AND `active` = '1'");

/* Try and display the server list */
$servers->display();

/* Display any notification if there are any ( no servers ) */
display_notifications();

/* Display the pagination if there are servers */
$servers->display_pagination('category/' . $category->url);
?>
