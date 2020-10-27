<div class="wrap">
	<h1><?php echo get_admin_page_title(); ?></h1>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content" class="logs-table-cont">
				<div class="meta-box-sortables">
					<form method="post">
						<?php
						$logs_table->prepare_items();
						$logs_table->display(); ?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>