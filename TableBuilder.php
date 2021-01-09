<?php //phpcs:ignore WordPress.Files.Filename.InvalidClassFileName
namespace GLM\Sessions;

use const GLM\Sessions\Defines\DB_PREFIX;
use const GLM\Sessions\Defines\DB_VERSION;

class TableBuilder{

    protected $tables = array();

    public function __construct(){

    }

    public function glm_sessions_install_tables(){
     
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ( $this->tables as $key => $table ) {
            $sql = 'CREATE TABLE ' . DB_PREFIX . "$key (
                $table
            )" . $this->glm_sessions_set_charset();

            dbDelta( $sql );
        }
    }

    private function glm_sessions_set_charset(){
		global $wpdb;
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }

		if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }

		return $charset_collate;
    }

    public function glm_sessions_db_update_check() {

        if ( ! get_site_option( 'glm_sessions_db_version' ) ) {
            print_r( update_option( 'glm_sessions_db_version', DB_VERSION ) );
            $this->glm_sessions_install_tables();
        } elseif ( get_site_option( 'glm_sessions_db_version' ) != DB_VERSION ) {
            $this->glm_sessions_install_tables();
         
            update_option( 'glm_sessions_db_version', DB_VERSION );
        }
    }

    public function glm_sessions_delete_table() {
        // remove tables / database
    }
}
