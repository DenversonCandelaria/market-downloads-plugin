<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MD_Browse_Files_Table extends WP_List_Table {

    private $db;
    public $MD_Model;

    /** Class constructor */
    public function __construct($wpdb) {
        $this->db = $wpdb;

        parent::__construct( [
            'singular' => __( 'File', 'market-downloads' ), //singular name of the listed records
            'plural'   => __( 'Files', 'market-downloads' ), //plural name of the listed records
            'ajax'     => false //should this table support ajax?
        ] );

        require_once MD_PLUGIN_DIR.'models/class-md-market-downloads-model.php';
        $this->MD_Model = new MD_Market_Downloads_Model($wpdb);
    }

    /** Text displayed when no order data is available */
    public function no_items() {
        _e( 'No files available.', 'market-downloads' );
    }

    /**
    * Method for doc_title column
    *
    * @param array $item an array of DB data
    *
    * @return string
    */
    function column_doc_title( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'delete_file' );
        $edit_nonce = wp_create_nonce( 'edit_file' );
        $dl_nonce = wp_create_nonce( 'dl_file' );
        $title = '<strong>' . $item['doc_title'] . '</strong>';
        $slug = get_post_field( 'post_name', $_GET['post'] );

        $dl_path = wp_upload_dir()['basedir'].'/downloads/reports/'.$slug.'/'.$item['filename']; 
        $dl_path = base64_encode($dl_path);
        $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
        $top_reports = get_option('iemop_top_reports', array());
        $top_page = (in_array($item['id'], $top_reports)) ? "Remove from Top Page" : "Add to Top Page";

        $actions = [
            'edit' => sprintf( '<a href="?post_type=market-reports&page=%s&post=%d&action=%s&id=%d&_editnonce=%s&paged=%d">Edit File</a>', esc_attr( $_REQUEST['page'] ), $_GET['post'], 'edit', absint( $item['id'] ), $edit_nonce, $paged),
            'download'=>sprintf( '<a href="?post_type=market-reports&_dlnonce=%s&md_file=%s">Download</a>', $dl_nonce, $dl_path ),
            'top'=>sprintf( '<a href="#" data-id="%s" class="add_to_top">'.$top_page.'</a>', esc_attr(absint( $item['id'] )) ),
            'delete' => sprintf( '<a href="#" data-id="%s" class="delete_file">Trash</a>', esc_attr(absint( $item['id'] )))

        ];

        return $title . $this->row_actions( $actions );
    }
    /**
    * Render a column when no column specific method exists.
    *
    * @param array $item
    * @param string $column_name
    *
    * @return mixed
    */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'doc_title':
            case 'description':
                return $item[$column_name];
                break;
            case 'published_date':
                return date("d F, Y H:i:s", strtotime($item[$column_name]));
                break;
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
      return sprintf(
        '<input type="checkbox" name="bulk-chk[]" value="%s" />', $item['id']
      );
    }


    /**
    *  Associative array of columns
    *
    * @return array
    */
    function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'doc_title'    => __( 'Document Title', 'market-downloads' ),
            'description' => __( 'Description', 'market-downloads' ),
            'published_date'    => __( 'Published Date', 'market-downloads' )
        ];

        return $columns;
    }

    /**
    * Columns to make sortable.
    *
    * @return array
    */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'doc_title' => array( 'doc_title', true ),
            'published_date' => array( 'published_date', true )
        );

        return $sortable_columns;
    }

    /**
    * Returns an associative array containing the bulk action
    *
    * @return array
    */
    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Trash'
        ];

        return $actions;
    }
       
    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'file_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = $this->MD_Model->total_files($_GET['post']);

        $this->set_pagination_args( [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );

        $this->items = $this->MD_Model->get_files( $_GET['post'], $per_page, $current_page );
        // wp_die(var_dump($this->items));
    }

    
    //Redirect after appying buck action(prevent already sent header error)
    function redirect() {
        wp_redirect( esc_url(menu_page_url('browse_files') ) );
        exit;
    }
}