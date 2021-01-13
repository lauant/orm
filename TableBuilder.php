<?php //phpcs:ignore WordPress.Files.Filename.InvalidClassFileName
namespace Lauant\ORM;

// DB_VERSION and DB_PREFIX can be sourced from anywhere in your plugin
use Lauant\ORM\Models\Setting;

abstract class TableBuilder{

    protected $tables = array();

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
            )" . $this->set_charset();

            dbDelta( $sql );
        }
    }

    private function set_charset(){
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

    public function db_update_check() {
        if ( ! get_option( 'lauant_orm_db_version' ) ) {
            add_option( 'lauant_orm_db_version', 'DB_VERSION VALUE');
            $this->install_tables();
        } elseif ( get_site_option( 'lauant_orm_db_version' ) != 'DB_VERSION VALUE' ) {
            $this->install_tables();
            update_option( 'lauant_orm_db_version', 'DB_VERSION VALUE' );
        }
    }
}
