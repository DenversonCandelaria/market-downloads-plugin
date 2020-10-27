<?php
/**
 * Model for Market Downloads
 */
class MD_Market_Downloads_Model {

	private $db;

    /**
     * Class Constructor
     * @param object $wpdb WordPress wpdb Class
     */
	public function __construct($wpdb) {
		$this->db = $wpdb;
	}

    /**
    * Insert a market report file to the database
    * @param array $data Contains arrays of file data
    * @return bool Whether the insertion of data is successfull(true) or not(false)
    */
	public function insert_file($data){
		
		$query = "INSERT INTO iemoppwdb_market_reports (mr_id, mr_slug, filename, doc_title, description, published_date, status) VALUES".implode(",", $data);
		$query = $this->db->prepare($query);
		$rslt = $this->db->query($query);

		return $rslt;

	}

    /**
     * Get a single market reports file data
     * @param array $id ID of the market report file
     * @return array Contians the data of file
     */
	public function get_file($id){
		$query = "SELECT * FROM iemoppwdb_market_reports WHERE id=%d";
		$query = $this->db->prepare($query, $id);
		$rslt = $this->db->get_row($query, ARRAY_A);

		return $rslt;
	}

    /**
     * Get all market reports files for a tabular format
     * @param int $post_id Market Reports ID
     * @param int $per_page Number of files to retrieve per page
     * @param int $page_number Current page number
     * @return array Collection of files data
     */
	public function get_files($post_id, $per_page = 10, $page_number = 1){
        $post_slug = get_post_field( 'post_name', $post_id );
	 	$orderby = 'published_date';
        $order = 'DESC';
        $offset = ( $page_number - 1 ) * $per_page;

        if ( ! empty( $_REQUEST['orderby'] ) ) {
        	$orderby = $_REQUEST['orderby'];
        	$order = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC';
        }

        $query = "SELECT * FROM iemoppwdb_market_reports WHERE mr_id=%d AND mr_slug=%s AND status=%d ORDER BY $orderby $order LIMIT %d OFFSET %d";
       

        $query = $this->db->prepare($query, $post_id, $post_slug, 1, $per_page, $offset);
        $rslt = $this->db->get_results( $query, ARRAY_A );
        // wp_die($query);
        return $rslt;
	}

    /**
     * Get all market reports files that will display in front side
     * @param int $post_id Market Reports ID
     * @param int $filters Contains filters
     * @return array Collection of market reports files
     */
    public function get_files_front($post_id="", $filters=array()){
        $post_slug = get_post_field( 'post_name', $post_id );

        $query = "SELECT * FROM iemoppwdb_market_reports mr INNER JOIN iemoppwdb_posts p ON mr.mr_id = p.ID";
        $where = " WHERE mr.status=%d AND mr.published_date<=%s AND p.post_status=%s";

        $where .= ($post_id) ? " AND mr.mr_id=".$post_id." AND mr.mr_slug='".$post_slug."'" : '';
        
        if(isset($filters['exclude'])){
            $where .= " AND mr.id NOT IN(".implode(",", $filters['exclude']).")";
        }

        if(isset($filters['datefilter']) && $_POST['datefilter'] != ''){
            $start_date = $filters['datefilter']['start'];
            $end_date = $filters['datefilter']['end'];
            $where .= " AND (mr.published_date>='".$start_date."' AND mr.published_date<='".$end_date."')";
        }

        $order = " ORDER BY mr.published_date DESC";
        if(isset($filters['sort']) && $filters['sort'] != ''){
            $order = " ORDER BY ".$filters['sort']; 
        }

        $limit = '';
        if(isset($filters['limit'])){
            $limit = " LIMIT ".$filters['limit'];
        }

        $query .= $where.$order.$limit;

        $query = $this->db->prepare($query, 1, current_time("Y-m-d H:i:s"), 'publish');
        $rslt = $this->db->get_results($query, ARRAY_A);

        return $rslt;
    }

    /**
     * Update Market reports file
     * @param int $id Market Reports file ID
     * @param array $data Contains new data
     * @return bool Whether it success (true) or not (false)
     */
 	public function update_file( $id, $data ) {

        $query = "UPDATE iemoppwdb_market_reports SET ".implode(",", $data)." WHERE id=%d";
        $query = $this->db->prepare($query, $id);
        $rslt = $this->db->query($query);

        return $rslt;
    }

    /**
     * Delete market report files
     * @param array $ids Contains all file ids
     * @return bool Whether it success (true) or not (false)
     */
	public function delete_files( $ids ) {

		$query = "DELETE FROM iemoppwdb_market_reports WHERE id IN (".implode(",", $ids).")";
       	$query = $this->db->prepare($query);
       	$rslt = $this->db->query($query);

       	return $rslt;
    }

    /**
     * Count the total published files of the given market ID
     * @param int $mr_id Market Report ID
     * @return int Total number of files
     */
 	public function total_files($mr_id) {
        $mr_slug = get_post_field( 'post_name', $mr_id );

        $query = "SELECT COUNT(*) FROM iemoppwdb_market_reports WHERE mr_id=%d AND mr_slug=%s AND status=%d";
        $query = $this->db->prepare($query, $mr_id, $mr_slug, 1);
        $rslt = $this->db->get_var( $query );
        return $rslt;

    }

    /**
     * Update the field of market report slug where the file is uploaded
     * @param int $mr_id   Market Report ID
     * @param int $mr_slug Market Report slug
     * @return bool Whether it success (true) or not (false)
     */
    public function update_file_mr_slug($mr_id, $mr_slug){
        $data = array('mr_slug'=>$mr_slug);
        $where = array('mr_id'=>$mr_id);
        $rslt = $this->db->update('iemoppwdb_market_reports', $data, $where);

        return $rslt;
    }

