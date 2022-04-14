<?php

class FrTable extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'user',
            'plural'   => 'users',
            'ajax'     => false,
        ) );

        $this->bulk_action_handler();

        add_screen_option( 'per_page', array(
            'label'   => 'Show on page',
            'default' => 20,
            'option'  => 'users_per_page',
        ) );

        $this->prepare_items();
    }

    public function column_default( $item, $colname ) {
        switch ( $colname ) {
            case 'id':
            case 'user_name':
            case 'user_email':
            case 'user_phone':
            case 'created':
                return $item[ $colname ];
            default:
                return $item[ $colname ];
        }
    }

    public function fetch_table_data() {
        global $wpdb;

        $wpdb_table = $wpdb->prefix . 'fr';
        $orderby    = ( isset( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'created';
        $order      = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'DESC';

        $user_query = "SELECT * FROM $wpdb_table ORDER BY $orderby $order";

        $query_results = $wpdb->get_results( $user_query, ARRAY_A );

        return $query_results;
    }

    public function get_columns() {
        return array(
            'id'         => 'ID',
            'user_name'  => 'Name',
            'user_email' => 'Email',
            'user_phone' => 'Phone',
            'created'    => 'Date',
        );
    }

    public function get_sortable_columns() {
        return array(
            'user_name'  => 'user_name',
            'user_email' => 'user_email',
            'user_phone' => 'user_phone',
            'created'    => 'created',
        );
    }

    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();
        $table_data            = $this->fetch_table_data();
        $this->items           = $table_data;

        $users_per_page = $this->get_items_per_page( 'users_per_page' );
        $table_page     = $this->get_pagenum();

        $this->items = array_slice( $table_data, ( ( $table_page - 1 ) * $users_per_page ), $users_per_page );

        $total_users = count( $table_data );
        $this->set_pagination_args( array(
            'total_items' => $total_users,
            'per_page'    => $users_per_page,
            'total_pages' => ceil( $total_users / $users_per_page )
        ) );
    }
}
