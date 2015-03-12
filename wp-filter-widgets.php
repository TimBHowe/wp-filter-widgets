<?php
/* *
 * Plugin Name: WordPress Filter Widgets
 * Plugin URI: http://www.hallme.com/
 * Description: Filter and alter the main query.
 * Version: 1.0
 * Author: Hall Internet Marketing
 * Author URI: http://www.hallme.com/
 * Author Email: cms.support@hallme.com
 * License: GPLv2 or later
 * Text Domain: wp_filter_widgets
 * Domain Path: /languages/
 * */

require_once( 'taxonomy-filter-widget.php' );

if( class_exists( 'acf' ) ) {
	//require_once( 'acf-filter-widget.php' );
}