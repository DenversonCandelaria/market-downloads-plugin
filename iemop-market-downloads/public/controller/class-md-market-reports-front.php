<?php
/**
 * Display all Market Reports on the Front Side
 */
class MD_Market_Reports_Front {

	public $file = MD_PLUGIN_FILE;
	public $prefix = MD_PREFIX;
	public $public_url = MD_PLUGIN_PUBLIC_URL;


	public $files = array();
	public $min_date;

	/**
     * Load all initialized actions/filter, models and external files
     */
	public function load() {
		$this->includes();
		$this->inits();
	}

	/**
     * Include Market Downloads model and other class
     */
	public function includes() {
		global $wpdb;

		//Insert here all needed models
		require_once MD_PLUGIN_DIR.'models/class-md-market-downloads-model.php';
		$this->MD_Model = new MD_Market_Downloads_Model($wpdb);
	}

	/**
     * Initialize all action or filter hooks 
     */
	public function inits() {
		add_action('init',array($this, 'archive_market_reports'));
		add_action( 'wp_enqueue_scripts', array($this, 'include_assets'));
		add_action( 'wp_ajax_display_filtered_market_reports_files', array($this, 'display_filtered_market_reports_files'));
		add_action( 'wp_ajax_nopriv_display_filtered_market_reports_files', array($this, 'display_filtered_market_reports_files'));
		add_shortcode('market-reports-list', array($this, 'display_market_reports'));
		add_shortcode('market-reports-file-list', array($this, 'display_market_reports_single'));
		add_shortcode('market-reports-homepage', array($this, 'display_top_reports'));
	}

	/**
     * Include all js and css for Market reports page
     */
	public function include_assets(){
		global $post;

		if((get_post_type() == 'market-reports' || get_page_template_slug() == 'page-templates/market-reports-template.php') && !is_search()){
			wp_enqueue_style( 'daterangepicker-css', plugins_url( '/public/css/daterangepicker/daterangepicker.css', $this->file ));
			wp_enqueue_style( $this->prefix.'-market-downloads-public-css', plugins_url( '/public/css/market-downloads-public.css', $this->file ));

			wp_enqueue_script( 'moment-js', plugins_url('/public/js/daterangepicker-master/moment.min.js', $this->file), array( 'jquery' ), "", true);
			wp_enqueue_script( 'daterangepicker-js', plugins_url('/public/js/daterangepicker-master/daterangepicker.js', $this->file), array( 'jquery', 'moment-js' ), "", true);
			wp_enqueue_script( 'pagination-js', plugins_url('/public/js/pagination/pagination.js', $this->file), array( 'jquery' ), "", true);

			wp_enqueue_script( $this->prefix.'-market-downloads-public-js', plugins_url('/public/js/market-downloads-public.js', $this->file), array('jquery'), "", true);
			
			wp_localize_script( $this->prefix.'-market-downloads-public-js', 'php',
	            array(
	            	'ajax_url'=>admin_url('admin-ajax.php'),
	            	'theme_uri'=>get_stylesheet_directory_uri(),
	            	'post_id'=>$post->ID,
	            	'post_type'=>get_post_type()
	    		)
	        );
		}
		
	}

	/**
     * Display template for Market Reports via Shortcode
     * @return string|bool Html template for Market Reports
     */
	public function display_market_reports(){
		ob_start();
		$terms_arg = array(
			'taxonomy'=>'market_reports_category',
			'hide_empty'=>false,
			'parent'=>0
		);

		$categories = get_terms($terms_arg);
		
		include($this->public_url . '/view/market-reports.php');

		return ob_get_clean();
	}

	/**
     * Display template when viewing Market Reports files via Shortcode
     * @return string|bool Html template for Market Reports files
     */
	public function display_market_reports_single(){
		ob_start();
		global $post;
		$category = get_field('mr_category', get_the_ID());
		$cat_name = $category->name;
		// $this->scan_market_data_files();

		// 

		include($this->public_url . '/view/market-reports-single.php');
		
		return ob_get_clean();
	}

	/**
     * Get the child categories of the given $parent category
     * @param int $parent ID of the parent category
     * @return array Child categories
     */
	public function get_child_categories($parent){

		$terms_arg = array(
			'taxonomy'=>'market_reports_category',
			'hide_empty'=>false,
			'parent'=>$parent
		);

		$child_cats = get_terms($terms_arg);

		return $child_cats;
	}

