<?php //phpcs:ignore WordPress.Files.Filename.InvalidClassFileName
namespace Lauant\ORM;

use Lauant\ORM\TableBuilder;

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
            'settings' =>
                'id INT NOT NULL AUTO_INCREMENT,
                api_key TINYTEXT NULL,
                api_secret TINYTEXT NULL,
                PRIMARY KEY  (id)',
            'sessions' =>
                'id INT NOT NULL AUTO_INCREMENT,
                created_at TIMESTAMP NOT NULL,
                updated_at TIMESTAMP NOT NULL,
                title TINYTEXT NOT NULL,
                user INT NOT NULL,
                slug TINYTEXT NOT NULL,
                PRIMARY KEY  (id)',
        );

        $this->db_update_check();
    }
}

