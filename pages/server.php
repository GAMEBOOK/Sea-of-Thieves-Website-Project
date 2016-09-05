<?php
include 'core/functions/recaptchalib.php';
include 'template/includes/modals/comment.php';
include 'template/includes/modals/blog.php';
include 'template/includes/modals/report.php';
include 'template/includes/modals/vote.php';

/* Check if server exists and the GET variables are not empty */
if(empty($_GET['server_id']) || !$server->exists) {
	$_SESSION['error'][] = $language['errors']['server_not_found'];
} else {

	/* Check if server is disabled */
	if(!$server->data->active) {
		$_SESSION['error'][] = $language['errors']['server_not_active'];
	}

	if(
		($server->data->private && !User::logged_in()) || 
		($server->data->private && User::logged_in() && $account_user_id != $server->data->user_id)
	) {
		/* Set error message and redirect */
		$_SESSION['error'][] = $language['errors']['server_private'];
	}

}

if(!empty($_SESSION['error'])) User::get_back();

/* If its private but the owner is viewing it, display a notice message */
if($server->data->private) echo output_notice($language['server']['private']);

/* Check if we should add another hit to the server or not */
$result = $database->query("SELECT `id` FROM `points` WHERE `type` = 0 AND `server_id` = {$server->data->server_id} AND `ip` = '{$_SERVER['REMOTE_ADDR']}' AND `timestamp` > UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)");
if(!$result->num_rows) {
	$database->query("UPDATE `servers` SET `hits` = `hits` + 1 WHERE `server_id` = {$server->data->server_id}");
	$database->query("INSERT INTO `points` (`type`, `server_id`, `ip`, `timestamp`) VALUES (0, {$server->data->server_id}, '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP())");
}

initiate_html_columns();

?>

<div id="response" style="display:none;"><?php output_success($language['messages']['success']); ?></div>

<h2 class="server-title no-margin">
	<a href="http://<?php echo $server->data->address ?>" target="_blank"><?php echo $server->data->name; ?></a>
</h2>

