<?php
/**
 * Create facility for Market Data Logs
 */
class MD_Logs {

	public $file = MD_PLUGIN_FILE;
	public $prefix = MD_PREFIX;
	public $admin_url = MD_PLUGIN_ADMIN_URL;

	public $MD_Model;
	public $logs_table;

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

		require_once $this->admin_url.'controller/class-md-logs-table.php';

		if(isset($_GET['page']) && ($_GET['page'] == 'market_data_logs' || $_GET['page'] == 'market_reports_logs')){
			$this->logs_table = new MD_Logs_Table($wpdb);	
		}
	}

	/**
     * Initialize all action or filter hooks 
     */
	public function inits() {
		add_action('admin_menu', array($this, 'add_market_downloads_logs_menu'));
		add_action('admin_enqueue_scripts', array($this, 'include_assets'));
		add_action( 'wp_ajax_export_log_files', array($this, 'export_log_files'));
	}

	/**
     * Include all js and css for News post type
     */
	public function include_assets() {
		
		if ( is_admin() && isset($_GET['page']) && ($_GET['page'] == 'market_data_logs' || $_GET['page'] == 'market_reports_logs') ) {

			wp_enqueue_script( $this->prefix.'-market-downloads-logs-js', plugins_url('/admin/js/market-downloads-logs.js', $this->file), array('jquery'), "", true);
			
			wp_localize_script( $this->prefix.'-market-downloads-logs-js', 'php_data',
	            array(
	            	'ajax_url'=>admin_url('admin-ajax.php'),
	            	'post_type'=>$_GET['post_type']
	    		)
	        );
    		
		}
	}

	/**
     * Register Market Data and Market Reports Logs page
     */
	public function add_market_downloads_logs_menu() {

	    add_submenu_page(
	        'edit.php?post_type=market-data',
	        __( 'Market Data Logs', 'market-downloads' ),
	        __( 'Market Data Logs', 'market-downloads' ),
	        'manage_market_data_logs',
	        'market_data_logs',
	        array($this, 'md_logs_page')
	    );

	    add_submenu_page(
	        'edit.php?post_type=market-reports',
	        __( 'Market Reports Logs', 'market-downloads' ),
	        __( 'Market Reports Logs', 'market-downloads' ),
	        'manage_market_reports_logs',
	        'market_reports_logs',
	        array($this, 'md_logs_page')
	    );
	}

	/**
     * Include the Market Download Logs page template
     */
	public function md_logs_page() {
		// $page = (isset($_GET['page'])) ? $_GET['page'] : "Market Reports";
		$this->logs_table->type = (get_admin_page_title() == "Market Data Logs") ? "Market Data" : "Market Reports";

		$logs_table = $this->logs_table;

	    include($this->admin_url . 'view/market-downloads-logs.php');	

	}

	/**
     * Export log files in csv format. Called via AJAX
     */
	public function export_log_files(){
		// $rslt = array();

		$log_id = $_POST['log_id'];
		$act = $_POST['act'];
		$date = date("YmdHis", strtotime($_POST['date']));
		$post_type = $_POST['post_type'];
		$post_id = $_POST['post_id'];

		$filename = $post_type.'-files-log-'.$date.'.csv';

		$upload_path = wp_upload_dir()['basedir'].'/';
		$full_path = $upload_path.$filename;

		$fp = fopen($full_path, 'w');
		$log_files = $this->MD_Model->get_log_files($log_id);
		$total = count($log_files);
		fputcsv($fp, array('Date', date("Y-m-d H:i:s", strtotime($date))));
		if($post_type == 'market-data'){
			$expected_data = get_field('expected_data', $post_id);
			$expected = ($expected_data) ? $expected_data : 'N/A';
			fputcsv($fp, array('Expected Data', '="' . $expected . '"'));
		}
		
		fputcsv($fp, array('Actual Data', '="' . $total . '"'));
		fputcsv($fp, array('Activity', $act));
		fputcsv($fp, array('', ''));
		fputcsv($fp, array('Filename', 'Date Uploaded', 'Target Date', 'File size'));

		foreach ($log_files as $log_file) {
			$row = array($log_file['filename'], $log_file['date_uploaded'], $log_file['target_date'], convert_filesize($log_file['file_size']));
		    
		    fputcsv($fp, $row);
		}

		fclose($fp);

		echo base64_encode($full_path);
		wp_die();
		
	}

}