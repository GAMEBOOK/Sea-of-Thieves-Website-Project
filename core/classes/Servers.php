<?php

class Servers {
	private $order_by;
	private $additional_join = null;
	private $where;

	public $pagination;
	public $server_results;
	public $affix;
	public $country_options;
	public $version_options;
	public $no_servers;

	public function __construct($category_id = false) {
		global $database;
		global $settings;
		global $language;

		/* Initiate the affix and start generating it */
		$this->affix = '';

		/* Order by system */
		$order_by_options = array('votes', 'hits', 'server_id');
		$order_by_column = (isset($_GET['order_by']) && in_array(strtolower($_GET['order_by']), $order_by_options)) ? strtolower($_GET['order_by']) : false;
		$this->order_by = 'ORDER BY `servers`.`highlight` DESC, ';
		$this->order_by .= ($order_by_column !== false) ? '`servers`.`' . $order_by_column . '` DESC' : '`servers`.`votes` DESC';
		$this->affix .= ($order_by_column !== false) ? '&order_by=' . $order_by_column : '';

		/* Filtering system */	
		$category_where = ($category_id !== false) ? 'AND `servers`.`category_id` = ' . $category_id : null;

		/* Process $_GET filters, build the affix */
		$highlight_options = array(0, 1);
		$highlight_value = (isset($_GET['filter_highlight']) && in_array($_GET['filter_highlight'], $highlight_options)) ? $_GET['filter_highlight'] : false;
		$highlight_where = ($highlight_value !== false) ? 'AND `servers`.`highlight` = ' . $highlight_value : null;
		$this->affix .= ($highlight_value !== false) ? '&filter_highlight=' . $highlight_value : '';

		/* Add the possible countries from the database into an array */
		$result = $database->query("SELECT DISTINCT `country_code` FROM `servers` WHERE 1=1 {$category_where}");
		$this->country_options = array();
		while($country_code = $result->fetch_object()) $this->country_options[] = $country_code->country_code;

		/* Processing again */
		$country_value = (isset($_GET['filter_country']) && in_array($_GET['filter_country'], $this->country_options)) ? $_GET['filter_country'] : false;
		$country_where = ($country_value !== false) ? 'AND `servers`.`country_code` = \'' . $country_value . '\'' : null;
		$this->affix .= ($country_value !== false) ? '&filter_country=' . $country_value : '';

		/* If affix isn't empty prepend the ? sign so it can be processed */
		$this->affix = (!empty($this->affix)) ? '?' . $this->affix : null;

		/* Create the maine $where variable */
		$this->where = "WHERE 1=1 {$category_where} {$highlight_where} {$country_where}";

		/* Generate pagination */
		$this->pagination = new Pagination($settings->servers_pagination, $this->where);

		/* Set the default no servers message */
		$this->no_servers = $language['messages']['no_servers'];

	}	

	public function additional_where($where) {
		global $settings;

		/* Remake the where with the additional condition */
		$this->where = $this->where . ' ' . $where;

		/* Remake the pagination */
		$this->pagination = new Pagination($settings->servers_pagination, $this->where);

	}

	public function additional_join($join) {
		global $settings; 
		
		/* This is mainly so we can gather the data based on the favorite servers */
		$this->additional_join = $join;

		/* Remake the pagination with the true condition so it counts the servers correctly */
		$this->pagination = new Pagination($settings->servers_pagination, $this->where, true);

	}

	public function remove_pagination() {

		/* Make the pagination null */
		$this->pagination->limit = null;

	}

