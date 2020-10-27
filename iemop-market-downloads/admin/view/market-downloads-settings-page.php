<?php
	// echo json_encode($_FILES);
	// echo json_encode($contents);
?>
<div id="wpbody" role="main">
	<div id="wpbody-content">
		<div class="wrap">
			<h1><?php echo get_admin_page_title(); ?></h1><?php

			if(isset($_POST['submit'])){
				show_admin_notice($data);
			} ?>
			<form action="#" method="POST" enctype="multipart/form-data" id="market_graphs_settings_form">
				<table class="form-table">
					<tbody>
						<tr><th scope="row">
								<label for="archive_cap">Market Data Maximum archive capacity (MB)</label>
							</th>
							
							<td>
								<input type="text" id="archive-cap" name="archive_cap" class="regular-text num" maxlength="4" value="<?php echo (isset($data['archive_cap'])) ? $data['archive_cap'] : 1024; ?>">
								<p class="description">Default: 1024 MB (1024 MB = 1 GB)</p>
							</td>
						</tr>
						<tr><th scope="row">
								<label for="archive_cap_mr">Market Reports Maximum archive capacity (MB)</label>
							</th>
							
							<td>
								<input type="text" id="archive-cap-mr" name="archive_cap_mr" class="regular-text num" maxlength="4" value="<?php echo (isset($data['archive_cap_mr'])) ? $data['archive_cap_mr'] : 1024; ?>">
								<p class="description">Default: 1024 MB (1024 MB = 1 GB)</p>
							</td>
						</tr>
						<tr><th scope="row">
								<label for="archive_cap_mr">Market Reports Maximum upload file size (MB)</label>
							</th>
							
							<td>
								<input type="text" id="mr-filesize" name="mr_filesize" class="regular-text num" maxlength="4" value="<?php echo (isset($data['mr_filesize'])) ? $data['mr_filesize'] : 5; ?>">
								<p class="description">Default: 5 MB (1024 MB = 1 GB)</p>
							</td>
						</tr>
						<tr><th scope="row">
								<label for="archive_cap_mr">Market Reports Maximum number of files per upload</label>
							</th>
							
							<td>
								<input type="text" id="mr-max-files" name="mr_max_files" class="regular-text num" maxlength="3" value="<?php echo (isset($data['mr_max_files'])) ? $data['mr_max_files'] : 50; ?>">
								<p class="description">Default: 50 Files per upload. Max: 100 Files</p>
							</td>
						</tr>
						<tr><th scope="row">
								<label for="archive_cap">Market Reports display at homepage</label>
							</th>
							<td><?php
								// var_dump($selected_position);
								?>
								<p><input type="radio" name="position" value="asc" <?php echo ($data['reports_position'] == 'asc') ? 'checked' : ''; ?>>Position reports by published ASC</p>
								<p><input type="radio" name="position" value="desc" <?php echo ($data['reports_position'] == 'desc') ? 'checked' : ''; ?>>Position reports by published DESC</p>
								<p><input type="radio" name="position" value="manual" <?php echo ($data['reports_position'] == 'manual') ? 'checked' : ''; ?>>Position reports manually</p>
							</td>
						</tr>
					</tbody>
				</table>
				<div id="manual-table">
					<div id="dialog-remove-file" title="Remove File" style="display: none;"><br>
						<div>Are you sure you want to remove this from top page?</div>
					</div>
					<h2>Market Report Files <span class="autosave">Saved!</span></h2>
					<table class="form-table table-content report-files-table">
						<thead>
							<tr>
								<td>Maximum of <span class="max_num">4</span> files only</td>
							</tr>
						</thead>
						<tbody class="<?php echo ($total_reports <= 1) ? '' : 'sortable'; ?>"><?php
							if(!empty($top_reports)){
								foreach ($top_reports as $key => $id) {  
									$report = $this->MD_Model->get_file($id); ?>
									<tr id="<?php echo $id; ?>"><th><?php echo $report['doc_title']; ?><br />
											<a href="#" class="remove_top_page" data-id="<?php echo $id; ?>">Remove</a>
										</th>
									</tr><?php
								}
							}else{ ?>
								<tr>
									<th>No files added</th>
								</tr>
								<?php
							} ?>
						</tbody>
					</table>
				</div>
				
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
			</form>
		</div>
	</div>
</div>