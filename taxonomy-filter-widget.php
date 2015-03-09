<?php
/*
Plugin Name: Taxonomy Filter Widgets
Plugin URI: http://www.hallme.com/
Description: Create a custom drop-down filter based on taxonomies
Version: 1.0
Author: Hall Internet Marketing
Author URI: http://www.hallme.com/
Author Email: cms.support@hallme.com
*/

class main_query_taxonomy_filter_widget extends WP_Widget {

	function __construct() {

		add_action( 'pre_get_posts', array( $this, 'foo_modify_query_exclude_category' ) ); //TODO: need to update to work in the class

        // Register site styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );


		parent::__construct(
			// Base ID of your widget
			'taxonomy_filter_widget',

			// Widget name will appear in UI
			__('Taxonomy Filter Widget', 'taxonomy_widget_domain'),

			// Widget description
			array( 'description' => __( 'Filters Post by Taxonomies', 'taxonomy_widget_domain' ), )
		);
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {

		// Get the title set in the widget options
		$title = apply_filters( 'widget_title', $instance['title'] );

		// Get the taxonomy set in the widget options
		$taxonomy = $instance['taxonomy'];

		// Get the taxonomy object
		$taxonomy_obj = get_taxonomy($taxonomy);

		// Get the current term so it can be set as selected
		if( isset( $_GET["$taxonomy"] ) )
			$current_tax_filters = $_GET["$taxonomy"];

		// Get the current query strings
		$get_string = $_SERVER['QUERY_STRING'];
		parse_str( $get_string, $get_array );
		unset($get_array["$taxonomy"]);

		// Before and after widget arguments are defined by themes
		echo $args['before_widget'];

		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		// This is where you run the code and display the output
		$terms = get_terms( $taxonomy, array(
			'hide_empty' => 0,
			'orderby' => 'name',
		) );

		// Print the taxonomy items
		echo '<form id="taxonomy-filter-widget" method="get" action="//' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '">';

			echo '<select name="' . $taxonomy . '" onchange="this.form.submit()">';

				echo '<option value="0">All ' . $taxonomy_obj->labels->name . '</option>';

			foreach( $terms as $term ) {

				echo '<option value="' . $term->slug . '"' . ( $current_tax_filters == $term->slug ? " selected" : "" ) . '>' . $term->name . '</option>';
			}

			echo '</select>';

			foreach( $get_array as $key => $value ) {

				echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';

			}

		echo '</form>';

		echo $args['after_widget'];
	}

	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
			$taxonomy = $instance['taxonomy'];
		} else {
			$title = __( 'Taxonomy Filter', 'taxonomy_widget_domain' );
		}

		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:' ); ?></label>
			<select class="widefat" for="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo esc_attr( $this->get_field_name('taxonomy') ); ?>"><?php _e( 'Taxonomy:' ); ?>
				<?php
				$taxonomies = get_taxonomies ( array( 'public' => true), 'objects' );
				foreach ( $taxonomies as $key => $tax_obj ) {
					echo '<option ' . ( $instance['taxonomy'] == $key ? 'selected' : '' ) . ' value="' . $key . '">' . $tax_obj->labels->name . '</option>';
				}
				?>
			</select>
		</p>
	<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['taxonomy'] = ( ! empty( $new_instance['taxonomy'] ) ) ? strip_tags( $new_instance['taxonomy'] ) : '';
		return $instance;
	}

	// Alter the main query with the taxonomy filter from the widget
	function foo_modify_query_exclude_category( $query ) {

		// Get the current query strings
		$get_string = $_SERVER['QUERY_STRING'];

		// Stop if there isn't a query stiring
		if( !isset( $get_string ) || empty( $get_string ) ) {
			return;
		}

		// Parse the query strings into an array
		parse_str( $get_string, $get_array );

		// Get an array of the public taxonomies
		$taxonomies = get_taxonomies ( array( 'public' => true) );

		// Collect the taxonomies from the URL query string
		$got_taxonomies = array_intersect_key( $get_array, $taxonomies );

		// Stop if there is no taxonomy query strings
		if( !isset( $got_taxonomies ) || empty( $got_taxonomies ) ) {
			return;
		}

		// Set all the taxonomies for the args
		foreach( $got_taxonomies as $taxonomy => $term ) {

			// Skip if the term is empty
			if( !isset( $term ) || empty( $term ) ) {
				continue;
			}

			$tax_args[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => array( $term ),
			);
		}

		// Finish formatting the taxonomy args - http://codex.wordpress.org/Class_Reference/WP_Query#Taxonomy_Parameters
		$tax_args = array(
			'relation' => 'AND',
			$tax_args
		);

		// Alter the main query with new taxonomy args
		//if ( $query->is_main_query() ) {

			$query->set( 'tax_query', $tax_args );

			// FOR TESTING
			//$query_vars = $query->query_vars;
			//var_dump( $query_vars );
		//}
	}

} // Class main_query_taxonomy_filter_widget ends here

// Register and load the widget
function wpb_load_widget() {
	register_widget( 'main_query_taxonomy_filter_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );