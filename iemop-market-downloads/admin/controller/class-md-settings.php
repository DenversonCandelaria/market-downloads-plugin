<?php
/**
 * Create facility for Market Downloads settings
 */
class MD_Settings {

	public $file = MD_PLUGIN_FILE;
	public $prefix = MD_PREFIX;
	public $admin_url = MD_PLUGIN_ADMIN_URL;

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
		add_action( 'admin_menu', array($this, 'register_market_downloads_settings_page' ));
		add_action( 'admin_enqueue_scripts', array($this, 'include_admin_assets'));
		add_action( 'wp_ajax_sort_report_file', array($this, 'sort_report_file'));
	}

	/**
     * Include all js and css for Market Downloads Settings
     */
	public function include_admin_assets() {
	   	
	 	global $post_type;
		
		if ( is_admin() && isset($_GET['page']) && $_GET['page'] == 'market_downloads_settings' ) {

			wp_enqueue_style( 'jquery-ui-css', plugins_url( '/admin/css/jquery-ui/jquery-ui.min.css', $this->file ));
	    	wp_enqueue_style( 'jquery-ui-theme-css', plugins_url( '/admin/css/jquery-ui/jquery-ui.theme.min.css', $this->file ));
			wp_enqueue_style( $this->prefix.'market-downloads-settings-css', plugins_url( '/admin/css/market-downloads-settings.css', $this->file ));

			wp_enqueue_script( 'jquery-ui-js', plugins_url( '/admin/js/jquery-ui/jquery-ui.min.js', $this->file ), array( 'jquery' ), "", true );
	    	wp_enqueue_script( $this->prefix.'-market-downloads-settings-js', plugins_url( '/admin/js/market-downloads-settings.js', $this->file ), array( 'jquery' ), "", true );

	    	wp_localize_script( $this->prefix.'-market-downloads-settings-js', 'php_data',
				array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
			);

		}
	}

	/**
     * Register Market downloads settings page
     */
	public function register_market_downloads_settings_page() {
	    add_options_page( 'Market Downloads', 
					   'Market Downloads', 
					   'manage_market_downloads_settings', 
					   'market_downloads_settings', 
					   array($this,'market_downloads_settings_page'),
					   10
		);
	}

	/**
     * Include the Market downloads settings page template
     */
	public function market_downloads_settings_page(){
		$data = get_option('iemop_market_downloads_settings', array());

		$top_reports = get_option('iemop_top_reports', array());
		$total_reports = count($top_reports);

		$data['reports_position'] = (isset($data['reports_position'])) ? $data['reports_position'] : 'asc';
		

		if(isset($_POST['submit'])){
			$data = $this->save_market_downloads_settings();
		}

		include($this->admin_url . 'view/market-downloads-settings-page.php');
	}

	/**
     * Validate & Save market download settings value
     * @return array Contains market downlaods settings value, status and error value 
     */
	private function save_market_downloads_settings(){
		$data = get_option('iemop_market_downloads_settings');
		$data['suc'] = true;
		$data['err_msg'] = '';

		$data['archive_cap'] = sanitize_text_field($_POST['archive_cap']);
		$data['archive_cap'] = ($data['archive_cap']) ? $data['archive_cap'] : 1024;

		$data['archive_cap_mr'] = sanitize_text_field($_POST['archive_cap_mr']);
		$data['archive_cap_mr'] = ($data['archive_cap_mr']) ? $data['archive_cap_mr'] : 1024;

		$data['mr_filesize'] = sanitize_text_field($_POST['mr_filesize']);
		$data['mr_filesize'] = ($data['mr_filesize']) ? $data['mr_filesize'] : 5;

		$data['mr_max_files'] = sanitize_text_field($_POST['mr_max_files']);
		$data['mr_max_files'] = ($data['mr_max_files']) ? $data['mr_max_files'] : 50;

		if($data['mr_max_files'] > 100){
			$data['suc'] = false;
			$data['err_msg'] = 'Maximum number of files per upload is not valid. Maximum of 100 files only';
		}
		
		$data['reports_position'] = $_POST['position'];

		update_option('iemop_market_downloads_settings', $data);

		return $data;

	}

	/**
     * Update Market reports files order. Called via AJAX 
     */
	public function sort_report_file(){
		$sorted_reports = $_POST['sorted'];
		$reports_option = array();

		for ($i=0; $i < count($sorted_reports); $i++) {
			$id = $sorted_reports[$i];
			$reports_option[$id] = $id;
		}

		$rslt = update_option('iemop_top_reports', $reports_option);

		echo json_encode($rslt);

		wp_die();
	}

}