<br />
<div class="row">
	<div class="col-lg-10 col-md-10 col-xs-12">

		<div class="panel panel-default">
			<div class="panel-body">

				<table class="table">
					<tbody>
						<tr>
							<td><span class="glyphicon glyphicon-random"></span> <strong><?php echo $language['server']['general_address']; ?></strong></td>
							<td><a href="http://<?php echo $server->data->address ?>" target="_blank"><?php echo $server->data->address ?></a></td>
						</tr>

						<tr>
							<td><span class="glyphicon glyphicon-cog"></span> <strong><?php echo $language['server']['general_category']; ?></strong></td>
							<td><?php echo '<a href="category/' . $server->category->url . '">' . $server->category->name . '</a>'; ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-tower"></span> <strong><?php echo $language['server']['general_owner']; ?></strong></td>
							<td><?php echo User::get_profile_link($server->data->user_id); ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-arrow-up"></span> <strong><?php echo $language['server']['general_votes']; ?></strong></td>
							<td id="votes_value"><?php echo $server->data->votes; ?></td>
						</tr>	
						<tr>
							<td><span class="glyphicon glyphicon-star"></span> <strong><?php echo $language['server']['general_hits']; ?></strong></td>
							<td><?php echo $server->data->hits; ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-upload"></span> <strong><?php echo $language['server']['general_hits_month']; ?></strong></td>
							<td><?php echo $server->hits; ?></td>
						</tr>
						<tr>
							<td><span class="glyphicon glyphicon-globe"></span> <strong><?php echo $language['server']['general_country']; ?></strong></td>
							<td><?php echo country_check(2, $server->data->country_code); ?> <img src="template/images/locations/<?php echo $server->data->country_code; ?>.png" alt="<?php echo $server->data->country_code; ?>" /></td>
						</tr>
						<?php if(!empty($server->data->website)) { ?>
						<tr>
							<td><span class="glyphicon glyphicon-link"></span> <strong><?php echo $language['forms']['server_website']; ?></strong></td>
							<td><a href="<?php echo $server->data->website; ?>"><?php echo $server->data->website; ?></a></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>

			</div>
		</div>

	</div>

	<div class="col-lg-2 col-md-2 col-xs-12">
		<?php include 'template/includes/widgets/server_options.php'; ?>
	</div>
</div>

<!-- Description -->
<?php if(!empty($server->data->description)) { ?>
<div class="panel panel-default">
	<div class="panel-body">
		<h3>
			<?php echo $language['server']['description']; ?>
		</h3>

		<?php echo bbcode($server->data->description); ?>

	</div>
</div>
<?php } ?>

<!-- Video -->
<?php if(!empty($server->data->youtube_id)) { ?>

<div class="panel panel-default">
	<div class="panel-body">
		<h3>
			<?php echo $language['server']['video']; ?>
		</h3>

		<div class="video-container">
			<?php echo youtube_convert($server->data->youtube_id); ?>
		</div>
	</div>
</div>
<?php } ?>

<!-- Statistics -->
<div class="panel panel-default">
	<div class="panel-body">
		<?php
		$result = $database->query("
			SELECT
				FROM_UNIXTIME(`points`.`timestamp`, '%Y-%m-%d') AS `date`,
				(SELECT COUNT(`points`.`id`) FROM `points` WHERE `type` = 0 AND `server_id` = {$server->data->server_id} AND  FROM_UNIXTIME(`points`.`timestamp`, '%Y-%m-%d') = `date`) AS `hits_count`,
				(SELECT COUNT(`points`.`id`) FROM `points` WHERE `type` = 1 AND `server_id` = {$server->data->server_id} AND FROM_UNIXTIME(`points`.`timestamp`, '%Y-%m-%d') = `date`) AS `votes_count`
			FROM `points`
			WHERE `points`.`timestamp` > UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY) AND `server_id` = {$server->data->server_id}
			GROUP BY `date`
			ORDER BY `date`
			");
		?>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("visualization", "1", {packages:["corechart"]});
			google.setOnLoadCallback(drawChart);
			function drawChart() {
				var data = google.visualization.arrayToDataTable([
					['Date', 'Hits', 'Votes'],
					<?php
					while($data = $result->fetch_object())
					echo "['" . $data->date . "', " . $data->hits_count . ", " . $data->votes_count . "],";
					?>
				]);

				var options = {
					title: <?php echo '\'' . $language['server']['tab_statistics'] . '\''; ?>
				};

				var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
				chart.draw(data, options);
			}

			$(window).resize(function(){
				drawChart();
			});

			$('[href=#statistics]').on('shown.bs.tab', function() {
				drawChart();
			});
		</script>

		<div id="chart_div" style="width: 100%; height: 400px;"></div>
	</div>
</div>

<div class="row">
	<div class="col-lg-6 col-md-6 col-xs-12">

		<!-- Comments -->
		<div class="panel panel-default">
			<div class="panel-body">
				<h3>
					<?php echo $language['server']['comments']; ?>
				</h3>

				<div id="comments"></div>

			</div>
		</div>

	</div>

	<div class="col-lg-6 col-md-6 col-xs-12">

		<!-- Blog Posts -->
		<div class="panel panel-default">
			<div class="panel-body">
				<h3>
					<?php echo $language['server']['tab_blog']; ?>
				</h3>

				<div id="blog_posts"></div>

			</div>
		</div>

	</div>
</div>



<!-- Recaptcha base -->
<div id="recaptcha_base">
	<div id="recaptcha" style="display:none;"><?php echo recaptcha_get_html($settings->public_key); ?></div>
</div>

<script>
$(document).ready(function() {

	/* Initialize the success message variable */
	var SuccessMessage = $('#response').html();

	/* Load the first comments results */
	showMore(0, 'processing/comments_show_more.php', '#comments', '#showMoreComments');

	/* Load the first blog results */
	showMore(0, 'processing/blog_show_more.php', '#blog_posts', '#showMoreBlogPosts');
	
	/* Delete system */
	$('#comments, #blog_posts').on('click', '.delete', function() {
		/* selector = div to be removed */
		var answer = confirm("<?php echo $language['messages']['confirm_delete']; ?>");
		
		if(answer) {
			$('html, body').animate({scrollTop:0},'slow');

			var $div = $(this).closest('.media');
			var reported_id = $(this).attr('data-id');
			var type = $(this).attr('data-type');

			/* Post and get response */
			$.post("processing/process_comments.php", "delete=true&reported_id="+reported_id+"&type="+type, function(data) {

				if(data == "success") {
					$("#response").html(SuccessMessage).fadeIn('slow');
					$div.fadeOut('slow');
				} else {
					$("#response").html(data).fadeIn('slow');
				}
				setTimeout(function() {
					$("#response").fadeOut('slow');
				}, 5000);
			});
		}
	});


});
</script>