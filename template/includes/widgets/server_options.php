<div class="btn-group btn-group-vertical" id="server_options">

	<div class="btn-group">
		<a class="btn btn-default" data-toggle="modal" data-target="#vote">
			<span class="glyphicon glyphicon-stats"></span> <?php echo $language['server']['sidebar_vote']; ?>
		</a>
	</div>

	<div class="btn-group">
		<a class="btn btn-default" data-toggle="modal" data-target="#comment">
			<span class="glyphicon glyphicon-plus"></span> <?php echo $language['server']['sidebar_add_comment']; ?>
		</a>
	</div>

	<?php if(User::logged_in() && $account_user_id == $server->data->user_id) { ?>
	<div class="btn-group">
		<a class="btn btn-default" data-toggle="modal" data-target="#blog">
			<span class="glyphicon glyphicon-pencil"></span> <?php echo $language['server']['sidebar_add_blog_post']; ?>
		</a>
	</div>
	<?php } ?>

	<div class="btn-group">
		<a class="btn btn-default" onclick="report(<?php echo $server->data->server_id; ?>, 2);">
			<span class="glyphicon glyphicon-exclamation-sign"></span> <?php echo $language['misc']['report']; ?>
		</a>
	</div>

	<?php if(User::logged_in() && $account_user_id == $server->data->user_id) { ?>
	<div class="btn-group">
		<a href="edit-server/<?php echo $server->data->server_id; ?>" class="btn btn-default">
			<span class="glyphicon glyphicon-pencil"></span> <?php echo $language['forms']['server_edit']; ?>
		</a>
	</div>
	<?php } ?>

	<?php if(User::logged_in() && User::is_admin($account_user_id)) { ?>
	<div class="btn-group">
		<a href="admin/edit-server/<?php echo $server->data->server_id; ?>" class="btn btn-default">
			<span class="glyphicon glyphicon-pencil"></span> <?php echo $language['forms']['server_admin_edit']; ?>
		</a>
	</div>
	<?php } ?>

</div>

