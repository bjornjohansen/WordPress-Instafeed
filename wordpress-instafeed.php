<?php
/*
Plugin Name: WordPress Instafeed
Plugin URI: https://github.com/bjornjohansen/WordPress-Instafeed
Description: Stream of photos from Instagram on your WordPress site
Version: 0.1.2
Author: Leidar
Author URI: http://leidar.com/
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

	const VERSION = '0.1.2';
	const CLIENT_ID = '6409bc9c964348899c3ae1b9091965b9';

	const DEFAULT_CLIENT_CACHETIME = 600;
	const DEFAULT_STREAM_CACHETIME = 3600;
	const DEFAULT_USERDATA_CACHETIME = 86400;

	function __construct() {
		add_action( 'wp_ajax_wp_instafeed_widgetcontent', array( $this, 'widgetcontent_callback' ) );
		add_action( 'wp_ajax_nopriv_wp_instafeed_widgetcontent', array( $this, 'widgetcontent_callback' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_translation' ) );
	}

	function init() {
		$jsfile = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'wp_instafeed_widget.js' : 'wp_instafeed_widget.min.js' );
		wp_enqueue_script( 'wp_instafeed_widget', plugins_url( '/js/' . $jsfile , __FILE__ ), array( 'jquery' ), filemtime( dirname( __FILE__ ) . '/js/' . $jsfile ), true );
		wp_localize_script( 'wp_instafeed_widget', 'wp_instafeed', array( 
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'client_cachetime' => apply_filters( 'wp_instafeed_client_cachetime', self::DEFAULT_CLIENT_CACHETIME ),
		) );
	}

	function load_translation() {
		load_plugin_textdomain( 'wp-instafeed', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
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

	function get_stream( $url ) {
		$ret = array();

		$transient_key = 'wpinstfd' . md5( $url );

		if ( false === ( $ret = get_transient( $transient_key ) ) ) {

			$ret = array();

			$response = wp_remote_get( $url, $this->remote_get_args() );

			if ( ! is_wp_error( $response ) ) {
				$tuff = json_decode( wp_remote_retrieve_body( $response ) );
				if ( isset( $tuff->data ) ) {
					foreach ( $tuff->data as $item ) {
						$current = new stdClass();
						$current->link = $item->link;
						$current->thumbnail = $item->images->thumbnail->url;
						/* Found bug in WP transient API. Need workaround. Skip captions for now */
						if ( false && isset( $item->caption->text ) ) {
							$current->caption = esc_html( $item->caption->text );
						} else {
							$current->caption = '';
						}
						
						// Scheme-relative urls
						$current->thumbnail = str_replace( 'http://', '//', $current->thumbnail );

						$ret[] = $current;
					}
					set_transient( $transient_key, $ret, apply_filters( 'wp_instafeed_stream_cachetime', self::DEFAULT_STREAM_CACHETIME  ) );
				}
			}
		}

		return $ret;
	}

	function get_tag_stream( $tag ) {
		$url = add_query_arg( array( 'client_id' => self::CLIENT_ID ), sprintf( 'https://api.instagram.com/v1/tags/%s/media/recent', $tag ) );
		$ret = $this->get_stream( $url );
		return $ret;
	}

	function get_user_stream( $username ) {
		$userdata = $this->userdata_from_username( $username );

		$ret = array();

		if ( count( $userdata ) ) {
			$url = add_query_arg( array( 'client_id' => self::CLIENT_ID ), sprintf( 'https://api.instagram.com/v1/users/%s/media/recent/', $userdata->id ) );
			$ret = $this->get_stream( $url );
		}

		return $ret;
	}

	function userdata_from_username( $username ) {

		$url = add_query_arg( array( 'q' => $username, 'client_id' => self::CLIENT_ID ), 'https://api.instagram.com/v1/users/search' );

		$transient_key = 'wpinstfd' . md5( $url );

		if ( false === ( $return = get_transient( $transient_key ) ) ) {

			$response = wp_remote_get( $url, $this->remote_get_args() );

			if ( is_wp_error( $response ) ) {
				$return = $response;
			} else {
				$omething = json_decode( wp_remote_retrieve_body( $response ) );
				if ( isset( $omething->data ) && count( $omething->data) ) {
					$return = $omething->data[0];

					set_transient( $transient_key, $return, apply_filters( 'wp_instafeed_userdata_cachetime', self::DEFAULT_USERDATA_CACHETIME ) );
				}
			}
		}

		return $return;
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