	public function display() {
		global $database;
		global $language;
		global $account_user_id;
		global $settings;

		/* Quickly verify the remaining of highlighted days remaining */
		$database->query("UPDATE `servers` JOIN `payments` ON `servers`.`server_id` = `payments`.`server_id` SET `servers`.`highlight` = '0' WHERE `payments`.`date` + INTERVAL `payments`.`highlighted_days` DAY < CURDATE()");

		/* Retrieve servers information */
		$result = $database->query("SELECT * FROM `servers` {$this->additional_join} {$this->where} {$this->order_by} {$this->pagination->limit}");

		/* Check if there is any result */
		$this->server_results = $result->num_rows;
		if($this->server_results < 1) $_SESSION['info'][] = $this->no_servers;

		/* Display the servers */
		while($server = $result->fetch_object()) {

		/* Get category information for the servers */
		$category_result = $database->query("SELECT `name`, `url` FROM `categories` WHERE `category_id` = {$server->category_id}");
		$category = $category_result->fetch_object();

		/* Check if there is any image uploaded, if not, display default */
		$server->image = (empty($server->image)) ? 'default.jpg' : $server->image;
		
		?>

		<div class="panel panel-default">
			<div class="panel-body<?php if($server->highlight) echo ' vip-shadow'; ?>" style="padding: 10px;">

				<table class="server">
					<tr>
						<td>
							<h3 class="server-title no-margin">
								<a href="<?php echo $settings->url . 'out/' . $server->server_id; ?>" target="_blank"><?php echo $server->name; ?></a>
							</h3>
							<a href="<?php echo $settings->url . 'out/' . $server->server_id; ?>" target="_blank">
								<img src="user_data/server_banners/<?php echo $server->image; ?>" class="img-rounded hidden-xs hidden-sm banner"/>
							</a>
						</td>
						<td class="header">
							<div class="pull-right inline" >
								<?php if(User::x_to_y('server_id', 'user_id', $server->server_id, 'servers') == $account_user_id) echo '<a href="edit-server/' . $server->server_id . '"><span class="tag tooltipz">' . $language['forms']['server_edit'] . '</span></a>'; ?>
								
								<span data-toggle="tooltip" title="<?php echo $language['server']['general_country']; ?>" class="tag tooltipz"><span class="glyphicon glyphicon-globe"></span> <?php echo country_check(2, $server->country_code); ?></span>
								<a href="server/<?php echo $server->server_id; ?>"><span data-toggle="tooltip" title="<?php echo $language['server']['general_votes'] ?>" class="tag tooltipz"><span class="glyphicon glyphicon-arrow-up"></span> <?php echo $server->votes; ?></span></a>

							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php echo string_resize($server->description, 256); ?>
							<br />
							<?php 
							if(!$server->active) 
								echo '<span class="label label-default">' . $language['server']['not_active'] . '</span>'; 
							else {
								if($server->private)
								echo '<span class="label label-default">' . $language['server']['private'] . '</span>';
							}
							?>
						</td>
					</tr>
					
				</table>

			</div>
		</div>

		<?php }
	}

	public function display_pagination($current_page) {

		/* If there are results, display pagination */
		if($this->server_results > 0) {

			/* Establish the current page link */
			$this->pagination->set_current_page_link($current_page);

			/* Display */
			$this->pagination->display($this->affix);
		}
	}


	public function filters_display() {
		global $language;
		global $database;

		if($this->server_results > 0) { 

			/* Generating the link again for every filter so it doesn't mess the url */
			$order_by_link = (isset($_GET['order_by'])) ? preg_replace('/&order_by=[A-Za-z0-9_]+/', '', $this->affix) : $this->affix;
			$filter_highlight = (isset($_GET['filter_highlight'])) ? preg_replace('/&filter_highlight=[01]+/', '', $this->affix) : $this->affix;
			$filter_country = (isset($_GET['filter_country'])) ? preg_replace('/&filter_country=[A-Za-z]+/', '', $this->affix) : $this->affix;
		?>

			<h4><?php echo $language['misc']['filters']; ?></h4>


			<ul class="nav nav-pills nav-stacked">

				<?php if(!empty($this->affix)) { ?>
				<li class="dropdown active">
					<a href="<?php echo $this->pagination->link; ?>"><?php echo $language['misc']['reset_filters']; ?></a>
				</li>
				<?php } ?>

				<li class="dropdown active">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $language['misc']['order_by']; ?><b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo $this->pagination->link . $order_by_link . '&order_by=votes' ?>"><?php echo $language['misc']['order_by_votes']; ?></a></li>
						<li><a href="<?php echo $this->pagination->link . $order_by_link . '&order_by=hits' ?>"><?php echo $language['misc']['order_by_hits']; ?></a></li>
						<li><a href="<?php echo $this->pagination->link . $order_by_link . '&order_by=server_id' ?>"><?php echo $language['misc']['order_by_latest']; ?></a></li>
					</ul>
				</li>

				<li class="dropdown active">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $language['misc']['filter_highlight']; ?><b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo $this->pagination->link . $filter_highlight . '&filter_highlight=1' ?>"><?php echo $language['misc']['filter_yes']; ?></a></li>
						<li><a href="<?php echo $this->pagination->link . $filter_highlight . '&filter_highlight=0' ?>"><?php echo $language['misc']['filter_no']; ?></a></li>
					</ul>
				</li>


				<li class="dropdown active">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $language['misc']['filter_country']; ?><b class="caret"></b></a>
					<ul class="dropdown-menu">
						<?php foreach($this->country_options as $country) { echo '<li><a href="' . $this->pagination->link . $filter_country . '&filter_country=' . $country . '">' . country_check(2, $country) . '</a></li>'; } ?>
					</ul>
				</li>


			</ul><br />
		<?php
		}
	}

}
?>