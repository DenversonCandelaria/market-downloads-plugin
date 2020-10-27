<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MD_Logs_Table extends WP_List_Table {

    private $db;
    public $MD_Model;
    public $type = 'Market Reports';

    /** Class constructor */
    public function __construct($wpdb) {
        $this->db = $wpdb;

        parent::__construct( [
            'singular' => __( 'Log', 'market-downloads' ), //singular name of the listed records
            'plural'   => __( 'Logs', 'market-downloads' ), //plural name of the listed records
            'ajax'     => false //should this table support ajax?
        ] );

        require_once MD_PLUGIN_DIR.'models/class-md-market-downloads-model.php';
        $this->MD_Model = new MD_Market_Downloads_Model($wpdb);
    }

    /** Text displayed when no order data is available */
    public function no_items() {
        _e( 'No logs available.', 'market-downloads' );
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
            case 'post_id':
                $val = '&mdash;';
                $post_id = $item['post_id'];
                
                if($post_id){
                    $permalink = get_permalink($post_id);
                    $val = '<a href="'.$permalink.'" target="_blank">'.$item['post_id'].'</a>';
                }
                
                return $val;
                break;
            case 'file':
                $html = '';
                $filename = $item[$column_name];

                    $files = $this->MD_Model->get_log_files($item['id']);

                    $count = count($files);
                    
                    if($files){
                    $html .= '<div><a href="#" data-id='.$item['id'].' data-count="'.$count.'" data-act="'.$item['activity'].'" data-date="'.$item['date_published'].'" data-archive="'.$filename.'" data-postid="'.$item['post_id'].'" class="view-files-logs">View Files ('.$count.')</a></div>
                                <div id="view-files-'.$item['id'].'" class="view-files-logs-dialog">
                                <table class="striped">
                                    <thead>
                                        <tr><td>Filename</td>
                                            <td>Date Uploaded</td>
                                            <td>Target Date</td>
                                            <td>File Size</td>
                                        </tr>
                                    </thead>
                                    <tbody>';
                        foreach ($files as $key => $file) {
                            $html .= '<tr><td>'.$file['filename'].'</td>
                                        <td>'.$file['date_uploaded'].'</td>
                                        <td>'.$file['target_date'].'</td>
                                        <td>'.convert_filesize($file['file_size']).'</td>
                                    </tr>
                                  ';
                        }

                        $html .='</tbody></table></div>';
                    }

                return $html;
                
                break;
            case 'date_published':
                return date("d F, Y H:i:s", strtotime($item[$column_name]));
                break;
            case 'file_size':
                return convert_filesize($item[$column_name]);
                break;
            default:
                 return ($item[$column_name]) ? $item[$column_name] : '&mdash;';
        }
    }

    /**
    *  Associative array of columns
    *
    * @return array
    */
    function get_columns() {
        $columns = [
            'post_id'    => __( $this->type. ' ID', 'market-downloads' ),
            'activity' => __( 'Activity', 'market-downloads' ),
            'file'    => __( 'Action', 'market-downloads' ),
            'date_published'    => __( 'Date', 'market-downloads' ),
            'file_size'    => __( 'Total size', 'market-downloads' )
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
            'date_published' => array(   'date_published', true )
        );

        return $sortable_columns;
    }
       
    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();
     
        // $ipp = ($this->type == "Market Data") ? 'md_per_page' : 'mr_per_page';

        $per_page     = $this->get_items_per_page( 'mr_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items  = $this->MD_Model->total_logs($this->type);

        $this->set_pagination_args( [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );

        $this->items = $this->MD_Model->get_logs($this->type, $per_page, $current_page );
        // wp_die(var_dump($this->items));
    }

}