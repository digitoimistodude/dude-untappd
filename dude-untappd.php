<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2017-04-26 14:33:19
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2017-04-28 17:40:15
 */

if( !defined( 'ABSPATH' )  )
	exit();

Class Dude_Untappd_Feed {
  private static $_instance = null;

  /**
   * Construct everything and begin the magic!
   *
   * @since   0.1.0
   * @version 0.1.0
   */
  public function __construct() {
  } // end function __construct

  /**
   *  Prevent cloning
   *
   *  @since   0.1.0
   *  @version 0.1.0
   */
  public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'dude' ) );
	} // end function __clone

  /**
   *  Prevent unserializing instances of this class
   *
   *  @since   0.1.0
   *  @version 0.1.0
   */
  public function __wakeup() {
    _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'dude' ) );
  } // end function __wakeup

  /**
   *  Ensure that only one instance of this class is loaded and can be loaded
   *
   *  @since   0.1.0
   *  @version 0.1.0
	 *  @return  Main instance
   */
  public static function instance() {
    if( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }

    return self::$_instance;
  } // end function instance

  public function get_user( $username = '' ) {
		if( empty( $username ) )
			return;

		$transient_name = apply_filters( 'dude-untappd/user_info_transient', 'dude-untappd-user-'.$username, $username );
		$info = get_transient( $transient_name );
	  if( !empty( $info ) || false != $info )
	    return $info;

    $parameters = array(
			'client_id'	     => apply_filters( 'dude-untappd/client_id/user='.$username, '', $username ),
			'client_secret'	 => apply_filters( 'dude-untappd/client_secret/user='.$username, '', $username ),
		);

		$response = self::_call_api( 'user/info/'.$username, $parameters );
		if( $response === FALSE )
			return;

		$i = 0;
		$activity = array();
		$response = apply_filters( 'dude-untappd/user_activity', json_decode( $response, true ) );

		set_transient( $transient_name, $response['response'], apply_filters( 'dude-untappd/user_info_lifetime', '600' ) );
		return $response['response'];
	} // end function get_user

	public function get_venue_checkins( $venueid = '' ) {
		if( empty( $venueid ) )
			return;

		$transient_name = apply_filters( 'dude-untappd/venue_checkins_transient', 'dude-untappd-venue-'.$venueid, $venueid );
		$info = get_transient( $transient_name );
	  if( !empty( $info ) || false != $info )
	    return $info;

    $parameters = array(
			'client_id'	     => apply_filters( 'dude-untappd/client_id', '' ),
			'client_secret'	 => apply_filters( 'dude-untappd/client_secret', '' ),
		);

		$response = self::_call_api( 'venue/checkins/'.$venueid, $parameters );
		if( $response === FALSE )
			return;

		$i = 0;
		$activity = array();
		$response = apply_filters( 'dude-untappd/venue_checkins', json_decode( $response, true ) );

		set_transient( $transient_name, $response['response'], apply_filters( 'dude-untappd/venue_checkins_lifetime', '600' ) );
		return $response['response'];
	} // end function get_venue_checkins

	public function get_venue_info( $venueid = '' ) {
		if( empty( $venueid ) )
			return;

		$transient_name = apply_filters( 'dude-untappd/venue_info_transient', 'dude-untappd-venueinfo-'.$venueid, $venueid );
		$info = get_transient( $transient_name );
	  if( !empty( $info ) || false != $info )
	    return $info;

    $parameters = array(
			'client_id'	     => apply_filters( 'dude-untappd/client_id', '' ),
			'client_secret'	 => apply_filters( 'dude-untappd/client_secret', '' ),
		);

		$response = self::_call_api( 'venue/info/'.$venueid, $parameters );
		if( $response === FALSE )
			return;

		$i = 0;
		$activity = array();
		$response = apply_filters( 'dude-untappd/venue_info', json_decode( $response, true ) );

		set_transient( $transient_name, $response['response'], apply_filters( 'dude-untappd/venue_info_lifetime', '600' ) );
		return $response['response'];
	} // end function get_venue_info

	private function _call_api( $endpoint = '', $parameters ) {
		if( empty( $endpoint ) )
			return false;

    $parameters = http_build_query( $parameters );
    $response = wp_remote_get( 'https://api.untappd.com/v4/'.$endpoint.'/?'.$parameters );

		if( $response['response']['code'] !== 200 ) {
			self::_write_log( 'response status code not 200 OK, endpoint: '.$url );
			return false;
		}

		return $response['body'];
	} // end function _call_api

	private function _write_log ( $log )  {
    if( true === WP_DEBUG ) {
      if( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ) );
      } else {
        error_log( $log );
      }
    }
  } // end _write_log
} // end class Dude_Untappd_Feed

function dude_untappd_feed() {
  return new Dude_Untappd_Feed();
} // end function dude_untappd_feed
