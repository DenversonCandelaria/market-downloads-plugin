<?php
/**
 * Create facility for custom post type Market Reports
 */
class MD_Market_Reports {

	public $file = MD_PLUGIN_FILE;
	public $prefix = MD_PREFIX;
	public $admin_url = MD_PLUGIN_ADMIN_URL;

	public $MD_Model;
	public $files_table;

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

		//Insert here all needed models
		require_once MD_PLUGIN_DIR.'models/class-md-market-downloads-model.php';
		$this->MD_Model = new MD_Market_Downloads_Model($wpdb);

		require_once $this->admin_url.'controller/class-md-browse-files-table.php';

		if(isset($_GET['post_type']) && $_GET['post_type'] == 'market-reports'){
			$this->files_table = new MD_Browse_Files_Table($wpdb);	
		}
		
	}

	/**
     * Initialize all action or filter hooks 
     */
	public function inits() {

		add_action( 'init', array($this, 'register_cpt_Market_Reports') );
		add_action( 'init', array($this, 'create_market_reports_category'), 0 );

		add_action( 'manage_market-reports_posts_columns', array($this, 'modify_column'));
		add_action( 'manage_market-reports_posts_custom_column', array($this, 'modify_column_content'), 10, 2);	
		add_action( 'admin_enqueue_scripts', array($this, 'include_admin_assets'));
		add_action( 'edit_form_after_editor', array($this, 'append_post_id_input') );
		add_action( 'add_meta_boxes', array($this, 'remove_categories_meta_box'));
		add_action( 'wp_ajax_load_parent_category_item_field_ajax', array($this, 'load_parent_category_item_field_ajax'));
		add_action( 'acf/save_post', array($this, 'update_post_title'));
		add_action( 'untrash_post', array($this, 'untrash_market_report') );
		add_action( 'wp_ajax_upload_file', array($this, 'upload_file'));
		add_action( 'admin_menu', array($this, 'add_upload_files_page'));
		add_action( 'admin_menu', array($this, 'add_browse_files_page'));
		add_action( 'wp_ajax_delete_files', array($this, 'delete_files'));
		add_action( 'wp_ajax_add_to_top_page', array($this, 'add_to_top_page'));
		add_action( 'admin_head',  array($this, 'hide_delete_link'));
		add_action( 'trashed_post', array($this, 'trashed_market_report_page') );

		add_filter( 'page_row_actions', array($this, 'remove_quick_edit_and_view'), 10, 2 );
		add_filter( 'acf/validate_value/name=mr_title', array($this, 'validate_title'), 10, 4);
		if(isset($_GET['page']) && $_GET['page'] == 'browse-files'){
			add_filter( 'set-screen-option', array($this, 'set_screen'), 10, 3 );	
		}
		add_filter( 'market_reports_category_row_actions', array($this, 'remove_delete_and_view'), 10, 2 );
	}
 	
	/**
     * Register custom post type Market Reports
     */
	public function register_cpt_Market_Reports() {
	 	$labels = array( 
	        'name' => _x( 'All Market Reports', 'market-downloads' ),
	        'singular_name' => _x( 'Market Report', 'market-downloads' ),
	        'add_new' => _x( 'Add New', 'market-downloads' ),
	        'add_new_item' => _x( 'Add Market Report', 'market-downloads' ),
	        'edit_item' => _x( 'Edit Market Report', 'market-downloads' ),
	        'new_item' => _x( 'New Market Report', 'market-downloads' ),
	        'all_items' => __( 'All Market Reports' ),
	        'view_item' => _x( 'View Market Report', 'market-downloads' ),
	        'search_items' => _x( 'Search Market Reports', 'market-downloads' ),
	        'not_found' => _x( 'No Market Report found', 'market-downloads' ),
	        'not_found_in_trash' => _x( 'No Market Reports found in Trash', 'market-downloads' ),
	        'parent_item_colon' => _x( 'Parent Market Reports:', 'market-downloads' ),
	        'menu_name' => _x( 'Market Reports', 'market-downloads' ),
	    );
	    
	    $args = array( 
	        'labels' => $labels,
	        'hierarchical' => true,            
	        'supports' => array(),
	        'public' => true,
	        'show_ui' => true,
	        'show_in_menu' => true,
	        'menu_position' => 9,
			'menu_icon'	=> 'dashicons-download',
	        'show_in_nav_menus' => false,
	        'publicly_queryable' => true,
	        'has_archive' => false,
	        'query_var' => true,
	        'can_export' => true,
	        'rewrite' => true,
	        'capability_type' => array('market_report', 'market_reports'),
			'map_meta_cap'=> true,
	        'exclude_from_search' => false,
	    );

	    register_post_type( 'market-reports', $args );
	}

	/**
     * Register custom taxonomy for market reports category
     */
	public function create_market_reports_category() {

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
				'manage_terms'  =>   'manage_market_reports_categories'
			)
		);

		register_taxonomy( 'market_reports_category', array( 'market-reports' ), $args );

	}

	/**
     * Remove default Category box on Add/Edit Market Reports page
     */
	public function remove_categories_meta_box() {
	    remove_meta_box( 'market_reports_categorydiv', 'market-reports', 'side' );// remove the Categories box
	}

	/**
     * Modified displayed column of listed Market Reports
     * @param  array $columns Contains column slug and names
     * @return array Contains array of modified columns
     */
	public function modify_column( $columns ) {

		unset($columns['taxonomy-market_reports_category']);
	    unset($columns['date']);
	    $columns['taxonomy-market_reports_category'] = __('Category');
	    $columns['data-retention'] = __('Data Retention');
	    $columns['date'] = __('Date');

	    return $columns;
	}

	/**
     * Modified column content of listed Market Reports
     * @param string $column  Slug of column
     * @param int    $post_id Market Reports ID
     */
	public function modify_column_content($column, $post_id) {


	    if ( $column == 'data-retention' ) {
	    	$days = '';	
	        $data_retention = get_field('mr_data_retention', $post_id);
	        $days = ($data_retention > 1) ? ' days' : ' day';
	        $days = ($data_retention) ? $days : '';
	        echo $data_retention.$days;
	    }
	  
	}

	/**
     * Include all js and css for Market Reports post type
     */
	public function include_admin_assets() {
	   	
	 	global $post_type;
		
		if ( is_admin() && ($post_type == 'market-reports' || (isset($_GET['post_type']) && $_GET['post_type'] == 'market-reports') ) ) {

			wp_enqueue_style( 'dropzone-css', plugins_url( '/admin/css/dropzone/dropzone.min.css', $this->file ));
			wp_enqueue_style( 'jquery-ui-css', plugins_url( '/admin/css/jquery-ui/jquery-ui.min.css', $this->file ));
	    	wp_enqueue_style( 'jquery-ui-theme-css', plugins_url( '/admin/css/jquery-ui/jquery-ui.theme.min.css', $this->file ));
	    	wp_enqueue_style( 'timepicker-addons-css', plugins_url( '/admin/css/jquery-ui-timepicker/jquery-ui-timepicker-addon.css', $this->file ));
	    	wp_enqueue_style( 'font-awesome-css', plugins_url( '/admin/css/font-awesome/css/font-awesome.css', $this->file ));
	    	wp_enqueue_style( 'sweetalert-css', plugins_url( '/admin/css/sweetalert/sweetalert2.min.css', $this->file ));
	    	wp_enqueue_style( $this->prefix.'market-downloads-admin-css', plugins_url( '/admin/css/market-downloads-admin.css', $this->file ));

	    	wp_enqueue_script( 'dropzone-js', plugins_url('/admin/js/dropzone/dropzone.min.js', $this->file), array(), "", true);
	    	wp_enqueue_script( $this->prefix.'-jquery-ui-js', plugins_url( '/admin/js/jquery-ui/jquery-ui.min.js', $this->file ), array( 'jquery' ), "", true );
	    	wp_enqueue_script( $this->prefix.'-timepicker-addons-js', plugins_url( '/admin/js/jquery-ui-timepicker/jquery-ui-timepicker-addon.js', $this->file ), array( 'jquery' ), "", true );
	    	wp_enqueue_script( 'sweetalert2-js', plugins_url( '/admin/js/sweetalert/sweetalert2.min.js', $this->file ), array(), "", true );
    		wp_enqueue_script( $this->prefix.'-market-reports-admin-js', plugins_url('/admin/js/market-reports-admin.js', $this->file), array( 'jquery', 'general-helper-admin.js', 'sweetalert2-js'), "", true);
    		wp_localize_script( $this->prefix.'-market-reports-admin-js', 'php_data',
	            array(
	            	'ajax_url'=>admin_url('admin-ajax.php'),
	            	'home_url'=>get_home_url(),
	            	'parent_cats'=>$this->get_parent_categories(),
	            	'post_id'=>sanitize_text_field($_GET['post']),
	            	'date_today'=>current_time("Y-m-d"),
	            	'max_filesize'=>$this->get_max_filesize(),
	            	'max_files'=>$this->get_max_files()
	    		)
	        );
	        if(isset($_GET['page']) && ($_GET['page'] == 'browse-files' || $_GET['page'] == 'upload-files')){
	        	add_action( 'admin_print_scripts', array($this, 'dequeue_script'), 100 ); //fixed conflict in plugin (Category Order and Taxonomy Terms Order)
	        }
		}
	}

	/**
     * Dequeue other plugins js to avoid conflict
     */
	public function dequeue_script() {
	    wp_dequeue_script( 'jquery-ui-sortable' );
	    wp_dequeue_script( 'scporderjs' );
	    wp_dequeue_script( 'plt-quick-add' );
	}

	/**
     * Modify available actions(Edit, Trash, View, Upload Files, Browse Files) for each Market Reports
     * @param array  $actions Default actions
     * @param int    $post    Market Reports data
     * @return array Contains array of actions
     */
	public function remove_quick_edit_and_view( $actions = array(), $post = null ) {
		global $pagenow;
		
		if ( !( ( $pagenow == 'edit.php' ) && ( get_post_type() == 'market-reports' ) ) ) {
			return $actions;
		}

	    // Remove the Quick Edit and View link
	    if ( isset( $actions['inline hide-if-no-js'] ) ) {
	        unset( $actions['inline hide-if-no-js'] );
	        // unset( $actions['view'] );
	    }
	    $post_id = $post->ID;
	    if($post->post_status != 'trash'){
    	 	$actions['upload_files'] = "<a href='".admin_url()."edit.php?post_type=market-reports&page=upload-files&post=".$post_id."'>Upload Files</a>";
	    	$actions['browse_files'] = "<a href='".admin_url()."edit.php?post_type=market-reports&page=browse-files&post=".$post_id."'>Browse Files</a>";
	    }
	   

	    if($post->post_status == 'trash'){
	    	$post_slug = str_replace("__trashed", "", $post->post_name);
	    	$already_exist = $this->check_if_title_exist($post_slug, $post_id);

	    	if($already_exist){
	    		$actions['untrash'] = "<span class='unrestored'>Can't be restored (Title already exist)</span>";	
	    	}
	    }
	    return $actions;
	}

	/**
     * Remove Delete and View action in Category list page if its already used
     * @param  array  $actions Contains all action for Market Reports category
     * @param  object $term    The category data
     * @return array  $actions Modified array of actions
     */
	public function remove_delete_and_view( $actions = array(), $term ) {

		unset($actions['view']);
		if(get_cat_count($term->term_id, 'market_reports_category')){
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
	    if ( 'term' === $current_screen->base && 'market_reports_category' === $current_screen->taxonomy && isset($_GET['tag_ID'])){
    		if(get_cat_count($_GET['tag_ID'], 'market_reports_category')){ ?>
		        <style>#delete-link { display: none; }</style><?php
    		}
	  
	    }
	}

	/**
     * Get all market reports parent categories. Called via AJAX
     */
	public function load_parent_category_item_field_ajax(){
		$rslt = $this->get_parent_categories();

		echo json_encode($rslt);
		die();
	}

	/**
     * Get all market reports parent categories
     * @return array Parent Categories
     */
	private function get_parent_categories(){
		$rslt = array();

		$args = array('taxonomy'=>'market_reports_category', 'hide_empty' => false, 'parent'=>0, 'orderby'=>'term_id','order'=>'ASC');
		$parent_cats = get_terms($args);
		// $rslt['-1'] = 'None';
		// wp_die(var_dump($parent_cats));
		foreach ($parent_cats as $key => $parent_cat) {
			$rslt[$parent_cat->term_id] = $parent_cat->name;
		}
		// rsort($rslt);
		return $rslt;
	}


	/**
     * Append an input field containing the market report id to the Add/Edit page.
     * @param object $post Contains current Market Reports data
     */
	public function append_post_id_input( $post ){
		global $post_type;

		if($post_type == 'market-reports'){
			echo "<input type='hidden' name='custom_post_id' value='".$post->ID."'/>";
			// $folder_option = (isset(var))
		}
	}

	/**
     * Validate Marke Reports Title. Called twice (via ajax & php submit)
     * @param mixed  $valid Whether or not the value is valid (boolean) or a custom error message (string).
     * @param int    $value The Field value
     * @param array  $field The field array containing all settings.
     * @param string $input The field DOM element name attribute.
     * @return mixed Empty if field is valid.
     */
	public function validate_title( $valid, $title, $field, $input_name ) {
		global $post_id; //no value on ajax call / has value on submit

		$post_id_js = $_POST['custom_post_id'];

	    // Bail early if value is already invalid.
	    if( $valid !== true) {
	        return $valid;
	    }

		$dir = $this->create_downloads_dir();
		$slug = str_replace(" ", "-", $title);

		$title_dir = $dir['reports_dir'].'/'.strtolower($slug);

		$title_exist = $this->check_if_title_exist($slug, $post_id_js);

		if($title_exist){
			return __("Title already exist");
		}

		if($post_id){
			$old_title = get_the_title($post_id);
			$old_title_slug = str_replace(" ", "-", $old_title);
			$old_title_dir = $dir['reports_dir'].'/'.strtolower($old_title_slug);
			// wp_die($post_id);
			if($title != $old_title && $old_title && file_exists($old_title_dir)){
				rename($old_title_dir, $title_dir);
			}else{
				if(!file_exists($title_dir)){ mkdir($title_dir, 0777); }
			}
		}

	    return $valid;
	}

	/**
     * Check if Market Report Title were already exist
     * @param string $slug Whether or not the value is valid (boolean) or a custom error message (string).
     * @param int    $post_id The Field value
     * @return array IDs of Market Data or empty if the title doesn't exist
     */
	private function check_if_title_exist($slug, $post_id){

		$args = array(
			'name'=>$slug,
			'post_type'=>'market-reports',
			'numberposts'=>-1,
			'fields'=>'ids',
			'post_status'=>'any',
			'exclude'=>array($post_id)
		);

		$rslt = get_posts($args);

		return $rslt;
	}

	/**
     * Update default wordpress market reports title with acf field title
     * @param int $post_id Market Reports ID
     */
	public function update_post_title( $post_id ) {
		if(get_post_type() != 'market-reports'){ return; }

	    $post_title = get_field('mr_title', $post_id);
	    $post_slug = str_replace(" ", "-", $post_title);
	    $post_slug = strtolower($post_slug);

	    $post = array(
	    	'ID'=>$post_id,
	    	'post_title'=>$post_title,
	    	'post_name'=>$post_slug
    	);

	  	wp_update_post( $post ); //update wp post title from acf title field
	  	$this->MD_Model->update_file_mr_slug($post_id, $post_slug);

	}

	/**
     * Create the upload folder(if not exist) when a Market Reports was restored from trash
     * @param int  $post_id The ID of Market Reports
     */
	public function untrash_market_report($post_id){
		if(get_post_type() != 'market-reports'){ return; }

		global $post;
		
		$slug = str_replace("__trashed", "", $post->post_name);
		$dir = $this->create_downloads_dir();
		$title_dir = $dir['reports_dir'].'/'.$slug;

		if(!file_exists($title_dir)){ mkdir($title_dir, 0777); }

	}

	/**
     * Register Upload Files page
     */
	public function add_upload_files_page() {
	    add_submenu_page( 
	        // 'edit.php?post_type=market-reports',   //or 'options.php'
	        null,
	        'Upload Files',
	        'Upload Files',
	        'publish_market_reports',
	        'upload-files',
        	array($this,'upload_files_page')
	    );
	}

	/**
     * Upload market reports file. Called via AJAX
     */
	public function upload_file(){
		$post_id = $_POST['post_id'];
		$folder_name = get_post_field( 'post_name', $post_id );
		$post_title = get_post_field( 'post_title', $post_id );
		$dir = $this->create_downloads_dir();
		$upload_dir = $dir['reports_dir'].'/'.$folder_name;

		if(!file_exists($upload_dir)){ mkdir($upload_dir, 0777); }
		
		$rslt = array();
		$insert_file = array();
		$insert_logs = array();
		$log_files = array();
		$activity = "";
		if(!empty($_FILES)){
			$total_size = 0;
			foreach ($_FILES['file']['tmp_name'] as $key => $tmp) {
				$filename = rename_duplicates($upload_dir.'/', $_FILES['file']['name'][$key]);
				$rslt[] = $uploaded = move_uploaded_file($tmp, $upload_dir.'/'.$filename);	
				
				$doc_title = $_POST['doc_title'][$key];
				$desc = $_POST['description'][$key];
				$published_date = date("Y-m-d H:i:s", strtotime($_POST['published_date'][$key]));

				$file_size = $_FILES['file']['size'][$key];

				$rslt['log_files'] = $log_files[] = array(
					'filename'=>$filename,
					'date_uploaded'=>current_time("Y-m-d H:i:s"),
					'target_date'=>$published_date,
					'file_size'=>$file_size,
					'upload_path'=>str_replace(wp_upload_dir()['basedir'].'/', '', $upload_dir).'/'.$filename
				);



				$activity = "Uploading file(s) failed for ".$post_title;
				if($uploaded){
					$activity = "Successfully uploaded file(s) for ".$post_title;
					$insert_file[] = '('.$post_id.', "'.$folder_name.'", "'.$filename.'", "'.$doc_title.'", "'.$desc.'", "'.$published_date.'", 1)';
				}
				$total_size += $file_size;
			}

			$insert_logs[] = "(".$post_id.", '".$activity."', '', 'Market Reports', '".current_time("Y-m-d H:i:s")."', ".$total_size.")";	

			if($insert_file){
				$inserted = $this->MD_Model->insert_file($insert_file);	
				if($inserted){
					$rslt['logs'] = $log_id = $this->MD_Model->insert_log($insert_logs);
					($log_id) ? $this->MD_Model->insert_log_files($log_id, $log_files) : '';
				}
			}
		}
		
		echo json_encode($rslt);
		wp_die();
	}

	/**
     * Include the Upload Files page template
     */
	public function upload_files_page(){
		$max_filesize = $this->get_max_filesize();
		$max_file = $this->get_max_files();
		include($this->admin_url . 'view/market-reports-upload-files.php');
	}

	/**
     * Register Browse files page
     */
	public function add_browse_files_page() {
	    $hook = add_submenu_page( 
	        // 'edit.php?post_type=market-reports',   //or 'options.php'
	        null,
	        'Browse Files',
	        'Browse Files',
	        'publish_market_reports',
	        'browse-files',
        	array($this,'browse_files_page')
	    );

	    if(isset($_GET['page']) && $_GET['page'] == 'browse-files'){
		     add_action( "load-$hook", array($this, 'screen_option') );
		}

	}

	/**
     * Include Browse files page template
     */
	public function browse_files_page(){
		global $wpdb;


		$files_table = $this->files_table;

		if(isset($_GET['action']) && $_GET['action'] == 'edit'){
			$nonce = esc_attr( $_REQUEST['_editnonce'] );
		 	if ( ! wp_verify_nonce( $nonce, 'edit_file' )) {
		      	die( 'Error' );
		      	// echo $_GET['action'];
		    }else{
	    		$data = $this->MD_Model->get_file($_GET['id']);
	    		$slug = get_post_field( 'post_name', $_GET['post'] );

	    		if(isset($_POST['submit'])){
	    			$data = $this->edit_file();
	    		}

	    		$dl_path = wp_upload_dir()['basedir'].'/downloads/reports/'.$slug.'/'.$data['filename']; 
		        $dl_path = base64_encode($dl_path);

		    	include($this->admin_url . 'view/market-reports-edit-file.php');
		    }
			
		}else{
			include($this->admin_url . 'view/market-reports-browse-files.php');	
		}

		
	}

	/**
     * Edit market reports file
     * @return array Contains updated data of market reports file, status and error message
     */
	private function edit_file(){
		$data['suc'] = false;
		$data['err_msg'] = '';
		$update_data = array();

		$data['doc_title'] = sanitize_text_field($_POST['doc_title']);
		$data['description'] = sanitize_text_field($_POST['description']);
		$data['published_date'] = sanitize_text_field($_POST['published_date']);
		$data['filename'] = sanitize_text_field($_POST['filename']);
		
		if($data['doc_title'] == '' || $data['description'] == '' || $data['published_date'] == ''){
			$data['err_msg'] = "Please fill up all required fields";
		}else{
			if(!empty($_FILES['file']['name'])){
				$file_tmp	= $_FILES['file']['tmp_name'];
				$file_size	= $_FILES['file']['size'];
				$file_name	= $_FILES['file']['name'];

				$ext = '.'.pathinfo($file_name, PATHINFO_EXTENSION);
				$max_filesize = $this->get_max_filesize();
				$mb = 1048576 * $max_filesize; //times to 1mb(binary)
				if($file_size > $mb){ //(binary)
					$data['err_msg'] = "File attachment too large, maximum file size is ".$max_filesize."mb only.";
				}elseif (!$this->is_file_valid($ext)) {
					$data['err_msg'] = "Invalid file format. Upload DOC, XLS, CSV, PPT, GIF, JPG, ZIP, and PDF files only";
				}

				if($data['err_msg'] != '') {
					return $data;
				}


				$dir = $this->create_downloads_dir();
				$slug = get_post_field( 'post_name', $_GET['post'] );
				$reports_dir = $dir['reports_dir'].'/'.$slug;
				$old_file = $reports_dir.'/'.$data['filename'];
				$file_name = rename_duplicates($reports_dir.'/', $file_name);

				if(!file_exists($reports_dir)){ mkdir($reports_dir, 0777); }
				if(file_exists($old_file)){ unlink($old_file); }
				$uploaded = move_uploaded_file($file_tmp, $reports_dir.'/'.$file_name);
				if($uploaded){
					$update_data[] = 'filename="'.$file_name.'"';
					$data['filename'] = $file_name;
				}
			}

			$update_data[] = 'doc_title="'.$data['doc_title'].'"';
			$update_data[] = 'description="'.$data['description'].'"';
			$update_data[] = 'published_date="'.$data['published_date'].'"';

			$update = $data['update'] = $this->MD_Model->update_file($_GET['id'], $update_data);

			$data['suc'] = true;
			$data['err_msg'] = '';
			
		}

		return $data;
	}

	/**
     * Check market reports file type is its valid
     * @param string $ext File extension
     * @return bool Returns true if the file is valid
     */
	public function is_file_valid($ext){
		$rslt = false;

		if($ext == '.doc' || $ext == '.DOC' || $ext == '.docx' || $ext == '.DOCX' || $ext == '.xls' || $ext == '.XLS' || $ext == '.xlsx'  || $ext == '.XLSX' || $ext == '.ppt' || $ext == '.PPT'  || $ext == '.pptx' || $ext == '.PPTX'  || $ext == '.pdf' || $ext == '.PDF' || $ext == '.jpg' || $ext == '.JPG' ||  $ext == '.png' ||  $ext == '.PNG' || $ext == '.csv' || $ext == '.CSV' || $ext == '.gif' || $ext == '.GIF' || $ext == '.zip' || $ext == '.ZIP') {

			$rslt = true;
		}

		return $rslt;

	}

	/**
     * Filters a screen option value before it is set.
     * @param mixed  $status The value to save instead of the option value. Default false (to skip saving the current option)
     * @param string $option The option name.
     * @param int    $value  The option value.
     * @return array Contains all Updated data and its status
     */
	public function set_screen( $status, $option, $value ) {
	 	if(isset($_GET['page']) && $_GET['page'] == 'browse-files'){
			return $value;
		}
	}

	/**
     * Create an Screen Option that allow the user to change how many files per page
     */
	public function screen_option() {
		$option = 'per_page';
		$args   = [
			'label'   => 'Files',
			'default' => 5,
			'option'  => 'file_per_page'
		];

		add_screen_option( $option, $args );
		
	}

	/**
     * Delete single or multiple market report files. Called via AJAX
     */
	public function delete_files(){
		$post_id = $_POST['post_id'];
		$ids = $_POST['ids'];
		$filenames = array();
		$success_logs = array();
		$failed_logs = array();
		$rslt = array();

		$slug = get_post_field( 'post_name', $post_id );
		$title = get_post_field( 'post_title', $post_id );
		$path = wp_upload_dir()['basedir'].'/downloads/reports/'.$slug.'/'; 

		$top_reports = get_option('iemop_top_reports', array());
		$total_size = 0;
		$log_files = array();

		foreach ($ids as $key => $id) {
			$file = $this->MD_Model->get_file($id);
			$filenames[] = $file['filename'];
			remove_report_from_top_page($id, $top_reports);

			$file_path = $path.$file['filename'];
			$file_size = filesize($file_path);
			$total_size += $file_size;

			$log_files[] = array(
				'filename'=>$file['filename'],
				'date_uploaded'=>$file['date_uploaded'],
				'target_date'=>$file['published_date'],
				'file_size'=>$file_size
			);
		}

		$delete_db = $this->MD_Model->delete_files($ids);
		$activity = 'Failed to delete file(s) on '.$title;
		if($delete_db){
			$activity = 'Successfully deleted file(s) on '.$title;
			foreach ($filenames as $key => $filename) {
				$file_path = $path.$filename; 
				$rslt[] = $file_path;
				if(file_exists($file_path)){
					$rslt[] = unlink($file_path);
				}
			}
		}

		$insert_logs[] = "(".$post_id.", '".$activity."', '', 'Market Reports', '".current_time("Y-m-d H:i:s")."', ".$total_size.")";
		$log_id = $this->MD_Model->insert_log($insert_logs);
		($log_id) ? $this->MD_Model->insert_log_files($log_id, $log_files) : '';

		echo json_encode($rslt);
		wp_die();
	}

	/**
     * Add/Remove Market reports file from top page. Called with AJAX
     */
	public function add_to_top_page(){
		$rslt = array('suc'=>true, 'msg'=>'');

		$id = $_POST['id'];
		$top_reports = get_option('iemop_top_reports', array());
		$total = count($top_reports);

		if(in_array($id, array_keys($top_reports))){ // remove frop top
			unset($top_reports[$id]);
			$rslt['msg'] = "Removed";
			update_option('iemop_top_reports', $top_reports);
		}else{
			if($total < 4){
				$top_reports[$id] = $id;
				$rslt['msg'] = "Added";
				update_option('iemop_top_reports', $top_reports);
			}else{
				$rslt['suc'] = false;
				$rslt['msg'] = "Top page downloads already reached the maximum of 4 files.";
			}
		}

		echo json_encode($rslt);
		wp_die();
	}

	/**
     * Removed market reports file when the Market Reports move to trashed
     * @param int $post_id Market reports ID
     */
	public function trashed_market_report_page($post_id){

		if(get_post_type() == "market-reports" || (isset($_GET['post_type']) && $_GET['post_type'] == 'market-reports')){ 
			$post_ids = is_array($_GET['post']) ? $_GET['post'] : array($_GET['post']);

			$top_reports = get_option('iemop_top_reports', array());
			
			if ($top_reports) { //remove files from top page that include to trashed mr page
				foreach ($post_ids as $key => $mr_id) { //selected mr page to be trashed
					foreach ($top_reports as $key => $id) {
						$file = $this->MD_Model->get_file($id);
						if($file){
							if($file['mr_id'] == $mr_id){
								remove_report_from_top_page($id, $top_reports);
							}
						}
					}
				}
			}
		}
		
	}

	/**
     * Get the stored maximum file size limit
     * @return int Maximum file size
     */
	private function get_max_filesize(){
		$md_setttings = get_option('iemop_market_downloads_settings', array());
		$file_size = isset($md_setttings['mr_filesize']) ? $md_setttings['mr_filesize'] : 5;
		
		return $file_size;
	}

	/**
     * Get the stored maximum number of files to upload
     * @return int Maximum number of files
     */
	private function get_max_files(){
		$md_setttings = get_option('iemop_market_downloads_settings', array());
		$max_files = isset($md_setttings['mr_max_files']) ? $md_setttings['mr_max_files'] : 50;
		
		return $max_files;
	}

	/**
     * Create market reports downloads and archived folder
     * @return array Paths of the created directories
     */
	private function create_downloads_dir(){
		$wp_upload_dir = wp_upload_dir();

		$downloads_dir = $wp_upload_dir['basedir'].'/downloads';
		$archived_dir = $wp_upload_dir['basedir'].'/archived';

		$reports_dir = $downloads_dir.'/reports';
		$archived_reports_dir = $archived_dir.'/reports';

		if(!file_exists($downloads_dir)){ mkdir($downloads_dir, 0777); }
		if(!file_exists($archived_dir)){ mkdir($archived_dir, 0777); }

		if(!file_exists($reports_dir)){ mkdir($reports_dir, 0777); }
		if(!file_exists($archived_reports_dir)){ mkdir($archived_reports_dir, 0777); }

		$rslt['reports_dir'] = $reports_dir;
		$rslt['archived_reports_dir'] = $archived_reports_dir;

		return $rslt;
	}

}