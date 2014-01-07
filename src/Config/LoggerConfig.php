<?php
/**
 * Logging config
 */
$config = array();

/**
 * Defines
 */
define('LVL_EMERGENCY',             'EMERGENCY');
define('LVL_ALERT',                 'ALERT');
define('LVL_CRITICAL',              'CRITICAL');
define('LVL_ERROR',                 'ERROR');
define('LVL_WARNING',               'WARNING');
define('LVL_NOTICE',                'NOTICE');
define('LVL_INFO',                  'INFO');
define('LVL_DEBUG',                 'DEBUG');

/**
 * Enable disable/logging (0/1)
 */
$config['logging_enabled']      =   1;

/**
 * Log level
 * Available levels:
 * - emergency - 0
 * - alert - 1
 * - critical - 2
 * - error - 3
 * - warning - 4
 * - notice - 5
 * - info - 6
 * - debug - 7
 */
$config['logging_log_level']    =   2;

/**
 * Log directory
 */
$config['log_directory']        =   APPROOT . 'Logs/';
