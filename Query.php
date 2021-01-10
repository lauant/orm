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
    protected $where      = array();

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
        $sanitized_value = null;
        $data_type_array = null;

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

    private function build_where_clause(){
        $where_clause = 'WHERE TRUE';

        if ( $this->where ) {
            $where_clause .= ' AND ';
            foreach ( $this->where as $condition => $value ) {
                $clause        = "$condition = '$value' ";
                $where_clause .= $clause;
            }
        }

        return $where_clause;
    }

    public function get() {
        global $wpdb;
        $where_clause = $this->build_where_clause();
        return $wpdb->get_results( "SELECT * FROM $this->table $where_clause" );
    }

    public function update( $values = array() ) {
        global $wpdb;

        $query = $this->sanitize_fields( $this->fields );

        if ( $this->timestamps ) {
            $query['data']['updated_at'] = current_time( 'mysql' );
        }
 
        if ( $values ) {
            $wpdb->update(
                $this->table,
                $values,
                $this->where
            );
        } else {
            $wpdb->update(
                $this->table,
                $query['data'],
                $this->where,
                $query['format'],
            );
        }
    }

    public function delete() {
        global $wpdb;
        $id = filter_var( $_REQUEST['payload']['id'], FILTER_SANITIZE_NUMBER_INT );
        $wpdb->delete( $this->table, array( 'id' => $id ) ); // db call ok.
    }

    public function save(){
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

    public function exists(){
        global $wpdb;
        $where_clause = $this->build_where_clause();
        $exists       = $wpdb->get_results( "SELECT COUNT(id) FROM $this->table $where_clause LIMIT 1", ARRAY_A )[0]['COUNT(id)'];
        return $exists == '0' ? false : true;
    }

    public function where( $where_array ) {
        $this->where = $where_array;
        return $this;
    }
}
