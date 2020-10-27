<?php
/**
 * Create facility for Generationg Summary Report of Market Data
 */
class MD_Summary_Report {

	public $file = MD_PLUGIN_FILE;
	public $prefix = MD_PREFIX;
	public $admin_url = MD_PLUGIN_ADMIN_URL;

	public $MD_Model;

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
		add_action('admin_menu', array($this, 'add_summary_report_menu'));
		add_action('admin_enqueue_scripts', array($this, 'include_assets'));
		add_action( 'wp_ajax_generate_summary_report', array($this, 'generate_summary_report'));
	}

	/**
     * Include all js and css for Summary Reports page
     */
	public function include_assets() {
		
		if ( is_admin() && isset($_GET['page']) && ($_GET['page'] == 'summary_report' || $_GET['page'] == 'summary_report') ) {

			wp_enqueue_style( $this->prefix.'-daterange-css', plugins_url( '/admin/css/jquery.datepick/jquery.datepick.css', $this->file ));
			wp_enqueue_script( $this->prefix.'-daterange-main-js', plugins_url('/admin/js/jquery.datepick/jquery.plugin.min.js', $this->file), array('jquery'), "", true);
			wp_enqueue_script( $this->prefix.'-daterange-js', plugins_url('/admin/js/jquery.datepick/jquery.datepick.js', $this->file), array('jquery'), "", true);

			wp_enqueue_script( $this->prefix.'-summary-report-js', plugins_url('/admin/js/summary-report.js', $this->file), array( 'jquery' ), "", true);

			
			wp_localize_script( $this->prefix.'-summary-report-js', 'php_data',
	            array(
	            	'ajax_url'=>admin_url('admin-ajax.php')
	    		)
	        );
    		
		}
	}

	/**
     * Register Generate summery report page
     */
	public function add_summary_report_menu() {

	    add_submenu_page(
	        'edit.php?post_type=market-data',
	        __( 'Generate Summary Report', 'market-downloads' ),
	        __( 'Generate Summary Report', 'market-downloads' ),
	        'manage_market_data_summary_report',
	        'summary_report',
	        array($this, 'summary_report_page')
	    );
	}

	/**
     * Include the Generate summary report page template
     */
	public function summary_report_page() {
		$terms_arg = array(
			'taxonomy'=>'market_data_category',
		);

		$categories = get_terms($terms_arg);

	    include($this->admin_url . 'view/generate-summary-report.php');	
	}

	/**
     * Generate summary report. Called via AJAX
     */
	public function generate_summary_report(){

		$date_from = date("Ymd", strtotime($_POST['daterange'][0]));
		$date_to = date("Ymd", strtotime($_POST['daterange'][1]));
		$market_data_id = $_POST['market_data'];
		$market_data = get_post($market_data_id);
		$folder = get_field('upload_folder', $market_data_id);
		$minimum_files = get_field('minimum_files', $market_data_id);
		$expected_data = get_field('expected_data', $market_data_id);

		$filename = "PW_MarketData_".$folder."_".$date_from."-".$date_to.".csv";
		$upload_path = wp_upload_dir()['basedir'].'/';
		$full_path = $upload_path.$filename;

		

		$trading_dates = $this->MD_Model->get_summary_report_files($market_data_id, $date_from, $date_to);
		if($trading_dates){
			$fp = fopen($full_path, 'w');
			fputcsv($fp, array("Trading Date", "Market Data Type", "Daily No. of expected file", "No. of Published Files", "Minimum No. of Files that must be generated", "Date Published"));
			foreach ($trading_dates as $key => $row) {
				$trading_date = $row['trading_date'];
				$max_date = date("Y-m-d H:i", strtotime($row['max']));
				$files_count = $this->MD_Model->count_published_files($market_data_id, $trading_date);
				fputcsv($fp, array($trading_date, $market_data->post_title, $expected_data, $files_count, $minimum_files, $max_date));
			}

			fclose($fp);
			echo base64_encode($full_path);
		}else{
			echo false;
		}
		

		wp_die();
		
	}
}