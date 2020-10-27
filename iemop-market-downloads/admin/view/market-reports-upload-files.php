<?php
	// echo json_encode($_FILES);
	// echo json_encode($contents);
?>
<div id="wpbody" role="main">
	<div id="wpbody-content">
		<div class="wrap">
			<h1><?php echo get_the_title($_GET['post']); ?></h1><?php
			if(isset($_POST['submit'])){
				show_admin_notice($contents);
			} ?>
			<form action="#" method="POST" enctype="multipart/form-data" id="upload_files_form">
				<h2>Upload Files</h2>
				<div id="dropzone">
					<div class="dz-message">
				    	Drop files here or click to upload.
			    		<div id="max-upload-size">
			    			<p>Maximum upload file size: <?php echo $max_filesize; ?> MB.</p>
			    			<p>Maximum files to upload: <?php echo $max_file; ?> files.</p>
			    			<p>Accepted Files: doc, docx, xls, xlsx, ppt, pptx, pdf, jpg, png, csv, gif, zip</p>
			    		</div>
				  	</div>
				  

					
				</div>
				<div id="preview">
					<div class="dz-preview dz-file-preview">
					  <div class="dz-details">
					    <div class="dz-filename field-row">
					    	<label for="doc_title">File</label>	<span data-dz-name></span>
					    </div>
					    <div class="doc_title field-row">
					    	<label for="doc_title">Document Title <span class="asterisk">*</span></label>
					    	<input type="text" name="doc_title[]" class="regular-text" maxlength="255" placeholder="Type the name for this document">
					    </div>
					    <div class="description field-row">
					    	<label for="description">Description <span class="asterisk">*</span></label>
					    	<input type="text" name="description[]" class="regular-text" maxlength="255" placeholder="Type info about this file">
					    </div>
					    <div class="published_date field-row">
					    	<label for="published_date">Publication Date <span class="asterisk">*</span></label>
					    	<input type="text" name="published_date[]" class="published-date regular-text" readonly="" placeholder="Select published date" value="<?php echo current_time("Y-m-d H:i:s"); ?>">
					    </div>
					    <div data-dz-remove class="btn btn-danger delete" title="Remove File">
				        	<i class="fa fa-trash-o fa-2x" aria-hidden="true"></i>
				      	</div>
					  </div>
					  <div class="clear"></div>
					  

					  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
					  <div class="dz-error-message"><span data-dz-errormessage></span></div>
					</div>
				</div>
				<div id="total-progress">
					<div id="total-progress-bar">
					</div>
					<div id="percentage">0%</div>
				</div>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="Upload Files">
					<span id="added-files-cont"><span id="added-files">0</span> / <?php echo $max_file; ?> files added</span>
				</p>
			</form>
		</div>
	</div>
</div>