<?php
/*
Plugin Name: Property Filter Widgets
Plugin URI: http://www.hallme.com/
Description: Create a custom drop-down filter based on taxonomies
Version: 1.0
Author: Hall Internet Marketing
Author URI: http://www.hallme.com/
Author Email: cms.support@hallme.com
*/
//Custom Filter Widget
class wpb_widget extends WP_Widget {

	function __construct() {

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
		global $wp_query, $wp;

		$title = apply_filters( 'widget_title', $instance['title'] );

		$query_tax = $wp_query->query_vars['taxonomy']; // returns string of current taxonomy used on page

		$query_vars = $wp->query_vars; // returns array of query strings

		$taxonomy = $instance['taxonomy'];

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];

		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		// This is where you run the code and display the output
		$terms = get_terms( $taxonomy, array(
			'hide_empty' => 0,
			'orderby' => 'name',
		) );

		// Print the taxonomy items
		echo '<select>';
		foreach( $terms as $term ) {

			// remove current taxonomy
			if( array_key_exists( $taxonomy, $query_vars ) ){
				unset( $query_vars[$taxonomy] );
			}

//Working Here
			$built_query = build_query( $query_vars );

			$term_url = get_term_link( $term );

			$link = add_query_arg( $built_query, '', $term_url );

			echo '<option></option>';
		}
		echo '</select>';

		echo $args['after_widget'];
	}

	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
			$taxonomy = $instance[ 'taxonomy' ];
		} else {
			$title = __( 'New title', 'taxonomy_widget_domain' );
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
				$taxonomies = get_object_taxonomies( 'product' );

				foreach ($taxonomies as $tax) {
					echo '<option value="'.$tax.'" ';
					if (isset($instance['taxonomy']) && $instance['taxonomy']==$tax) :
						echo 'selected="selected"';
					endif;
					echo '>'.$tax.'</option>';
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
} // Class wpb_widget ends here

// Register and load the widget
function wpb_load_widget() {
	register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );
