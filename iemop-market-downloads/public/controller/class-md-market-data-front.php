<?php
/**
 * Display all Market Data on the Front Side
 */
class MD_Market_Data_Front {

	public $file = MD_PLUGIN_FILE;
	public $prefix = MD_PREFIX;
	public $public_url = MD_PLUGIN_PUBLIC_URL;

	public $MD_Model;
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
		//Insert here all needed models
		global $wpdb;

		require_once MD_PLUGIN_DIR.'models/class-md-market-downloads-model.php';
		$this->MD_Model = new MD_Market_Downloads_Model($wpdb);

	}

	/**
     * Initialize all action or filter hooks 
     */
	public function inits() {
		add_action('init',array($this, 'download_market_data_files'));
		add_action('init',array($this, 'archive_market_data'));
		add_action( 'wp_enqueue_scripts', array($this, 'include_assets'));
		add_action( 'wp_ajax_display_filtered_market_data_files', array($this, 'display_filtered_market_data_files'));
		add_action( 'wp_ajax_nopriv_display_filtered_market_data_files', array($this, 'display_filtered_market_data_files'));
		add_shortcode('market_data_list', array($this, 'display_market_data'));
		add_shortcode('market_data_file_list', array($this, 'display_market_data_single'));
	}

	/**
     * Include all js and css for Market data page
     */
	public function include_assets(){
		global $post;

		if((get_post_type() == 'market-data' || get_page_template_slug() == 'page-templates/market-data-template.php') && !is_search()){
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
	            	'min_date'=>$this->get_file_limit_days(),
	            	'post_type'=>get_post_type()
	    		)
	        );
		}
	}

	/**
     * Display template for Market Data via Shortcode
     * @return string|bool Html template for Market Data
     */
	public function display_market_data(){
		ob_start();
		$terms_arg = array(
			'taxonomy'=>'market_data_category',
			'hide_empty'=>false,
			''
		);

		$categories = get_terms($terms_arg);
		
		include($this->public_url . '/view/market-data.php');

		return ob_get_clean();
	}

	/**
     * Display template when viewing Market Data files via Shortcode
     * @return string|bool Html template for Market Data files
     */
	public function display_market_data_single(){
		ob_start();
		global $post;
		
		$this->scan_market_data_files();

		$files = $this->files;

		include($this->public_url . '/view/market-data-single.php');
		
		return ob_get_clean();
	}

	/**
     * Generate each market data files on the Market Data template. Called via AJAX
     */
	public function display_filtered_market_data_files(){ //ajax
		$post_id = $_POST['post_id'];

		$this->scan_market_data_files($post_id);
		// $page_count = count($this->files) / 24;

		if(isset($_POST['sort'])){
			$sort = $_POST['sort'];
			if($sort == 'asc'){
				asort($this->files); //Ascending by date
			}

			$files = $this->files;
		}

		if(isset($_POST['datefilter']) && $_POST['datefilter'] != ''){
			$start_date = $_POST['datefilter']['start'];
			$end_date = $_POST['datefilter']['end'];
				
			$files = array_filter($this->files, function($file_date) use ($start_date, $end_date){
				$file_date = date("Y-m-d H:i", strtotime($file_date));
				$rslt = $file_date >= $start_date && $file_date <= $end_date;
				
				return $rslt;
			});
		}

		$rslt['count'] = count($files);
		$rslt['source'] = array_keys($files);
		foreach ($files as $file_path => $file_date) {
			$filename = basename(base64_decode($file_path));
			$rslt['data'][$file_path] = array(
				'filename'=>$filename,
				'date'=>date("d F Y H:i", strtotime($file_date))
			);
		}
		echo json_encode($rslt);
		// wp_die(json_encode($files));
		wp_die();
	}

	/**
     * Bulk download of market data files. Called via JS
     */
	public function download_market_data_files(){
   		if(isset($_GET['post']) && isset($_GET['sort']) && isset($_GET['page']) && isset($_GET['start']) && isset($_GET['end'])){
   			$post_id = $_GET['post'];
	   		$sort = $_GET['sort'];
	   		$page = $_GET['page'];
	   		$start_date = $_GET['start'];
	   		$end_date = $_GET['end'];

	   		$this->scan_market_data_files($post_id);

			if($sort == 'asc'){
				asort($this->files); //Ascending by date
			}

	   		$wp_upload_dir = wp_upload_dir();
			$zip_name = $wp_upload_dir['basedir'].'/'.get_the_title($post_id);
			$folder_name = get_field('upload_folder', $post_id);


	   		if($start_date && $end_date){

				$files = array_filter($this->files, function($file_date) use ($start_date, $end_date){
					$file_date = date("Y-m-d H:i", strtotime($file_date));
					$rslt = $file_date >= $start_date && $file_date <= $end_date;
					
					return $rslt;
				});
				$zip_name .= '_'.str_replace(":", "", $start_date).'-'.str_replace(":", "", $end_date);
	   		}else{
	   			$end = $page * 24;
				$start = $end - 24;

				$files = array_filter($this->files, function($file_path) use ($start, $end){
					$index = array_search($file_path, array_keys($this->files));
					$rslt = $index >= $start && $index < $end;

					return $rslt;
				}, ARRAY_FILTER_USE_KEY);
	   		}

	   		$zip_name .= '.zip';

	   		if($files){
		 		$zip = new ZipArchive();

		 		$opened = $zip->open( $zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);
	        	if ($opened === TRUE){
		            // Add files to the zip file
		            $folders = array();
		            foreach ($files as $file_url => $file_date) {
		            	$file_url = base64_decode($file_url);
            			$file_url = str_replace("\\", "/", $file_url);
		   				$file_name = basename($file_url);
		   				$file_folder = explode($folder_name.'/', $file_url);

		   				if (!$zip->addFile($file_url, $file_folder[1])) {
						    die("Could not add file $file_url");
						}
		   			}

	   				

		            $zip->close();
		        }

	         	header("Content-type: application/zip"); 
		        header('Content-Disposition: attachment; filename="'.basename($zip_name).'"'); 
		        header("Content-length: " . filesize($zip_name));
		        header("Pragma: no-cache"); 
		        header("Expires: 0"); 
		        readfile("$zip_name");
		        unlink($zip_name); //remove tmp file
		        exit();

	   		}else{
				global $wp_query;
	            $wp_query->set_404();
	            status_header( 404 );
	            get_template_part( 404 ); exit();
	   		}
   		}
	}

	/**
     * Stored all scanned files from the given directory
     * @param int  $post_id Market Data ID
     * @param bool $get_all Get all files or limited files only
     * @return array Files scanned
     */
	public function scan_market_data_files($post_id="", $get_all=false){
		global $post;
		$this->files = array();
		$post_id = ($post_id) ? $post_id : $post->ID;

		$folder_name = get_field('upload_folder', $post_id);
		$min_date = (!$get_all) ? $this->get_file_limit_days($post_id) : '';

		// $folder_dir = __DIR__.'../../../../uploads/downloads';
		$folder_dir = wp_upload_dir()['basedir'].'/downloads/data/'.$folder_name;

		$this->dirToArray($folder_dir, $min_date);
		arsort($this->files); //Descending by date
		// wp_die(json_encode($this->dates));
		return $this->files;
	}

	/**
     * Archive market data files that already reached the retention days. Called via CRON
     */
	public function archive_market_data(){
	 	if(isset($_GET['archive_market_data'])){
	 		$rslt = array('status'=>false);

			$args = array(
	            'post_type'=>'market-data',
	            'numberposts'=>-1,
	            'post_status'=>'publish'
	        );

	        $market_data_posts = get_posts($args);
	        
        	$archive_files = array();

        	$archived_dir = wp_upload_dir()['basedir'].'/archived';
            $archived_market_data_folder = $archived_dir.'/data';


            if(!file_exists($archived_dir)){ mkdir($archived_dir, 0777); }
            if(!file_exists($archived_market_data_folder)){ mkdir($archived_market_data_folder, 0777); }

	        foreach ($market_data_posts as $key => $market_data) {
	        	// $this->files = array();

	        	$post_id = $market_data->ID;
	        	$post_title = $market_data->post_title;
	        	$data_retention = get_field('md_data_retention', $post_id);
	        	if($data_retention == 0){ continue; } //skip if retention is 0
	        	$this->scan_market_data_files($post_id, true);


	        	if($this->files){
	        		$time = current_time("H:i:s");
	        		$date = current_time("Y-m-d");
	        		$datetime = strtotime($date.' '.$time.' -'.$data_retention.' day');

	        		foreach ($this->files as $file_url => $file_date) {
		            	$file_url = base64_decode($file_url);
            			$file_url = str_replace("\\", "/", $file_url);
		   				$file_date = strtotime($file_date);
		   				// $rslt2 = $file_date < $datetime; 
		   				// echo date('m/d/Y H:i:s', $file_date) . " < ". date("m/d/Y H:i:s", $datetime) ." = ".$rslt2."<br>";
		   				$date_uploaded = filectime($file_url);
						$date_uploaded = new DateTime(date('r',$date_uploaded));
						date_timezone_set($date_uploaded, timezone_open('Asia/Manila'));
						$date_uploaded = date_format($date_uploaded,'Y-m-d H:i:s');

		   				if($file_date <= $datetime){ //file exceed data retention

		   					$archive_files[$file_url] = array('filename'=>basename($file_url),
		   													  'date_uploaded'=>$date_uploaded,
		   													  'target_date'=>date("Y-m-d H:i:s", $file_date),
		   													  'file_size'=>filesize($file_url),
		   													  'md_page'=>$post_title,
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
		            foreach ($archive_files as $file_url => $file_data) {
		   				$file_folder = explode('data/', $file_url);
	   					if (!$zip->addFile($file_url, $file_folder[1])) {
					    	die("Could not add file $file_url");
						}
		   			}

		            $zip->close();
		        }


		        $log_files = array();
	         	foreach ($archive_files as $file_url => $file_data) {
	            	$unlink = unlink($file_url); //delete file
	            	if($unlink){
	            		$md_page = $file_data['md_page'];
	            		$log_files[$md_page][] = $file_data;
	            	}
	            }
             	$bytes = filesize($zip_name);
	            $zip_size = ($bytes / 1024) / 1024; //mb
	            $archive_folder_size = $this->get_archive_folder_size($archived_market_data_folder);
	            $total_size = $zip_size + $archive_folder_size;

	            $md_settings = get_option('iemop_market_downloads_settings', array());
	            $archive_cap = (isset($md_settings['archive_cap'])) ? $md_settings['archive_cap'] : 1024;
	            
	            $rslt['zip_size'] = $zip_size;
				$rslt['total_size'] = $total_size;			            
	            
	            if($total_size > $archive_cap){ //if total size exceed the given maximum capacity
	            	$ctr = 1;
	            	$new_archive_folder_name = $archived_market_data_folder.'-'.$ctr;
	            	while (file_exists($new_archive_folder_name)) {
	            		$ctr++;
            			$new_archive_folder_name = $archived_market_data_folder.'-'.$ctr;
	            	}

	            	rename($archived_market_data_folder, $new_archive_folder_name);
	            	if(!file_exists($archived_market_data_folder)){ mkdir($archived_market_data_folder, 0777); }
	            }

		        $rslt['status'] = rename($zip_name, $archived_market_data_folder.'/'.basename($zip_name));

		        foreach ($log_files as $md_page => $log_file) {
		        	$insert_logs = array();
		        	$total_bytes = 0;
		        	foreach ($log_file as $key => $file) {
		        		$total_bytes += $file['file_size'];
		        	}

		        	$activity = ($rslt['status']) ? "Successfully archived file(s) on ".$md_page : "Failed to archive file(s) on ".$md_page;
        		 	$insert_logs[] = "(".$log_file[0]['post_id'].", '".$activity."', '".basename($zip_name)."', 'Market Data', '".current_time("Y-m-d H:i:s")."', ".$total_bytes.")";

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

	/**
     * Get the minimum day of accessing a file
     * @param int $post_id Market Data ID
     * @return string Minimum date
     */
	private function get_file_limit_days($post_id=""){
		global $post;
		$post_id = ($post_id) ? $post_id : $post->ID;

		$search_limit = get_field('search_file_limit', $post_id);
		$min_date = date('m/d/Y', strtotime("-".$search_limit.' days'));

		return $min_date;
	}

	/**
     * Scan all files recursively within the given directory.
     * @param string $dir      The directory
     * @param string $min_date The minimum date to access a files
     * @return array Other directory inside the $dir 
     */
	private function dirToArray($dir, $min_date="") {
   		$result = array();
   		

	   	$cdir = scandir($dir);
	   	foreach ($cdir as $key => $value){
	      	if (!in_array($value,array(".",".."))){
	         	if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
	            	$result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value, $min_date);
	         	}
	         	else{
	         		$file_path = $dir.'/'.$value;

					$split = explode("_", $value);
					$date = pathinfo(end($split), PATHINFO_FILENAME);
					$date = date('m/d/Y H:i', strtotime($date));


	         		if($min_date){ // get files within min date and today
		         		// $test = $date >= $min_date && $date <= current_time('m/d/Y H:i');
	         			// $this->dates[] = array('min_days'=>$min_date, 'date'=>$date, 'today'=>current_time('m/d/Y H:i'), 'rslt'=>$test);
	         			if($date >= $min_date && $date <= current_time('m/d/Y H:i')){
		         			$encoded_path = base64_encode($file_path);
		         			$this->files[$encoded_path] = $date; 
		         		}	
	         		}else{ //get all files
	         			$encoded_path = base64_encode($file_path);
	         			$this->files[$encoded_path] = $date;
	         		}
	         	}
	      	}
	   	}
	  
	   return $result;
	}
}