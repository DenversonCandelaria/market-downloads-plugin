<?php
/**
 * Create facility for custom post type Market Data
 */
class MD_Market_Data {

	public $file = MD_PLUGIN_FILE;
	public $prefix = MD_PREFIX;
	public $admin_url = MD_PLUGIN_ADMIN_URL;

	public $MD_Model;
	public $files = array();

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

		add_action( 'init', array($this, 'register_cpt_Market_Data') );
		add_action( 'init', array($this, 'create_market_data_category'), 0 );

		add_action( 'manage_market-data_posts_columns', array($this, 'modify_column'));
		add_action( 'manage_market-data_posts_custom_column', array($this, 'modify_column_content'), 10, 2);	
		add_action( 'admin_enqueue_scripts', array($this, 'include_admin_assets'));
		add_action( 'edit_form_after_editor', array($this, 'append_post_id_input') );
		add_action( 'add_meta_boxes', array($this, 'remove_categories_meta_box'));
		add_action('acf/save_post', array($this, 'update_post_title'));
		add_action( 'untrash_post', array($this, 'untrash_market_data') );
		add_action( 'admin_head',  array($this, 'hide_delete_link'));

		add_action('init',array($this, 'scan_uploaded_market_data_file'));

		add_filter( 'page_row_actions', array($this, 'remove_quick_edit_and_view'), 10, 2 );
		add_filter( 'market_data_category_row_actions', array($this, 'remove_delete_and_view'), 10, 2 );
		add_filter('acf/validate_value/name=upload_folder', array($this, 'validate_upload_folder'), 10, 4);
		add_filter('acf/validate_value/name=expected_data', array($this, 'validate_expected_data'), 10, 4);

	}

	/**
     * Register custom post type Market Data
     */
	public function register_cpt_Market_Data() {
	 	$labels = array( 
	        'name' => _x( 'All Market Data', 'market-downloads' ),
	        'singular_name' => _x( 'Market Data', 'market-downloads' ),
	        'add_new' => _x( 'Add New', 'market-downloads' ),
	        'add_new_item' => _x( 'Add Market Data', 'market-downloads' ),
	        'edit_item' => _x( 'Edit Market Data', 'market-downloads' ),
	        'new_item' => _x( 'New Market Data', 'market-downloads' ),
	        'all_items' => __( 'All Market Data' ),
	        'view_item' => _x( 'View Market Data', 'market-downloads' ),
	        'search_items' => _x( 'Search Market Data', 'market-downloads' ),
	        'not_found' => _x( 'No Market Data found', 'market-downloads' ),
	        'not_found_in_trash' => _x( 'No Market Data found in Trash', 'market-downloads' ),
	        'parent_item_colon' => _x( 'Parent Market Data:', 'market-downloads' ),
	        'menu_name' => _x( 'Market Data', 'market-downloads' ),
	    );
	    
	    $args = array( 
	        'labels' => $labels,
	        'hierarchical' => true,            
	        'supports' => array(),
	        'public' => true,
	        'show_ui' => true,
	        'show_in_menu' => true,
	        'menu_position' => 8,
			'menu_icon'	=> 'dashicons-download',
	        'show_in_nav_menus' => false,
	        'publicly_queryable' => true,
	        'has_archive' => false,
	        'query_var' => true,
	        'can_export' => true,
	        'rewrite' => true,
	        'capability_type' => array('market_data', 'market_data'),
			// 'map_meta_cap'=> true,
	        'exclude_from_search' => false,
	    );

	    register_post_type( 'market-data', $args );
	}

	/**
     * Register custom taxonomy for market data category
     */
	public function create_market_data_category() {

		$labels = array(
			'name'                       => 'Categories',
			'singular_name'              => 'Category',
			'menu_name'                  => 'Category',
			'all_items'                  => 'All Category',
			'parent_item'                => 'Parent Category Item',
			'parent_item_colon'          => 'Parent Category Item:',
			'new_item_name'              => 'New Category',
			'add_new_item'               => 'Add New Category',
			'edit_item'                  => 'Edit Category',
			'update_item'                => 'Update Category',
			'view_item'                  => 'View Category',
			'separate_items_with_commas' => 'Separate categories with commas',
			'add_or_remove_items'        => 'Add or remove categories',
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular Categories',
			'search_items'               => 'Search Categories',
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No categories',
			'items_list'                 => 'Category list',
			'items_list_navigation'      => 'Category list navigation',
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'capabilities'				 => array(
				'manage_terms'  =>   'manage_market_data_categories'
			)
		);

		register_taxonomy( 'market_data_category', array( 'market-data' ), $args );

	}

	/**
     * Remove default Category box on Add/Edit Market Data page
     */
	public function remove_categories_meta_box() {
	    remove_meta_box( 'market_data_categorydiv', 'market-data', 'side' );// remove the Categories box
	}

	/**
     * Modified displayed column of listed Market Data
     * @param  array $columns Contains column slug and names
     * @return array Contains array of modified columns
     */
	public function modify_column( $columns ) {

		unset($columns['taxonomy-market_data_category']);
	    unset($columns['date']);
	    $columns['taxonomy-market_data_category'] = __('Category');
	    $columns['data-retention'] = __('Data Retention');
	    $columns['search-file-limit'] = __('Search File Limit');
	    $columns['upload-folder'] = __('Upload Folder');
	    $columns['date'] = __('Date');

	    return $columns;
	}

	/**
     * Modified column content of listed Market Data
     * @param string $column  Slug of column
     * @param int    $post_id Market Data ID
     */
	public function modify_column_content($column, $post_id) {


	    if ( $column == 'data-retention' ) {
	    	$days = '';
	        $data_retention = get_field('md_data_retention', $post_id);
	        $days = ($data_retention > 1) ? ' days' : ' day';
	        $days = ($data_retention) ? $days : '';
	        echo $data_retention.$days;
	    }

	    if ( $column == 'search-file-limit' ) {
	        $search_file_limit = get_field('search_file_limit', $post_id);
	        $days = ($search_file_limit > 1) ? ' days' : ' day';
	        echo $search_file_limit.$days;
	    }

	    if ( $column == 'upload-folder' ) {
	        $upload_folder = get_field('upload_folder', $post_id);

	        echo $upload_folder;
	    }

	  
	}

	/**
     * Include all js and css for Market Data post type
     */
	public function include_admin_assets() {
	   	
	 	global $post_type, $post_id;
		
		if ( is_admin() && ($post_type == 'market-data' || (isset($_GET['post_type']) && $_GET['post_type'] == 'market-data') ) ) {

	    	wp_enqueue_style( $this->prefix.'market-downloads-admin-css', plugins_url( '/admin/css/market-downloads-admin.css', $this->file ));
			wp_enqueue_style( 'jquery-ui-css', plugins_url( '/admin/css/jquery-ui/jquery-ui.min.css', $this->file ));
	    	wp_enqueue_style( 'jquery-ui-theme-css', plugins_url( '/admin/css/jquery-ui/jquery-ui.theme.min.css', $this->file ));

	    	wp_enqueue_script( $this->prefix.'-jquery-ui-js', plugins_url( '/admin/js/jquery-ui/jquery-ui.min.js', $this->file ), array( 'jquery' ), "", true );
    		wp_enqueue_script( $this->prefix.'-market-data-admin-js', plugins_url('/admin/js/market-data-admin.js', $this->file), array( 'jquery', 'general-helper-admin.js' ), "", true);
    		
		}
	}

	/**
     * Modify available actions(Edit, Trash, View) for each Market Data
     * @param array  $actions Default actions
     * @param int    $post    Market Data info
     * @return array Contains array of actions
     */
	public function remove_quick_edit_and_view( $actions = array(), $post = null ) {
		global $pagenow;
		
		if ( !( ( $pagenow == 'edit.php' ) && ( get_post_type() == 'market-data' ) ) ) {
			return $actions;
		}

	    if ( isset( $actions['inline hide-if-no-js'] ) ) {
	        unset( $actions['inline hide-if-no-js'] );
	    }

	    if($post->post_status == 'trash'){
	    	$upload_folder = get_field('upload_folder', $post->ID);
		
			$already_used = $this->check_if_folder_name_is_used($upload_folder, $post->ID);

	    	if($already_used){
	    		$actions['untrash'] = "<span class='unrestored'>Can't be restored (Upload Folder already exist)</span>";	
	    	}
	    }

	    return $actions;
	}

	/**
     * Remove Delete and View action in Category list page if its already used
     * @param  array  $actions Contains all action for Market Data category
     * @param  object $term    The category data
     * @return array  $actions Modified array of actions
     */
	public function remove_delete_and_view( $actions = array(), $term ) {

		unset($actions['view']);
		
		if($term->count > 0){
			unset($actions['delete']);
		}

	    return $actions;
	}

	/**
     * Remove Delete action on Edit Category page if its already used
     */
	public function hide_delete_link () {
	    $current_screen = get_current_screen();


	    // Hides the "Delete" link on the term edit page.
	    if ( 'term' === $current_screen->base && 'market_data_category' === $current_screen->taxonomy && isset($_GET['tag_ID'])){
	    	$term = get_term_by('id', $_GET['tag_ID'], 'market_data_category');
    		if($term->count > 0){ ?>
		        <style>#delete-link { display: none; }</style><?php
    		}
	  
	    }
	}


	/**
     * Append an html input on Add/Edit Banner page that contains current Market Data ID
     * @param array $post Contains Market Data info
     */
	public function append_post_id_input( $post ){
		global $post_type;

		if($post_type == 'market-data'){
			echo "<input type='hidden' name='acf[market_data_post_id][post_ID]' value='".$post->ID."'/>";
			// $folder_option = (isset(var))
			echo "<input type='hidden' name='acf[market_data_folder_option]' id='opt' value=''/>";
		}
	}

	/**
     * Validate if upload folder value is already taken by other Market Data. Run twice via ajax and on submit
     * @param mixed  $valid Whether or not the value is valid (boolean) or a custom error message (string).
     * @param int    $value The Field value
     * @param array  $field The field array containing all settings.
     * @param string $input The field DOM element name attribute.
     * @return mixed Empty if field is valid.
     */
	public function validate_upload_folder( $valid, $folder_name, $field, $input_name ) { 
		global $post_id; //no value on ajax call / has value on submit

		$post_id_js = $_POST['acf']['market_data_post_id']['post_ID'];
		$selected_opt = $_POST['acf']['market_data_folder_option'];

	    // Bail early if value is already invalid.
	    if( $valid !== true ) {
	        return $valid;
	    }

		$dir = $this->create_downloads_dir();
		$folder_name_dir = $dir['data_dir'].'/'.$folder_name;
		// $archived_data_dir = $dir['archived_data_dir'].'/'.$folder_name;


		$orig_folder_name = get_field('upload_folder', $post_id_js);

		if(!$selected_opt){
			$already_used = $this->check_if_folder_name_is_used($folder_name, $post_id_js);

			if($already_used){
				return __("Folder name was already used by another market data");
			}elseif (file_exists($folder_name_dir) && !$already_used && $orig_folder_name != $folder_name) {
				return __("Folder name already exist, Do you want to replace/remove all existing files(if there's any)? <a href='#' class='folder_option' data-opt='3'>Yes</a> | <a href='#' class='folder_option' data-opt='4'>No</a> | <a href='#' class='folder_option' data-opt='0'>Cancel</a>");
			}elseif ($orig_folder_name != $folder_name && $orig_folder_name) {
				return __("Do you want to create a new folder or rename the previous folder? <a href='#' class='folder_option' data-opt='1'>Create</a> | <a href='#' class='folder_option' data-opt='2'>Rename</a> | <a href='#' class='folder_option' data-opt='0'>Cancel</a>");
			}
		}

		
		if($post_id){ //run only 2nd time (on submit)
			if($selected_opt == 2){ //Rename folder
				rename($dir['data_dir'].'/'.$orig_folder_name, $folder_name_dir);
			}elseif ($selected_opt == 3) { //delete exsiting files
				$this->delete_dir_content($folder_name_dir);
				if(!file_exists($folder_name_dir)){ mkdir($folder_name_dir, 0777); }
			}else{ //create new folder
				if(!file_exists($folder_name_dir)){ mkdir($folder_name_dir, 0777); }
				// if(!file_exists($archived_data_dir)){ mkdir($archived_data_dir, 0777); }
			}
		}
		


	    return $valid;
	}

	/**
     * Check if $folder_name is already used
     * @param  string $folder_name Folder name that is needed to verify
     * @param  int    $post_id     Market Data that will exclude from checking
     * @return array Contains IDs of Market Data using the $folder_name
     */
	private function check_if_folder_name_is_used($folder_name, $post_id){

		$args = array(
			'post_type'=>'market-data',
			'numberposts'=>-1,
			'meta_key'=>'upload_folder',
			'fields'=>'ids',
			'meta_value'=>$folder_name,
			'exclude'=>array($post_id),
			'post_status'=>'any'
		);

		$rslt = get_posts($args);

		return $rslt;
	}

	/**
     * Update default wordpress market data title with acf field title
     * @param int $post_id Market Data ID
     */
	public function update_post_title( $post_id ) {
		if(get_post_type() != 'market-data'){ return; }

	    // Check the new value of a specific field.
	    $post_title = get_field('md_title', $post_id);
	    $post_slug = str_replace(" ", "-", $post_title);

	    $post = array(
	    	'ID'=>$post_id,
	    	'post_title'=>$post_title,
	    	'post_name'=>strtolower($post_slug)
    	);

	  	wp_update_post( $post ); //update wp post title from acf title field
	}

	/**
     * Create the upload folder(if not exist) when a Market Data was restored from trash
     * @param int  $post_id The ID of Market Data
     */
	public function untrash_market_data($post_id){
		if(get_post_type() != 'market-data'){ return; }

		$upload_folder = get_field('upload_folder', $post_id);

		$dir = $this->create_downloads_dir();
		$upload_folder_dir = $dir['data_dir'].'/'.$upload_folder;

		if(!file_exists($upload_folder_dir)){ mkdir($upload_folder_dir, 0777); }

	}

	/**
     * Delete the $dir and the files inside recursively
     * @param string $dir The Directory
     * @return bool Status when directory successfully removed
     */
	private function delete_dir_content($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		
		foreach ($files as $file) {
		  	(is_dir("$dir/$file")) ? $this->delete_dir_content("$dir/$file") : unlink("$dir/$file");
		}

		return rmdir($dir);
	}

	/**
     * Create market data downloads and archived folder
     * @return array Paths of the created directories
     */
	private function create_downloads_dir(){
		$wp_upload_dir = wp_upload_dir();

		$downloads_dir = $wp_upload_dir['basedir'].'/downloads';
		$archived_dir = $wp_upload_dir['basedir'].'/archived';

		$data_dir = $downloads_dir.'/data';
		$archived_data_dir = $archived_dir.'/data';

		if(!file_exists($downloads_dir)){ mkdir($downloads_dir, 0777); }
		if(!file_exists($archived_dir)){ mkdir($archived_dir, 0777); }

		if(!file_exists($data_dir)){ mkdir($data_dir, 0777); }
		if(!file_exists($archived_data_dir)){ mkdir($archived_data_dir, 0777); }

		$rslt['data_dir'] = $data_dir;
		$rslt['archived_data_dir'] = $archived_data_dir;

		return $rslt;
	}

	/**
     * Store all uploaded Market Data files to database. Called via CRON
     */
	public function scan_uploaded_market_data_file(){
		if(isset($_GET['scan_uploaded_files'])){
			$rslt = array('status'=>false);
			$this->files = array();

			$this->scan_market_data_files();

			$files = $this->files;
			// $grp_files = array();
			if($files){
				foreach ($files as $folder => $log_files) {
					$insert_logs = array();
					// echo json_encode($grp_files)."<br><br>";
					$post_id = 0;
					$total_size = 0;
					foreach ($log_files as $key => $file) {
						$total_size += $file['file_size'];
						
					}
					$post_id = $this->get_market_data_id($folder);
					$post_title = ($post_id) ? "for ".get_post_field( 'post_title', $post_id ) : "on folder ".$folder;
					$activity = "Successfully uploaded file(s) ".$post_title;

					$insert_logs[] = "(".$post_id.", '".$activity."', '', 'Market Data', '".current_time("Y-m-d H:i:s")."', ".$total_size.")";	

					$log_id = $this->MD_Model->insert_log($insert_logs);
					($log_id) ? $this->MD_Model->insert_log_files($log_id, $log_files) : '';
				}
			}

			echo json_encode($files);
		}
		
	}

	/**
     * Get the Market Data ID with $folder
     * @param int $folder Upload folder name
     * @return int ID of the Market Data 
     */
	public function get_market_data_id($folder){

		$args = array(
			'numberposts'	=> -1,
			'post_type'		=> 'market-data',
			'meta_key'		=> 'upload_folder',
			'meta_value'	=> $folder,
			'fields' => 'ids'
		);

		$rslt = get_posts( $args );

		$post_id = ($rslt) ? $rslt[0] : 0;

		return $post_id;
	}

	/**
     * Scan Market Data files and stored it to a property
     * @param int $post_id Market Data ID
     * @return array Market Data files found
     */
	public function scan_market_data_files($post_id=""){
		global $post;
		$this->files = array();
		$post_id = ($post_id) ? $post_id : $post->ID;

		$folder_dir = wp_upload_dir()['basedir'].'/downloads/data';
		if(!file_exists($folder_dir)){ mkdir($folder_dir, 0777); }
		
		$this->get_market_data_files($folder_dir);
		// arsort($this->files); //Descending by date
		return $this->files;
	}

	/**
     * Scan files on $dir recursively
     * @param string $dir Market Data ID
     * @return array Child directories of $dir 
     */
	private function get_market_data_files($dir) {
   		$result = array();

	   	$cdir = scandir($dir);
	   	foreach ($cdir as $key => $value){
	      	if (!in_array($value,array(".",".."))){
	         	if (is_dir($dir . '/' . $value)){
	            	$result[$value] = $this->get_market_data_files($dir . '/' . $value);
	         	}
	         	else{
	         		$file_path = $dir.'/'.$value;
	         		$file_created = filemtime($file_path);
					$file_created = new DateTime(date('r',$file_created));
					date_timezone_set($file_created, timezone_open('Asia/Manila'));
					$file_date = date_format($file_created,'Y-m-d H:i:s');

					$split = explode("_", $value);
					$type = $split[0];

					$info = pathinfo($split[1]);
					$ymd =  basename($split[1],'.'.$info['extension']);
					$target_date = date("Y-m-d H:i:s", strtotime($ymd));

					$filename = basename($file_path);
					$upload_path = str_replace(wp_upload_dir()['basedir'].'/', '', $file_path);

					if($this->MD_Model->get_existing_market_data_file($upload_path, $file_date)){ continue; }
					$folder = $this->get_md_folder($file_path);

					$this->files[$folder][] = array(
         				'filename'=>$filename,
         				'date_uploaded'=>$file_date,
         				'target_date'=>$target_date,
					 	'file_size'=>filesize($file_path),
					 	'file_path'=>$file_path,
					 	'upload_path'=>$upload_path
         			);	
	         	}
	      	}
	   	}
	  
	   return $result;
	}

	/**
     * Get the upload folder name where the $file_path
     * @param string $file_path Path of the file
     * @return string Upload folder name
     */
	private function get_md_folder($file_path){

		$segments = explode('data/', $file_path);
		$folder = explode('/', $segments[1]);

		return $folder[0];
	}

	/**
     * Validate the value of Expected data field
     * @param mixed  $valid Whether or not the value is valid (boolean) or a custom error message (string).
     * @param int    $value The Field value
     * @param array  $field The field array containing all settings.
     * @param string $input The field DOM element name attribute.
     * @return mixed Empty if field is valid.
     */
	public function validate_expected_data( $valid, $expected_data, $field, $input_name ) { // run twice via ajax and on submit

	    // Bail early if value is already invalid.
	    if( $valid !== true) {
	        return $valid;
	    }

	    $min_files_field = 'field_5f2003c51b38d';
	    $minimum = isset($_POST['acf'][$min_files_field]) ? $_POST['acf'][$min_files_field] : 0;

	    // wp_die($_POST['acf']['field_5f2003c51b38d']." == ".$expected_data);
	    if($expected_data < $minimum){
	    	return _("This should be equal or higher than the minimum number of files");
	    }

	    return $valid;
	}
}