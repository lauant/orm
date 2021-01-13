<?php 

namespace Lauant\ORM\Models;

class ExampleModel extends BaseModel {

    public function __construct(){
        global $wpdb;
        $this->table  = "TABLE NAME INCLUDING wpdb->prefix";
        $this->fields = array(
            'string_example' => array(
                'type' => '%s',
            ),
            'number_example' => array(
                'type' => '%d',
            ),
            'float'          => array(
                'type' => '%f',
            ),
        );
    }
}
