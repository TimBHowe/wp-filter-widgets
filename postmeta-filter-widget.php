<?php
/* *
 * Taxonomy Filter
 * This widget allows you to select a taxonomy to filter the main query.
 * */
class main_query_postmeta_filter_widget extends WP_Widget {

	function __construct() {

		// Alter the main query based on a postmeta value passed
		add_action( 'pre_get_posts', array( $this, 'filter_query_by_postmeta_value' ) );

		parent::__construct(
			// Base ID of your widget
			'postmeta-filter-widget',

			// Widget name will appear in UI
			__('Post Meta Filter Widget', 'wp_filter_widgets'),

			// Widget description
			array( 'description' => __( 'Filters Post by a selected Post Meta values', 'wp_filter_widgets' ), )
		);
	}

	// Widget Backend
	public function form( $instance ) {

		// Get widget instant settings
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Post Meta Filter', 'wp_filter_widgets' );
		}

		$postmeta_key = $instance['postmeta'];

		$post_types = get_post_types( array( 'public' => true, 'exclude_from_search' => false ) );

		var_dump()

		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type:' ); ?></label>
			<select class="widefat" for="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo esc_attr( $this->get_field_name('post_type') ); ?>">
				<?php // Collect and loop through the post type options
				$post_types = get_post_types( array( 'public' => true, 'exclude_from_search' => false ) );
				foreach ( $post_types as $post_type ) {
					echo '<option ' . ( $instance['post_type'] == $post_type ? 'selected' : '' ) . ' value="' . $post_type . '">' . $post_type . '</option>';
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
		echo '<form id="' . $arg['widget_id'] . '-form" method="get" action="//' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '">';

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

} // END CLASS main_query_postmeta_filter_widget

// Register and load the taxonomy filter widget
function postmeta_filter_widget() {
	register_widget( 'main_query_postmeta_filter_widget' );
}
add_action( 'widgets_init', 'postmeta_filter_widget' );