<?php

function instafeed_widget_init() {
	register_widget( 'InstaFeed_Widget' );
}
add_action( 'widgets_init', 'instafeed_widget_init' );


class InstaFeed_Widget extends WP_Widget {

	function __construct() {

		parent::__construct(
			'instafeed_widget', 
			__('Instafeed Widget', 'instafeed'), 
			array( 'description' => __( 'Stream of photos from Instagram', 'instafeed' ), ) 
		);
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );


		if ( isset( $args['before_widget'] ) ) {
			echo $args['before_widget'];
		}

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( ! isset( $instance['instafeed_term'] ) || ! strlen( $instance['instafeed_term'] ) ) {
			echo sprintf( '<p>%s</p>', __( 'Please configure the WordPress InstaFeed widget', 'instafeed' ) );
		}

		if ( isset( $args['after_widget'] ) ) {
			echo $args['after_widget'];
		}

	}

	public function form( $instance ) {

		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'Instagram', 'instafeed' );
		$instafeed_term = isset( $instance[ 'instafeed_term' ] ) ? $instance[ 'instafeed_term' ] : '';
		$num_entries = isset( $instance[ 'num_entries' ] ) ? intval ( $instance[ 'num_entries' ] ) : '5';
		if ( $num_entries < 1 ) {
			$num_entries = 1;
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'instafeed_term' ); ?>"><?php _e( 'Username or tag:', 'instafeed' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'instafeed_term' ); ?>" name="<?php echo $this->get_field_name( 'instafeed_term' ); ?>" type="text" value="<?php echo esc_attr( $instafeed_term ); ?>" />
			<br><span class="desc"><?php _e( 'Input "username" or "#tag"', 'instafeed' ); ?></span>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'num_entries' ); ?>"><?php _e( 'Number of entries to show:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'num_entries' ); ?>" name="<?php echo $this->get_field_name( 'num_entries' ); ?>" min="1" type="number" value="<?php echo esc_attr( $num_entries ); ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['instafeed_term'] = ( ! empty( $new_instance['instafeed_term'] ) ) ? strip_tags( $new_instance['instafeed_term'] ) : '';
		if ( '@' == substr( $instance['instafeed_term'], 0, 1 ) ) {
			$instance['instafeed_term'] = substr( $instance['instafeed_term'], 1 );
		}
		$instance['num_entries'] = ( ! empty( $new_instance['num_entries'] ) ) ? intval( $new_instance['num_entries'] ) : '1';
		if ( intval( $instance['num_entries'] ) < 1 ) {
			$instance['num_entries'] = 1;
		}
		return $instance;
	}

}
