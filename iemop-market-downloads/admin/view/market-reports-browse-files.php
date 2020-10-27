<div class="wrap">
	<h1><?php echo get_the_title($_GET['post']); ?></h1>
	<h2>Browse Files</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content" class="browse-files-cont">
				<div class="meta-box-sortables">
					<form method="post">
						<?php
						$files_table->search_box('search', 'search_id');
						$files_table->prepare_items();
						$files_table->display(); ?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>

	<div id="deleteDialog" title="Delete File?">
		<p class="dialogContent"></p>
	</div>
	<div id="maxTopPageDialog" title="Adding Failed">
		<p class="dialogContent"></p>
	</div>
</div>