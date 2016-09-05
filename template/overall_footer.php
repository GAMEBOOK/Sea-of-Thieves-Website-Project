						<?php include 'includes/close_main_column.php'; ?>

						<!-- START Sidebar -->
						<?php if(!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] == 'list')) { ?>
							<div class="col-md-2">
								<?php include 'includes/sidebar.php'; ?>
							</div>
						<?php } ?>
						<!-- END Sidebar -->

					</div><!-- END ROW -->

					<?php include 'includes/widgets/bottom_ads.php'; ?>
				</div><!-- END Main panel body -->
			</div><!-- END Main panel -->

			<!-- FOOTER -->
			<div class="panel panel-default panel-main panel-low">
				<div class="panel-body">
					<?php include 'includes/footer.php'; ?>
				</div>
			</div>

		</div><!-- END Container -->

	</body>
</html>