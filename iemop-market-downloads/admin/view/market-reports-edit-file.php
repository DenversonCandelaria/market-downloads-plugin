<?php
	// echo json_encode($_FILES);
	// echo json_encode($contents);
?>
<div id="wpbody" role="main">
	<div id="wpbody-content">
		<div class="wrap">
			<h1><?php echo get_the_title($_GET['post']); ?></h1>
			<h2>Edit File</h2>
			<p><a href="edit.php?post_type=market-reports&page=browse-files&post=<?php echo $_GET['post']; ?>&paged=<?php echo isset($_GET['paged']) ? $_GET['paged'] : 1; ?>"><i class="fa fa-angle-left" aria-hidden="true"></i>Back</a></p><?php
			if(isset($_POST['submit'])){
				show_admin_notice($data);
			} ?>
			<form action="#" method="POST" enctype="multipart/form-data" id="edit_file_form">
				<table class="form-table">
					<tbody>
						<tr><th scope="row">
								<label for="doc_title">Document Title</label><span class="asterisk"> *</span>
							</th>
							<td>
								<input type="text" name="doc_title" value="<?php echo isset($data) ? $data['doc_title'] : ''; ?>" class="regular-text" maxlength="255" />
								<p class="description">Maximum of 255 characters</p>
							</td>
						</tr>
						<tr><th scope="row">
								<label for="doc_title">Description</label><span class="asterisk"> *</span>
							</th>
							<td>
								<input type="text" name="description" value="<?php echo isset($data) ? $data['description'] : ''; ?>" class="regular-text" maxlength="255"/>
								<p class="description">Maximum of 255 characters</p>
							</td>
						</tr>	
						<tr><th scope="row">
								<label for="doc_title">Published Date</label><span class="asterisk"> *</span>
							</th>
							<td>
								<input type="hidden" name="filename" value="<?php echo isset($data)? $data['filename'] : ''; ?>">
								<input type="text" name="published_date" value="<?php echo isset($data) ? $data['published_date'] : ''; ?>" class="regular-text published-date" maxlength="255" readonly />
							</td>
						</tr>
						<tr><th scope="row">
								<label for="file">Upload File</label><span class="asterisk"> *</span>
							</th>
							<td>
								<p><a href="?post_type=market-reports&md_file=<?php echo $dl_path; ?>"><?php echo isset($data) ? $data['filename'] : ''; ?></a></p>
								<input type="file" name="file" accept=".doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.gif,.jpg,.zip,.pdf,.png" /><br>
								<p class="description">Maximum upload file size: <?php echo $this->get_max_filesize(); ?> MB.<br>Accepted Files: doc, docx, xls, xlsx, ppt, pptx, pdf, jpg, png, csv, gif, zip</p>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
			</form>
		</div>
	</div>
</div>