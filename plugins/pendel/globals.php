<?php

//require_once ( '../../../wp-load.php' );
global $wpdb;

/**
 * Log an notice
 * @param type $message
 */
function log_notice($message) {
    trigger_error($message, E_USER_NOTICE);
}

/**
 * Log an error
 * @param type $message
 */
function log_error($message) {
    trigger_error($message, E_USER_ERROR);
}
