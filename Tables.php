<?php //phpcs:ignore WordPress.Files.Filename.InvalidClassFileName
namespace GLM\Sessions;

use const GLM\Sessions\Defines\DB_VERSION;
use GLM\Sessions\TableBuilder;

/**
 * Class: Tables
 *
 * Setup for the Plugin tables.
 *
 * @see TableBuilder
 */
class Tables extends TableBuilder{

    /**
     * Class Constructor
     *
     * Build the tables variable for defining the database tables.
     * 'table_name' => 'table creation sql'
     */
    public function __construct(){

        $this->tables = array(
            'sessions_settings' =>
                'id INT NOT NULL AUTO_INCREMENT,
                opentok_key TINYTEXT NULL,
                opentok_secret TINYTEXT NULL,
                PRIMARY KEY  (id)',
            'sessions'          =>
                'id INT NOT NULL AUTO_INCREMENT,
                created_at TIMESTAMP NOT NULL,
                updated_at TIMESTAMP NOT NULL,
                session_key TINYTEXT NOT NULL,
                title TINYTEXT NOT NULL,
                slug TINYTEXT NOT NULL,
                PRIMARY KEY  (id)',
        );
 
        $this->glm_sessions_db_update_check();
    }
}

