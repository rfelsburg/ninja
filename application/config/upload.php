<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * This path is relative to your index file. Absolute paths are also supported.
 * Should end with a slash (/) and be writable by webserver (apache)
 */
$config['directory'] = DOCROOT.'upload/';

/**
 * Enable or disable directory creation.
 */
$config['create_directories'] = FALSE;

/**
 * Remove spaces from uploaded filenames.
 */
$config['remove_spaces'] = TRUE;
