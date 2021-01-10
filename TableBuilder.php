<?php //phpcs:ignore WordPress.Files.Filename.InvalidClassFileName
namespace GLM\Sessions;

use const GLM\Sessions\Defines\DB_PREFIX;
use const GLM\Sessions\Defines\DB_VERSION;
use const GLM\Sessions\Defines\SETTINGS_TABLE;
use GLM\Sessions\Models\Setting;

class TableBuilder{

    protected $tables = array();

    public function __construct(){

    }

    protected function setup_defaults( $table, $values, $format ) {
        global $wpdb;

        $wpdb->insert(
            $table,
            $values,
            $format
        );
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
        if ( ! get_option( 'glm_sessions_db_version' ) ) {
            add_option( 'glm_sessions_db_version', DB_VERSION );
            $this->glm_sessions_install_tables();
            $this->setup_defaults( SETTINGS_TABLE, array( 'opentok_key' => 'api_key', 'opentok_secret' => 'api_secret' ), array( '%s', '%s' ) );
        } elseif ( get_site_option( 'glm_sessions_db_version' ) != DB_VERSION ) {
            $this->glm_sessions_install_tables();
            update_option( 'glm_sessions_db_version', DB_VERSION );
        }
    }

    public function glm_sessions_delete_table() {
        // remove tables / database
    }
}
