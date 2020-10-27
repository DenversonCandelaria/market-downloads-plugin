<?php
/**
 * Plugin Name:       IEMOP Public Website | Market Downloads
 * Description:       Manage all market downloads including market data and market reports
 * Version:           1.0.0
 * Author:            Denverson Candelaria
 * Text Domain:       market-downloads
 */


class IEMOP_Market_Downloads_init {
	public function load(){
		$this->pluginConstants();
		$this->includes();
		$this->inits();
	}

	public function pluginConstants() {
        // Plugin prefix
        if ( !defined('MD_PREFIX') ) {
            define( 'MD_PREFIX', 'MD' );
        }

        // Plugin Folder Path
        if ( !defined('MD_PLUGIN_DIR') ) {
            define( 'MD_PLUGIN_DIR', plugin_dir_path(__FILE__) );
        }

        // Plugin Admin URL
        if ( !defined('MD_PLUGIN_ADMIN_URL') ) {
            define( 'MD_PLUGIN_ADMIN_URL', MD_PLUGIN_DIR . 'admin/' );
        }

        // Plugin Public URL
        if ( !defined('MD_PLUGIN_PUBLIC_URL') ) {
            define( 'MD_PLUGIN_PUBLIC_URL', MD_PLUGIN_DIR . 'public/' );
        }

        // Plugin Root File
        if ( !defined( 'MD_PLUGIN_FILE' ) ) {
            define( 'MD_PLUGIN_FILE', __FILE__ );
        }

    }

 	public function includes() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once MD_PLUGIN_ADMIN_URL . 'controller/class-md-market-data.php';
        require_once MD_PLUGIN_ADMIN_URL . 'controller/class-md-market-reports.php';
        require_once MD_PLUGIN_ADMIN_URL . 'controller/class-md-settings.php';
        require_once MD_PLUGIN_ADMIN_URL . 'controller/class-md-logs.php';
        require_once MD_PLUGIN_ADMIN_URL . 'controller/class-md-summary-report.php';
        
        require_once MD_PLUGIN_PUBLIC_URL . 'controller/class-md-market-data-front.php';
        require_once MD_PLUGIN_PUBLIC_URL . 'controller/class-md-market-reports-front.php';
    }

    public function inits() {
        $MD_Market_Data = new MD_Market_Data();
        $MD_Market_Data->load();
       
        $MD_Market_Reports = new MD_Market_Reports();
        $MD_Market_Reports->load();

        $MD_Settings = new MD_Settings();
        $MD_Settings->load();

        $MD_Market_Data_Front = new MD_Market_Data_Front();
        $MD_Market_Data_Front->load();

        $MD_Market_Reports_Front = new MD_Market_Reports_Front();
        $MD_Market_Reports_Front->load();

        $MD_Logs = new MD_Logs();
        $MD_Logs->load();

        $MD_Summary_Report = new MD_Summary_Report();
        $MD_Summary_Report->load();
    }

}

function MD_init() {
    $MD = new IEMOP_Market_Downloads_init();
    $MD->load();
}
MD_init();