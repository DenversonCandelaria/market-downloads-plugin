<section id="report-download-section">
	<h1 class="text-center header-36">
	reports and downloads
	</h1>
	<div class="line center-line"></div>
	<div class="container">
		<div class="row report-download-section-row">
			<?php 
			if($top_reports){
				// echo json_encode($top_reports);
				foreach ($top_reports as $key => $report) { 
					$post_id = $report['mr_id'];
					$slug = get_post_field( 'post_name', $post_id );
					$dl_path = wp_upload_dir()['basedir'].'/downloads/reports/'.$slug.'/'.$report['filename'];
					$ext = pathinfo($report['filename'], PATHINFO_EXTENSION); 
					$icon = rtrim($ext, 'x').'-icon'; 
					$icon_white = $icon.'-white'; ?>
			 		<div class="col-lg-3 col-md-6 col-12">
						<div class="report-download-section-item">
							<div class="report-download-section-icon">
								<img class="pdf-icon-blue" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/<?php echo $icon; ?>.png">
								<img class="pdf-icon-white" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/<?php echo $icon_white; ?>.png">
							</div>
							<div class="report-download-section-title">
								<?php echo $report['doc_title']; ?>
							</div>
							<div class="short-line"></div>
							<div class="report-download-section-description">
								<?php echo $report['description']; ?>  
							</div>
							<a href="?md_file=<?php echo base64_encode($dl_path); ?>"><button type="button" class="btn download-btn orange-btn"> <div style="margin-right:5px" class="btn-text">Download</div><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/download-btn-icon.png"></button></a>
						</div>
					</div><?php
			 	} ?>
			 	<?php
			}else{ ?>
				<div class="empty-notice" style="width: 100%">No Reports and Downloads available</div>
				<?php
			}
			?>
			
		</div><?php
		if($top_reports && $mr_url){ ?>
			<a class="text-center load-more-link" href="<?php echo $mr_url; ?>">
				<span class="knowmore-link">Know More</span> <i class="know-more-icon fa fa-angle-right"></i>
			</a><?php
		} ?>
		
	</div>
</section>	