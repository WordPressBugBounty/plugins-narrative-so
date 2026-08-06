<?php
/**
 * Authenticator class.
 *
 * @package Narrative_Publisher/Authenticator;
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check the API token.
 */
class Authenticator {
	static $PASS_CODE_LENGTH = 6;
	static $PIN_MODULO;
	static $SECRET_LENGTH = 10;

	public function __construct() {
		self::$PIN_MODULO = pow( 10, self::$PASS_CODE_LENGTH );
	}

	public function checkCode( $secret, $code ) {

		if ( ! is_string( $code ) || '' === $code ) {
			return false;
		}

		$time = floor( time() / 30 );

		$valid = false;

		for ( $i = - 1; $i <= 1; $i ++ ) {

			/*
			 * hash_equals(), not ==. Both operands are numeric strings, so loose
			 * comparison compared them as numbers and treated "012345" and
			 * "12345" as the same code. It is also constant time.
			 *
			 * The loop deliberately runs to completion rather than returning
			 * early, so the time taken does not reveal which window matched.
			 */
			if ( hash_equals( (string) $this->getCode( $secret, $time + $i ), $code ) ) {
				$valid = true;
			}
		}

		return $valid;

	}

	public function getCode( $secret, $time = null ) {

		if ( ! $time ) {
			$time = floor( time() / 30 );
		}
		$base32 = new FixedBitNotation( 5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', true, true );
		$secret = $base32->decode( $secret );

		$time = pack( "N", $time );
		$time = str_pad( $time, 8, chr( 0 ), STR_PAD_LEFT );

		$hash   = hash_hmac( 'sha1', $time, $secret, true );
		$offset = ord( substr( $hash, - 1 ) );
		$offset = $offset & 0xF;

		$truncatedHash = $this->hashToInt( $hash, $offset ) & 0x7FFFFFFF;
		$pinValue      = str_pad( $truncatedHash % self::$PIN_MODULO, 6, "0", STR_PAD_LEFT );

		return $pinValue;
	}

	protected function hashToInt( $bytes, $start ) {
		$input = substr( $bytes, $start, strlen( $bytes ) - $start );
		$val2  = unpack( "N", substr( $input, 0, 4 ) );

		return $val2[1];
	}

}
