<?php
/**
 * GLM Sessions Query 
 *
 * Main data processing model
 *
 * @category   Components
 * @package    GLM Sessions
 */
namespace GLM\Sessions;
use GLM\Sessions\Utility as U;
abstract class Query{
    protected $table;
    protected $fields;
    protected $timestamps = false;

    protected function get_model() {
        return isset( $_REQUEST['model'] ) ? filter_var( $_REQUEST['model'], FILTER_SANITIZE_STRING ) : '';
    }

    public function __get( $field ) {
        if ( isset( $this->fields[ $field ]['value'] ) ) {
            return $this->fields[ $field ]['value'];
        } else {
            return null;
        }
    }

    public function __isset( $field ) {
        return isset( $this->fields[ $field ]['value'] );
    }

    public function __set( $field, $value ) {
        $this->fields [ $field ]['value'] = $value;
    }

    protected function sanitize_fields( $fields ) {
        $data_pairs      = array();
        $sanitized_value = false;

        foreach ( $fields as $field_key => $field_data ) {
            if ( array_key_exists( 'value', $field_data ) && $field_data['value'] ) {
                $type              = $field_data['type'];
                $data_type_array[] = $type;

                if ( $type === '%s' ) {
                    $sanitized_value = wp_strip_all_tags( $field_data['value'] );
                } elseif ( $type === '%f' ) {
                    $sanitized_value = filter_var( $field_data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
                } elseif ( $type === '%d' ) {
                    $sanitized_value = filter_var( $field_data['value'], FILTER_SANITIZE_NUMBER_INT );
                }
                $data_pairs[ $field_key ] = $sanitized_value;
            }
        }

        return array(
            'data'   => $data_pairs,
            'format' => $data_type_array,
        );

    }

    protected function create_query( $operation ) {
        $payload    = $this->get_payload();
        $data_array = array();
        $sanitized  = $this->sanitize_fields( $payload );

        $data_pairs      = $sanitized['data'];
        $data_type_array = $sanitized['format'];
        array_push( $data_array, $data_pairs );

        if ( $operation === 'update' ) {

            $id = filter_var( $_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT );
            return array(
                'data'   => $data_array,
                'format' => $data_type_array,
                'where'  => array( 'id' => $id ),
            );
        } elseif ( $operation === 'create' ) {
            return (
                array(
                    'data'   => $data_array,
                    'format' => $data_type_array,
                )
            );
        }
    }

    protected function get_where_clause() {
        $where         = 'WHERE TRUE';
        $where_request = isset( $_REQUEST['where'] ) ? $_REQUEST['where'] : false;

        if ( $where_request ) {
            foreach ( $where_request as $column => $value ) {
                $where .= " AND $column = $value";
            }
        }
        $where .= ';';
        return $where;
    }

    protected function get_table_columns() {
        $columns = isset( $_REQUEST['columns'] ) ? filter_var( implode( ',', $_REQUEST['columns'] ), FILTER_SANITIZE_STRING ) : false;
        return $columns;
    }

    /**  Exposed Model Methods **/
    public function create( $operation ) {
        global $wpdb;
        $query = $this->create_query( $operation );
        $wpdb->insert( $this->table, $query['data'][0], $query['format'] ); // db call ok.

        if( $_REQUEST['entity'] === 'true' ) {
            $wpdb->insert(
                GLM_SESSIONS_TABLE,
                array(
                    'map'         => $_REQUEST['map'],
                    'entity_id'   => $wpdb->insert_id,
                    'entity_name' => $this->get_model(),
                ),
                array(
                    '%d',
                    '%d',
                    '%s',
                )
            ); // db call ok.
        }
        return $wpdb->insert_id;
    }

    public function get() {
        global $wpdb;
        $table_columns = $this->get_table_columns() ? $this->get_table_columns() : '*';
        $where         = $this->get_where_clause();
        $results       = $wpdb->get_results(
            "SELECT $table_columns FROM $this->table $where",
            'ARRAY_A'
        ); // db call ok.
        return $results;
    }

    public function update( $operation ) {
        global $wpdb;

        $query = $this->create_query( $operation );
        $wpdb->update( $this->table, $query['data'][0], $query['where'], $query['format'] ); // db call ok.
    }

    public function delete() {
        global $wpdb;
        $id = filter_var( $_REQUEST['payload']['id'], FILTER_SANITIZE_NUMBER_INT );
        $wpdb->delete( $this->table, array( 'id' => $id ) ); // db call ok.
    }

    public function save() {
        global $wpdb;

        $query = $this->sanitize_fields( $this->fields );

        if ( $this->timestamps ) {
            $query['data']['created_at'] = current_time( 'mysql' );
            $query['data']['updated_at'] = current_time( 'mysql' );
        }

        $wpdb->insert(
            $this->table,
            $query['data'],
            $query['format']
        );

        return $wpdb->insert_id;
    }

    public function update_or_create(){
        // use wpdb replace
    }
}