	/**
     * Get the Market Reports page with the given category
     * @param int $cat_id Category ID
     * @return array Market Reports pages
     */
	public function get_mr_pages($cat_id){
		$args = array(
            'post_type'=>'market-reports',
            'posts_per_page'=>-1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'market_reports_category',
                    'field' => 'term_id',
                    'terms' => $cat_id,
                )
            )
        );

        $mr_pages = get_posts($args);

        return $mr_pages;
	}

	/**
     * Generate each market reports files on the Market Reports template. Called via AJAX
     */
	public function display_filtered_market_reports_files(){ //ajax
		$rslt = array();
		$filters = array();
		$post_id = $_POST['post_id'];

		$filters['sort'] = $_POST['sort'];
		if(isset($_POST['datefilter']) && $_POST['datefilter'] != ''){
			$filters['datefilter'] = $_POST['datefilter'];
		}

		$files = $this->MD_Model->get_files_front($post_id, $filters);

		
		foreach ($files as $key => $file) {
			$slug = get_post_field( 'post_name', $post_id );

        	$path = wp_upload_dir()['basedir'].'/downloads/reports/'.$slug.'/'; 
        	$file_path = $path.$file['filename'];

        	if(file_exists($file_path) && $file['filename'] != ''){
        		$id = $file['id'];
        		$file['published_date'] = date('d F Y H:i', strtotime($file['published_date']));
        		$dl_path = base64_encode($file_path);
        		$file['dl_path'] = $dl_path;
        		$rslt['data'][$id] = $file;
        	}
		}

		$rslt['count'] = count($rslt['data']);
		$rslt['source'] = array_keys($rslt['data']);

		echo json_encode($rslt);
		// wp_die(json_encode($files));
		wp_die();
	}

	/**
     * Display all Market Reports files that was added to top page
     * @return string|bool Html template for Market Reports files on top page
     */
	public function display_top_reports(){
		ob_start();

		$top_reports = $this->get_top_reports();

		$mr_page_id = get_page_id_by_template('page-templates/market-reports-template.php');
		$mr_url = (isset($mr_page_id[0])) ? get_permalink($mr_page_id[0]) : ''; 
		
		include($this->public_url . '/view/market-reports-homepage.php');

		return ob_get_clean();
	}

	/**
     * Get the market reports top page files
     * @return array Market reports files
     */
	public function get_top_reports(){
		$top_reports = array();

		$md_settings = get_option('iemop_market_downloads_settings', array());
		$top_reports_display = (isset($md_settings['reports_position'])) ? $md_settings['reports_position'] : 'asc';

		if ($top_reports_display == 'desc') {
			$sort = 'published_date DESC';
			$filters = array('sort'=>$sort, 'limit'=>4);
			$top_reports = $this->MD_Model->get_files_front("", $filters);
		}elseif ($top_reports_display == 'manual') {
			$top_report_ids = get_option('iemop_top_reports', array());
			$count = count($top_report_ids);
			$limit = 4 - $count;
			
			if($top_report_ids){
				foreach ($top_report_ids as $key => $id) {
					$file_info = $this->MD_Model->get_file($id);
					$top_reports[$id] = $file_info;
				}
			}

			if($limit > 0){
				$filters = array('limit'=>$limit, 'exclude'=>array_keys($top_reports));
				$latest = $this->MD_Model->get_files_front("", $filters);
				foreach ($latest as $key => $file) {
					$top_reports[$file['id']] = $file;
				}
			}
		}else{
			$sort = 'published_date ASC';
			$filters = array('sort'=>$sort, 'limit'=>4);
			$top_reports = $this->MD_Model->get_files_front("", $filters);
		}

		return $top_reports;
	}

	/**
     * Archive market reports files that already reached the retention days. Called via CRON
     */
	public function archive_market_reports(){
	 	if(isset($_GET['archive_market_reports'])){
	 		$rslt = array('status'=>false);

			$args = array(
	            'post_type'=>'market-reports',
	            'numberposts'=>-1,
	            'post_status'=>'publish'
	        );

	        $market_reports_posts = get_posts($args);
	        
        	$archive_files = array();
        	$log_files = array();

        	$downloads_dir = wp_upload_dir()['basedir'].'/downloads';
        	$downloads_reports_dir = $downloads_dir.'/reports';
        	$archived_dir = wp_upload_dir()['basedir'].'/archived';
            $archived_market_reports_folder = $archived_dir.'/reports';

            if(!file_exists($downloads_dir)){ mkdir($downloads_dir, 0777); }
            if(!file_exists($downloads_reports_dir)){ mkdir($downloads_reports_dir, 0777); }
            if(!file_exists($archived_dir)){ mkdir($archived_dir, 0777); }
            if(!file_exists($archived_market_reports_folder)){ mkdir($archived_market_reports_folder, 0777); }

	        foreach ($market_reports_posts as $key => $market_report) {
	        	// $this->files = array();

	        	$post_id = $market_report->ID;
	        	$post_title = $market_report->post_title;
	        	$data_retention = get_field('mr_data_retention', $post_id);
	        	if($data_retention == 0){ continue; } //skip if retention is 0

	        	$files = $this->MD_Model->get_files_front($post_id);
	        	
				$slug = get_post_field( 'post_name', $post_id );
		   			
	        	if($files){
	        		$time = current_time("H:i:s");
	        		$date = current_time("Y-m-d");
	        		$datetime = strtotime($date.' '.$time.' -'.$data_retention.' day');

	        		foreach ($files as $key => $file) {
	        			$published_date = strtotime($file['published_date']);
	        			//$rslt2 = $published_date < $datetime; 
		   				// echo date('m/d/Y H:i:s', $published_date) . " < ". date("m/d/Y H:i:s", $datetime) ." = ".$rslt2."<br>";
		   				if($published_date <= $datetime){ //file exceed data retention
							$folder_url = $slug.'/'.$file['filename'];
		   					$file_url = $downloads_reports_dir.'/'.$folder_url;
		   					$archive_files[$file_url] = array('id'=>$file['id'],
		   													  'filename'=>$file['filename'],
		   													  'folder'=>$folder_url,
		   													  'date_uploaded'=>$file['date_uploaded'],
		   													  'target_date'=>$file['published_date'],
		   													  'file_size'=>filesize($file_url),
		   													  'mr_page'=>$post_title,
		   													  'post_id'=>$post_id
		   													);
						}
		   			}
		   			
	            	
	        	}

	        }

   			if($archive_files){
   				$zip = new ZipArchive();
        		$zip_name = wp_upload_dir()['basedir'].'/'.current_time("m-d-Y").'.zip';

		 		$opened = $zip->open( $zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);
	        	if ($opened === TRUE){
		            // Add files to the zip file
		            foreach ($archive_files as $file_url => $file) {
		            	if(file_exists($file_url)){
		            		if (!$zip->addFile($file_url, $file['folder'])) {
						    	die("Could not add file $file_url");
							}
		            	}
		   			}

		            $zip->close();
		        }
		        
		        $archive_logs = array();

				$top_reports = get_option('iemop_top_reports', array());
	         	foreach ($archive_files as $file_url => $file) { //remove file and update db after zip close 
	         		$updated = $this->MD_Model->update_file($file['id'], array('status = 0'));
	            	if($updated){
	            		$mr_page = $file['mr_page'];
	            		$log_files[$mr_page][] = $file;
	            		remove_report_from_top_page($file['id'], $top_reports);
	            	}
	            	unlink($file_url); //delete file
	            }
	            $bytes = filesize($zip_name);
	            $zip_size = ($bytes / 1024) / 1024; //mb
	            $archive_folder_size = $this->get_archive_folder_size($archived_market_reports_folder);
	            $total_size = $zip_size + $archive_folder_size;

	            $md_settings = get_option('iemop_market_downloads_settings', array());
	            $archive_cap = (isset($md_settings['archive_cap_mr'])) ? $md_settings['archive_cap_mr'] : 1024;
	            
	            $rslt['zip_size'] = $zip_size;
				$rslt['total_size'] = $total_size;			            
	            
	            if($total_size > $archive_cap){ //if total size exceed the given maximum capacity
	            	$ctr = 1;
	            	$new_archive_folder_name = $archived_market_reports_folder.'-'.$ctr;
	            	while (file_exists($new_archive_folder_name)) {
	            		$ctr++;
            			$new_archive_folder_name = $archived_market_reports_folder.'-'.$ctr;
	            	}

	            	rename($archived_market_reports_folder, $new_archive_folder_name);
	            	if(!file_exists($archived_market_reports_folder)){ mkdir($archived_market_reports_folder, 0777); }
	            }

		        $rslt['status'] = rename($zip_name, $archived_market_reports_folder.'/'.basename($zip_name));
		        
		        foreach ($log_files as $mr_page => $log_file) {
		        	$insert_logs = array();
		        	$total_bytes = 0;
		        	foreach ($log_file as $key => $file) {
		        		$total_bytes += $file['file_size'];
		        	}

		        	$activity = ($rslt['status']) ? "Successfully archived file(s) on ".$mr_page : "Failed to archive file(s) on ".$mr_page;
        		 	$insert_logs[] = "(".$log_file[0]['post_id'].", '".$activity."', '".basename($zip_name)."', 'Market Reports', '".current_time("Y-m-d H:i:s")."', ".$total_bytes.")";

			        $log_id = $this->MD_Model->insert_log($insert_logs);
			        ($log_id) ? $this->MD_Model->insert_log_files($log_id, $log_file) : '';
		        }
		       

   			}

	        $rslt['count'] = count($archive_files); 
	        echo json_encode($rslt);
	        // echo "MARKET DATA ARCHIVE FUNCTION";
	    }
		
	}
	/**
     * Get total size of archive folder
     * @param string $dir Archive directory
     * @return int Folder size
     */
	private function get_archive_folder_size($dir){
	    $size = 0;

	    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
	        $size += is_file($each) ? filesize($each) : $this->get_archive_folder_size($each);
	    }
	    
	    // return in mb

	    return ($size) ? ($size/1024) / 1024 : $size;
	}

}