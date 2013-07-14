<?php

class wpdbshell {
	function get_results($query) {
		global $wpdb; 
		return call_user_func_array( array( $wpdb, 'get_results' ), $query );
	}

	function get_var($query) {
		global $wpdb; 
		return call_user_func_array( array( $wpdb, 'get_var' ), $query );
	}

	function get_col($query) {
		global $wpdb; 
		return call_user_func_array( array( $wpdb, 'get_col' ), $query );
	}

	function get_row($query) {
		global $wpdb; 
		return call_user_func_array( array( $wpdb, 'get_row' ), $query );
	}
}

$modules[] = new wpdbshell();
