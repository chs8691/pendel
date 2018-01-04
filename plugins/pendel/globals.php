<?php

//require_once ( '../../../wp-load.php' );
global $wpdb;

/**
 * Log an notice.
 * TODO trigger_error Scheint nicht zu funktionieren
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
    error_log($message, E_USER_ERROR);
}
