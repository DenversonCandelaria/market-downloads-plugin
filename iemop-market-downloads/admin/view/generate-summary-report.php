<div id="wpbody" role="main">
	<div id="wpbody-content">
		<div class="wrap">
			<h1><?php echo get_admin_page_title(); ?></h1><?php
			if(isset($_POST['submit'])){
				show_admin_notice($contents);
			} ?>
			<div class="acf-notice -error acf-error-message -dismiss custom-notice-summary-reports"><p></p><a href="#" class="acf-notice-dismiss acf-icon -cancel small"></a></div>
			<form action="#" method="POST" enctype="multipart/form-data" id="summary_report_form">
				<table class="form-table">
					<tbody>
						<tr><th scope="row">
								<label for="date_range">Date Range</label><span class="required"> *</span>
							</th>
							<td>
								<input type="text" id="daterange" name="date_range" class="regular-text" autocomplete="off" readonly="" placeholder="Select date range">
								<p class="description">Maximum of 31 days</p>
							</td>
						</tr>
						<tr><th scope="row">
								<label for="market_data_type">Market Data Type</label><span class="required"> *</span>
							</th>
							<td>

								<select name="market_data_type" id="market_data_type">
									<?php 
										if(count($categories)){
											foreach ($categories as $key => $category) { 
												$cat_id = $category->term_id;
						                        $args = array(
						                            'post_type'=>'market-data',
						                            'posts_per_page'=>-1,
						                            'tax_query' => array(
						                                array(
						                                    'taxonomy' => 'market_data_category',
						                                    'field' => 'term_id',
						                                    'terms' => $cat_id,
						                                )
						                            )
						                        );
						                        $md_pages = get_posts($args); 

						                        ?>
												<optgroup label="<?php echo $category->name; ?>">
													<?php foreach ($md_pages as $key => $md_page) { ?>
														<option value="<?php echo $md_page->ID; ?>"><?php echo $md_page->post_title; ?></option>
													<?php } ?>
												</optgroup>
												<?php
											}
										}else{ ?>
											<option value="">No market data available</option>
											<?php
										}
										
									?>		
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary generate_summary_report" value="Generate" <?php echo (!count($categories)) ? 'disabled' : ''; ?>></p>
			</form>
		</div>
	</div>
</div>