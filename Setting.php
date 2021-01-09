<?php 

use GLM\Sessions\Defines\SETTINGS_TABLE;

class Setting extends Query{

    protected $primary_key      = 'id';
    protected $primary_key_type = '%d';

    public function __construct(){
        $this->table  = SETINGS_TABLE;
        $this->fields = array(
            'opentok_key'    => array(
                'type' => '%s',
            ),
            'opentok_secret' => array(
                'type' => '%s',
            ),
        );
    }
}
