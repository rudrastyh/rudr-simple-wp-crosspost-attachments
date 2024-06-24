<?php
/*
 * Plugin name: Simple WP Crossposting – Attachments in Custom Fields
 * Author: Misha Rudrastyh
 * Author URI: https://rudrastyh.com
 * Description: Allows to crosspost media files from custom fields.
 * Version: 3.0
 * Plugin URI: https://rudrastyh.com/support/crossposting-attachments-from-post-meta
 */

class Rudr_SWC_Attachments {

	function __construct() {

		add_filter( 'rudr_swc_pre_crosspost_meta', array( $this, 'process_meta' ), 10, 4 );
		add_filter( 'rudr_swc_pre_crosspost_termmeta', array( $this, 'process_meta' ), 10, 4 );

	}

	function process_meta( $meta_value, $meta_key, $object_id, $blog ) {

		if( ! class_exists( 'Rudr_Simple_WP_Crosspost' ) ) {
			return $meta_value;
		}

		// not an attachment custom field
		if( ! in_array( $meta_key, apply_filters( 'rudr_crosspost_attachment_meta_keys', array() ) ) ) {
			return $meta_value;
		}

		$meta_value = maybe_unserialize( $meta_value );

		// comma separated
		if( false !== strpos( $meta_value, ',' ) ) {
			$meta_value = array_map( 'trim', explode( ',', $meta_value ) );
		}

		if( is_array( $meta_value ) ) {
			// gallery field
			$meta_value = array_filter( array_map( function( $attachment_id ) use ( $blog ) {
				$crossposted = Rudr_Simple_WP_Crosspost::maybe_crosspost_image( $attachment_id, $blog );
				if( isset( $crossposted[ 'id' ] ) && $crossposted[ 'id' ] ) {
					return $crossposted[ 'id' ];
				}
				return false; // will be removed with array_filter()
			}, $meta_value ) );
		} else {
			// image or file field
			$crossposted = Rudr_Simple_WP_Crosspost::maybe_crosspost_image( $meta_value, $blog );
			if( isset( $crossposted[ 'id' ] ) && $crossposted[ 'id' ] ) {
				$meta_value = $crossposted[ 'id' ];
			}
		}
		//return null;
		return $meta_value;

	}


}

new Rudr_SWC_Attachments;
