<?php
/**
 * Adds TMD_Radio_Player.
 */
class TMD_Radio_Player extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'tmd_radio_player', // Base ID
            __('TMD Radio Player', 'text_domain'), // Name
            array( 'description' => __( 'Menampilkan radio player', 'text_domain' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {

        $title = apply_filters( 'widget-title', $instance['title'] );

        echo $args['before_widget'];

        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];

        $stream_sources = isset( $instance['stream_sources'] ) ? $instance['stream_sources'] : array();

        // Your code here

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'Radio Player', 'text_domain' );
        };

        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Judul:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>

        <?php
        $stream_sources = isset( $instance['stream_sources'] ) ? $instance['stream_sources'] : array();
        $stream_num = count($stream_sources);
        $stream_sources[$stream_num+1] = '';
        $stream_sources_html = array();
        $stream_counter = 0;

        foreach ( $stream_sources as $stream_source )
        {
            if ( isset($stream_source['title']) )
            {
                $stream_sources_html[] = sprintf(
                    '<p><input type="text" name="%1$s[%2$s][title]" value="%3$s" class="widefat sourc%2$s"><span class="remove-field button button-primary button-large">Hapus</span></p>',
                    $this->get_field_name( 'stream_sources' ),
                    $stream_counter,
                    esc_attr( $stream_source['title'] )
                );
            }
            $stream_counter += 1;
        }

        print 'Fields<br>' . join( $stream_sources_html );

        ?>

        <script type="text/javascript">
            var fieldname = <?php echo json_encode( $this->get_field_name('stream_sources') ) ?>;
            var fieldnum = <?php echo json_encode( $stream_counter-1 ) ?>;

            jQuery(function($) {
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

        <span id="<?php echo $this->get_field_id( 'field_clone' );?>">

        </span>

        <?php

        echo '<input class="button '.$this->get_field_id('add_field').' button-primary button-large" type="button" value="' . __( 'Tambah Audio', 'myvps' ) . '" id="add_field" />';
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        $instance['stream_sources'] = array();

        if ( isset( $new_instance['stream_sources'] ) )
        {
            foreach ( $new_instance['stream_sources'] as $stream_source )
            {
                if ( '' !== trim( $stream_source['title'] ) )
                    $instance['stream_sources'][] = $stream_source;
            }
        }

        return $instance;
    }

} // class TMD_Radio_Player

// register TMD_Radio_Player widget
function register_tmd_radio_player() {
    register_widget( 'TMD_Radio_Player' );
}
add_action( 'widgets_init', 'register_tmd_radio_player' );