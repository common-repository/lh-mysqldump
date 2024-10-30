<?php
/**
 * Plugin Name: LH Mysqldump
 * Plugin URI: https://lhero.org/portfolio/lh-mysqldump/
 * Description: Dumps out your database and emails it to you
 * Version: 1.01
 * Text Domain: lh_mysqldump
 * Domain Path: /languages
 * Author: Peter Shaw
 * Author URI: https://shawfactor.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists('LH_Mysqldump_plugin')) {
    

class LH_Mysqldump_plugin {
    
    private static $instance;
        
    static function return_plugin_namespace(){

    return 'lh_mysqldump';

    }
    
    static function plugin_name(){
    
    return 'LH Mysqldump';
    
    
    }
    
    
    static function return_plugin_filename(){
        
        return plugin_basename( __FILE__ );
        
    }
    
    static function return_download_filename(){
        
        return sanitize_file_name( sprintf( self::return_plugin_namespace().'-download-%s', date( 'Y-m-d-U' ) ) );
        
    }
    
    static function write_log($log) {
        
        if (true === WP_DEBUG) {
            
            if (is_array($log) || is_object($log)) {
                
                error_log(plugin_basename( __FILE__ ).' - '.print_r($log, true));
                
            } else {
                
                error_log(plugin_basename( __FILE__ ).' - '.$log);
                
            }
            
        }
    }
    
    
    
    static function is_this_plugin_network_activated(){
    
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
            
        }
    
        if ( is_plugin_active_for_network( self::return_plugin_filename() ) ) {
            
            // Plugin is activated
            return true;
        
        } else  {
        
            //not active for network
            return false;
        
        
        }
    
    }

    static function get_site_roles_array(){
        
        if ( ! function_exists( 'get_editable_roles' ) ) {
            
            require_once ABSPATH . 'wp-admin/includes/user.php';
            
        }
    
        $all_roles = get_editable_roles();
        
        $return_roles = array();
        
        foreach ($all_roles as $key => $value){
            
            $return_roles[] = $key;
            
        }
        
        return $return_roles;
        
    }

    static function get_super_admin_ids_array(){
        
        $super_admins = get_super_admins();
        
        $super_ids = array();
        
        foreach ($super_admins as $the_super_admin){
            
            if ($the_super_user = get_user_by( 'login', $the_super_admin )){
            
        $super_ids[] = $the_super_user->ID;
            
            }
            
        }
        
        return $super_ids;
        
    }

    static function results_to_csv_string($results){
        
        $headings = array();
    
        $return = '';
    
        if (!empty($results['0']) && is_object($results['0'])){
        
            foreach ($results['0'] as $key => $value){
    
                $headings[] = $key;
            
            }
    
            $headings = array_unique($headings);
    
            $return = implode(",",$headings).'
            ';
    
        
            foreach ($results as $result){
        
                $return .= implode(",",array_values((array)$result)).'
                ';
            
            }   
    
        }
       
        return $return;    
        
    }


    static function return_download_button(){
        
        $text = '<div class="card">
        			<h2 class="title">'.__('Download your sites Database', self::return_plugin_namespace()).'</h2>';
            
        $text .= '<a href="'.wp_nonce_url( admin_url( 'tools.php?run=' ).self::return_plugin_filename(), self::return_plugin_namespace().'-generate_download', self::return_plugin_namespace().'-generate_download' ).'"><button class="button button-blue button-bordered">'.__('Generate Download', self::return_plugin_namespace()).'</button></a>';
        
        $text .= '<p>'.__('This will generate a zip file of your database tables.', self::return_plugin_namespace()).'</p>';
        
        $text .= '</div>';
        
        return $text;
        
    }

    static function generate_file_2($filename) {
    
        if (!class_exists('MySQLDump')) {
            
            require_once('includes/MySQLDump.php');
        
        }

        global $table_prefix;

        $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $tables = array();
        $result = $db->query("SHOW TABLES like '".$table_prefix."%'");
        
        
        while($row = $result->fetch_row()){
            
            $tables[] = $row[0];
            
        }
        
        $dump = new MySQLDump($db);

        $zip = new ZipArchive();

        $temp_file = tempnam(sys_get_temp_dir(), $filename).'.zip';

        if ($zip->open($temp_file, ZIPARCHIVE::CREATE)!==TRUE) {
       
            exit("cannot open <$filename>\n");
    
        }

        $handle = fopen('php://output', 'wb');


        
        foreach($tables as $table){
                
            ob_start();
                
            $dump->dumpTable($handle, $table);
                
            $return_string = ob_get_contents();
            ob_end_clean();
                
            $zip->addFromString($table.".sql", $return_string);
                
        }  
            
            
        if (!is_main_site()){
    
            global $wpdb;
    
            $the_roles = self::get_site_roles_array();

            $super_admins = self::get_super_admin_ids_array();

            $all_site_users = get_users( array( 'role__in' => $the_roles, 'exclude'  => $super_admins ));

            $all_site_user_ids = array();

            foreach($all_site_users as $site_user){
                
                $all_site_user_ids[] = $site_user->ID;    
                
            }

            $sql = "SELECT * FROM ".$wpdb->users." WHERE ID IN ('".implode("','", array_map('intval', $all_site_user_ids))."')";

            $results = $wpdb->get_results($sql);

            $csv_string = self::results_to_csv_string($results);

            $zip->addFromString($wpdb->users.".csv", $csv_string);

            $sql = "SELECT * FROM ".$wpdb->usermeta." WHERE user_id IN ('".implode("','", array_map('intval', $all_site_user_ids))."')";

            $results = $wpdb->get_results($sql);

            $csv_string = self::results_to_csv_string($results);

            $zip->addFromString($wpdb->usermeta.".csv", $csv_string);
    
        }
            
            
        $zip->close();

        return $temp_file;
    
    }


    static function generate_download() {
    
        $filename   = self::return_download_filename();
        
        $temp_file = self::generate_file_2($filename);
        
        
        // Send the report.
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream");
        header( "Content-Disposition: attachment; filename={$filename}.zip" );
        header("Content-Transfer-Encoding: binary");
        
        clearstatcache();
        header("Content-Length: ".filesize($temp_file));
        // output the file
        readfile($temp_file);
        	
        exit;
        
    }

    static function setup_crons() {
        
        
        wp_clear_scheduled_hook( 'lh_mysqldump_run' ); 
        
        if (is_main_site()){
            
            self::write_log("cron added for the following site ".get_current_blog_id());
            
            wp_schedule_event( time() + wp_rand( 0, 3600 ), 'weekly', 'lh_mysqldump_run' );
            
        }
        
    }

    static function remove_crons(){
        
        wp_clear_scheduled_hook( 'lh_mysqldump_run' ); 
        
    }
    

    
    public function maybe_generate_download() {
    
	    if ( !current_user_can('manage_options')) {
	    
            return;
            
        }
	
        if ( empty( $_GET[self::return_plugin_namespace().'-generate_download'] ) or !wp_verify_nonce($_GET[self::return_plugin_namespace().'-generate_download'], self::return_plugin_namespace().'-generate_download')) {
	    
            return;

	    }


        self::generate_download(); exit;
    
    
    }




    public function run_processes(){
        
        if (is_multisite() && is_main_site()){
            
            $args = array('number' => 500, 'fields' => 'ids');
            $sites = get_sites($args);
            
            foreach ($sites as $blog_id) {
            
                $args = array( $blog_id);
                wp_schedule_single_event( time() + wp_rand( 0, 3600 ), self::return_plugin_namespace().'_do_single_site', $args);
    
            }
            
        } else {
        
            
            $recipients = apply_filters('lh_mysqldump_email_recipients', get_bloginfo('admin_email'));
            
            $title = apply_filters('lh_mysqldump_email_title', get_bloginfo('name').' LH Mysqldump backup '.date( 'Y-m-d-U' ));
            
            $message = apply_filters('lh_mysqldump_email_message', 'This is a backup of your database data, generated at '.date( 'Y-m-d-U' ).' by the lh_mysqldump plugin');
            
            $temp_file = self::generate_file_2( self::return_download_filename());
            
            $test = wp_mail( $recipients, $title, $message, '', $temp_file);
        
        }
    
    
    }
    
    public function do_single_site( $arg_1_is_site_id ) {
        
        wp_clear_scheduled_hook( self::return_plugin_namespace().'_do_single_site' ,  array($arg_1_is_site_id));
        
        self::write_log('the site id is '.$arg_1_is_site_id);
        
        switch_to_blog($arg_1_is_site_id);
        
        $recipients = apply_filters('lh_mysqldump_email_recipients', get_bloginfo('admin_email'));
        
        self::write_log($recipients);
            
        $title = apply_filters('lh_mysqldump_email_title', get_bloginfo('name').' LH Mysqldump backup '.date( 'Y-m-d-U' ));
            
        $message = apply_filters('lh_mysqldump_email_message', 'This is a backup of your database data, generated at '.date( 'Y-m-d-U' ).' by the lh_mysqldump plugin');
            
        $temp_file = self::generate_file_2( self::return_download_filename());
        
        restore_current_blog();
        
        $test = wp_mail( $recipients, $title, $message, '', $temp_file);
        
        
    }




    public function add_download_button(){
        
        echo self::return_download_button();
        
    }


    public function plugin_init(){
    
        //load translations
        load_plugin_textdomain( self::return_plugin_namespace(), false, basename( dirname( __FILE__ ) ) . '/languages' );
    
        //generate the export download  
        add_action( 'admin_init', array($this,'maybe_generate_download'), 10000);
	    
        //to attach processes to the ongoing cron job
        add_action( 'lh_mysqldump_run', array($this,'run_processes'));
        
        //to attach processes to the one off cron jobs
        add_action( self::return_plugin_namespace().'_do_single_site', array($this,'do_single_site'),10,1);
        
        //add a button on the tools screen
        add_action( 'tool_box', array($this,'add_download_button'));
           
    }
        
        
    /**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
     
    public static function get_instance(){
        
        if (null === self::$instance) {
            
            self::$instance = new self();
            
        }
 
        return self::$instance;
        
    }
    
    static function on_activate($network_wide) {
        
        if (is_multisite() && !$network_wide){
            
            wp_die( __( 'On Multisite this plugin must be network activated', self::return_plugin_namespace()) );
            
        }
    
    
        if ( is_multisite() && $network_wide ) { 
            
            $args = array('number' => 500, 'fields' => 'ids');
            $sites = get_sites($args);
        
        
            foreach ($sites as $blog_id) {
                        
                switch_to_blog($blog_id);
                self::setup_crons();
                restore_current_blog();
                
            }
            
        } else {
            
            self::setup_crons();
            
        }
    
    }


    static function on_deactivate($network_wide) {
    
        if ( is_multisite() && $network_wide ) { 

            $args = array('number' => 500, 'fields' => 'ids');
        
            $sites = get_sites($args);
    
            foreach ($sites as $blog_id) {
                
                switch_to_blog($blog_id);
                self::remove_crons();
                restore_current_blog();
            
            } 

        } else {

            self::remove_crons();

        }
    
    }


    
    
    /**
	* Constructor
	*/
	public function __construct() {
	   
        //run our hooks on plugins loaded to as we may need checks       
        add_action( 'plugins_loaded', array($this,'plugin_init'));
	    
	}
    
}

$lh_mysqldump_instance = LH_Mysqldump_plugin::get_instance();
register_activation_hook(__FILE__, array('LH_Mysqldump_plugin','on_activate') );
register_deactivation_hook( __FILE__, array( 'LH_Mysqldump_plugin', 'on_deactivate' ) );

}

?>