    /**
     * Count the total logs of market data or market reports
     * @param string $type Whether its Market Data or Market Reports
     * @return int Total count of market data or market report logs
     */
    public function total_logs($type) {

        $query = "SELECT COUNT(*) FROM iemoppwdb_md_logs WHERE md_type=%s";
        $query = $this->db->prepare($query, $type);
        $rslt = $this->db->get_var( $query );

        return $rslt;
    }

    /**
     * Get all log of Market data or Market Reports for tabular format
     * @param string $type        Whether its Market Data or Market Reports
     * @param int    $per_page    Number of logs to retrieve
     * @param int    $page_number Current page number
     * @return bool Whether it success (true) or not (false)
     */
    public function get_logs($type, $per_page = 10, $page_number = 1){
        $orderby = 'id';
        $order = 'DESC';
        $offset = ( $page_number - 1 ) * $per_page;

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $orderby = $_REQUEST['orderby'];
            $order = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC';
        }

        $query = "SELECT * FROM iemoppwdb_md_logs WHERE md_type=%s ORDER BY $orderby $order LIMIT %d OFFSET %d";
       
        $query = $this->db->prepare($query, $type, $per_page, $offset);
        $rslt = $this->db->get_results( $query, ARRAY_A );
        // wp_die($query);
        return $rslt;
    }

    /**
     * Get all files involved in an specific log
     * @param int $log_id Market data or Market report log ID
     * @return array Collection of files
     */
    public function get_log_files($log_id){

        $query = "SELECT * FROM iemoppwdb_md_logs_files WHERE log_id=%s ORDER BY id ASC";
       
        $query = $this->db->prepare($query, $log_id);
        $rslt = $this->db->get_results( $query, ARRAY_A );

        return $rslt;
    }

    /**
     * Insert market data or market reports logs
     * @param array $data Contains array of log data
     * @return int|bool Contains the inserted ID(succes) or false (failed) 
     */
    public function insert_log($data){
        
        $query = 'INSERT INTO iemoppwdb_md_logs (post_id, activity, file, md_type, date_published, file_size) VALUES'.implode(",", $data);
        $query = $this->db->prepare($query);
        $rslt = $this->db->query($query);

        return ($rslt) ? $this->db->insert_id : $rslt;

    }

    /**
     * Insert market data files or market reports files that involved in an activity(log)
     * @param int   $log_id    The ID of log
     * @param array $log_files Collection of log files data
     * @return bool Whether it success (true) or not (false)
     */
    public function insert_log_files($log_id, $log_files){
        $data = array();
        foreach ($log_files as $key => $log_file) {
            $upload_path = (isset($log_file['upload_path'])) ? $log_file['upload_path'] : '';
            $data[] = "(".$log_id.", '".$log_file['filename']."', '".$log_file['date_uploaded']."', '".$log_file['target_date']."', ".$log_file['file_size'].", '".$upload_path."')";
        }

        $query = 'INSERT INTO iemoppwdb_md_logs_files (log_id, filename, date_uploaded, target_date, file_size, upload_path) VALUES'.implode(",", $data);
        $query = $this->db->prepare($query);
        $rslt = $this->db->query($query);

        return $rslt;
    }

    /**
     * Check if the file were already inserted to the database
     * @param string   $upload_path   The file to check
     * @param string   $date_uploaded Date when the file is uploaded
     * @return array Contains the existing file or empty if file doesn't exist
     */
    public function get_existing_market_data_file($upload_path, $date_uploaded){
        $query = "SELECT * FROM iemoppwdb_md_logs_files WHERE upload_path=%s AND date_uploaded=%s ORDER BY id ASC";
       
        $query = $this->db->prepare($query, $upload_path, $date_uploaded);
        $rslt = $this->db->get_results( $query, ARRAY_A );

        return $rslt;
    }

    /**
     * Get all files within the given range of date
     * @param int    $post_id   Market Data ID
     * @param string $date_from Starting date
     * @param string $date_to   End date
     * @return array Contains all files uploaded
     */
    public function get_summary_report_files($post_id, $date_from, $date_to){
        $query = "SELECT DATE(mf.target_date) AS trading_date, MAX(mf.date_uploaded) AS max
                  FROM iemoppwdb_md_logs_files mf
                  INNER JOIN iemoppwdb_md_logs ml
                  ON ml.id=mf.log_id
                  WHERE ml.md_type='Market Data' AND 
                        DATE(mf.target_date)>=%s AND DATE(mf.target_date)<=%s AND
                        mf.upload_path != '' AND
                        ml.post_id = %d
                  GROUP BY trading_date
                  ORDER BY mf.target_date ASC";
       
        $query = $this->db->prepare($query, $date_from, $date_to, $post_id);
        $rslt = $this->db->get_results( $query, ARRAY_A );

        return $rslt;
    }

    /**
     * Count published or uploaded files on the given $date 
     * @param int    $post_id Market Data ID
     * @param string $date    Date to check the number uploaded files
     * @return int Total number of files
     */
    public function count_published_files($post_id, $date){
        $inner_query = "SELECT mf.id
                  FROM iemoppwdb_md_logs_files mf
                  INNER JOIN iemoppwdb_md_logs ml
                        ON ml.id = mf.log_id
                  WHERE ml.md_type = 'Market Data' AND
                        DATE(mf.target_date) = %s AND
                        mf.upload_path != '' AND
                        ml.post_id = %d
                  GROUP BY mf.upload_path";

        $query = "SELECT COUNT(*) as total FROM (".$inner_query.") t";

        $query = $this->db->prepare($query, $date, $post_id);
        $rslt = $this->db->get_var( $query );

        return $rslt;
    }

}