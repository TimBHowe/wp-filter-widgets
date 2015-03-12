<?php
/* *
 * Advanced Custom Fields Filter
 * This widget allows you to select a ACF to filter the main query.
 * */
class main_query_acf_filter_widget extends WP_Widget {

	function __construct() {

		// Alter the main query based on a acf value passed
		add_action( 'pre_get_posts', array( $this, 'filter_query_by_acf_value' ) );

		parent::__construct(
			// Base ID of your widget
			'acf-filter-widget',

			// Widget name will appear in UI
			__('ACF Filter Widget', 'wp_filter_widgets'),

			// Widget description
			array( 'description' => __( 'Filters Post by ACF Data', 'wp_filter_widgets' ), )
		);
	}

	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Meta Filter', 'wp_filter_widgets' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'acf-field' ); ?>"><?php _e( 'ACF Field:' ); ?></label>
			<select class="widefat" for="<?php echo $this->get_field_id( 'acf-field' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'acf-field' ) ); ?>"><?php _e( 'ACF Field:' ); ?>
			<?php
			// Get all the ACF field group obj
			$acf_fields_groups = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'acf-field-group' ) );

			// Loop through field groups as option groups
			foreach ( $acf_fields_groups as $acf_fields_group ) {

				echo ' <optgroup label="' . $acf_fields_group->post_title . '">';

				// Array of ACF fields in ACF field group
				$acf_fields = acf_get_fields_by_id( $acf_fields_group->ID );

				// Loop through field as options
				//TODO: Need to find a way to deal with complex fields
				foreach( $acf_fields as $acf_field ) {

					// Set the selected field
					if(  $instance['acf-field'] == $acf_field['key'] ) { $selected = ' selected '; } else { $selected = ''; }

					// Disable complex fields
					if( !in_array( $acf_field['type'], array( 'text', 'checkbox' ) ) ) { $disabled = ' disabled '; } else { $disabled = ''; }

					echo '<option ' . $selected . $disabled . ' value="' . $acf_field['key'] . '" data-field-type="' . $acf_field['type'] . '">' . $acf_field['label'] . '</option>'."\n";
				}

				echo '</optgroup>';
			}
			?>
			</select>
		</p>
			<legend>Input Type:</legend>
			<input type="radio" id="<?php echo $this->get_field_id( 'rating' ); ?>" name="<?php echo $this->get_field_name( 'rating' ); ?>" value=""><label for="<?php echo $this->get_field_id( 'rating' ); ?>"></label>
		<p>
		</p>

        <script type="text/javascript">
            var fieldname = <?php echo json_encode( $this->get_field_name('stream_sources') ) ?>;
            var fieldnum = <?php echo json_encode( $stream_counter-1 ) ?>;

            jQuery(function($) {
				$('.<?php echo $this->get_field_id( 'add_field' );?>').click(function() {

                var count = fieldnum;
                $('.<?php echo $this->get_field_id( 'add_field' );?>').click(function() {
                    $("#<?php echo $this->get_field_id( 'field_clone' );?>").append("<p><input type='text' name='"+fieldname+"["+(count+1)+"][title] value='' class='widefat sourc"+(count+1)+"'><span class='remove-field button button-primary button-large'>Hapus</span></p>");
                    count++;
                });

                $(".remove-field").live('click', function() {
                    $(this).parent().remove();
                });
            });
        </script>

	<?php
        //echo '<input class="button '.$this->get_field_id('add_field').' button-primary button-large" type="button" value="' . __( 'Tambah Audio', 'myvps' ) . '" id="add_field" />';

	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		// Title - Text
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		// ACF Field - Select
		if( $new_instance['acf-field'] != $old_instance['acf-field'] ) {
			$instance['acf-field'] = $new_instance['acf-field'];
		}

		// Option Type - Radio Button
		if( $new_instance['option-type'] != $old_instance['option-type'] ) {
			$instance['option-type'] = $new_instance['option-type'];
		}

		return $instance;
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {

		// Get the title set in the widget options
		$title = apply_filters( 'widget_title', $instance['title'] );

		// Before and after widget arguments are defined by themes
		echo $args['before_widget'];

		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

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

	// Alter the main query with the taxonomy filter from the widget
	function filter_query_by_acf_value( $query ) {

		// Get the current query strings
		$get_string = $_SERVER['QUERY_STRING'];

		// Stop if there isn't a query stiring
		if( !isset( $get_string ) || empty( $get_string ) ) {
			return;
		}

		// Parse the query strings into an array
		parse_str( $get_string, $get_array );

		// Alter the main query with new taxonomy args
		if ( $query->is_main_query() ) {

			//$query->set();

		}
	}

} // Class main_query_taxonomy_filter_widget ends here

// Register and load the taxonomy filter widget
function acf_filter_widget() {
	register_widget( 'main_query_acf_filter_widget' );
}
add_action( 'widgets_init', 'acf_filter_widget' );