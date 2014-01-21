<?php
/*
Plugin Name: WordPress Instafeed
Plugin URI: https://github.com/bjornjohansen/WordPress-Instafeed
Description: Stream of photos from Instagram on your WordPress site
Version: 0.1
Author: Leidar
Author URI: http://twitter.com/leidar
Text Domain: wp-instafeed
License: GPL2

    Copyright 2014  Leidar  (email : teknisk@leidar.no)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

require_once 'widget.php';

class WordPress_InstaFeed {

	const VERSION = '0.1';
	const CLIENT_ID = '6409bc9c964348899c3ae1b9091965b9';

	function __construct() {
		add_action( 'wp_ajax_wp_instafeed_widgetcontent', array( $this, 'widgetcontent_callback' ) );
		add_action( 'wp_ajax_nopriv_wp_instafeed_widgetcontent', array( $this, 'widgetcontent_callback' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		wp_enqueue_script( 'wp_instafeed_widget', plugins_url( '/js/wp_instafeed_widget.js' , __FILE__ ), array( 'jquery' ), filemtime( dirname( __FILE__ ) . '/js/wp_instafeed_widget.js' ), true );
		wp_localize_script( 'wp_instafeed_widget', 'wp_instafeed', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	function widgetcontent_callback () {

		list( $widget_name, $widget_id ) = explode( '-', $_GET['widget'] );
		$all_widgets_options = get_option( 'widget_' . $widget_name );
		$widget_options = $all_widgets_options[ $widget_id ];

		$term = $widget_options[ 'instafeed_term' ];

		if ( '#' == substr( $term, 0, 1 ) ) {
			$list = $this->get_tag_stream( substr( $term, 1 ) );
		} else {
			$list = $this->get_user_stream( $term );
		}

		if ( count( $list ) ) {

			echo '<ul class="wp_instafeed_widget_list">';

			for ( $i = 0, $c = min( $widget_options[ 'num_entries' ], count( $list ) ); $i < $c; $i++ ) {
				echo '<li class="wp_instafeed_widget_list_item">';
				echo sprintf( '<a href="%s" target="_blank" class="wp_instafeed_widget_list_item_link"><img src="%s" alt="%s"></a>', esc_url( $list[$i]->link ), esc_url( $list[$i]->thumbnail ), esc_attr( $list[$i]->caption ) );
				echo '</li>';
			}

			echo '<ul>';
		}
		

		exit;

	}

	function get_tag_stream( $tag ) {
		$url = add_query_arg( array( 'client_id' => self::CLIENT_ID ), sprintf( 'https://api.instagram.com/v1/tags/%s/media/recent', $tag ) );

		$response = wp_remote_get( $url, $this->remote_get_args() );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		}

		$stuff = json_decode( wp_remote_retrieve_body( $response ) );
		$return = array();

		if ( isset( $stuff->data ) ) {
			foreach ( $stuff->data as $item ) {
				$current = new stdClass();
				$current->link = $item->link;
				$current->thumbnail = $item->images->thumbnail->url;
				if ( isset( $item->caption->text ) ) {
					$current->caption = $item->caption->text;
				} else {
					$current->caption = '';
				}
				
				if ( is_ssl() ) {
					$current->thumbnail = str_replace( 'http://', 'https://', $current->thumbnail );
				}

				$return[] = $current;
			}
		}
		return $return;
	}

	function get_user_stream( $username ) {

	}

	function remote_get_args() {
		$args = array(
			'timeout'     => 10,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => 'WordPress Instafeed' . self::VERSION . '; ' . get_bloginfo( 'url' ),
			'blocking'    => true,
			'headers'     => array(),
			'cookies'     => array(),
			'body'        => null,
			'compress'    => false,
			'decompress'  => true,
			'sslverify'   => true,
			'stream'      => false,
			'filename'    => null
		);

		return $args;
	}

}

new WordPress_InstaFeed;

