<?php

/**
 * @class ES_Format
 * @since 4.4.0
 */
class ES_Format {

	const MYSQL = 'Y-m-d H:i:s';


	/**
	 * @param int|string|DateTime|\WC_DateTime $date
	 * @param bool|int                         $max_diff Set to 0 to disable diff format
	 * @param bool                             $convert_from_gmt If its gmt convert it to local time
	 * @param bool                             $shorten_month
	 *
	 * @since 3.8 $shorten_month param added
	 *
	 * @return string|false
	 */
	static function datetime( $date, $max_diff = false, $convert_from_gmt = true, $shorten_month = false ) {
		if ( ! $timestamp = self::mixed_date_to_timestamp( $date ) ) {
			return false; // convert to timestamp ensures WC_DateTime objects are in UTC
		}

		if ( $convert_from_gmt ) {
			$timestamp = strtotime( get_date_from_gmt( date( self::MYSQL, $timestamp ), self::MYSQL ) );
		}

		$now = current_time( 'timestamp' );

		if ( $max_diff === false ) {
			$max_diff = DAY_IN_SECONDS; // set default
		}

		$diff = $timestamp - $now;

		if ( abs( $diff ) >= $max_diff ) {
			return $date_to_display = date_i18n( self::get_date_format( $shorten_month ) . ' ' . wc_time_format(), $timestamp );
		}

		return self::human_time_diff( $timestamp );
	}


	/**
	 * @param int|string|DateTime|\WC_DateTime $date
	 * @param bool|int                         $max_diff
	 * @param bool                             $convert_from_gmt If its gmt convert it to local time
	 * @param bool                             $shorten_month
	 *
	 * @since 3.8 $shorten_month param added
	 *
	 * @return string|false
	 */
	static function date( $date, $max_diff = false, $convert_from_gmt = true, $shorten_month = false ) {
		if ( ! $timestamp = self::mixed_date_to_timestamp( $date ) ) {
			return false; // convert to timestamp ensures WC_DateTime objects are in UTC
		}

		if ( $convert_from_gmt ) {
			$timestamp = strtotime( get_date_from_gmt( date( self::MYSQL, $timestamp ), self::MYSQL ) );
		}

		$now = current_time( 'timestamp' );

		if ( $max_diff === false ) {
			$max_diff = WEEK_IN_SECONDS; // set default
		}

		$diff = $timestamp - $now;

		if ( abs( $diff ) >= $max_diff ) {
			return $date_to_display = date_i18n( self::get_date_format( $shorten_month ), $timestamp );
		}

		return self::human_time_diff( $timestamp );
	}


	/**
	 * @since 3.8
	 * @param bool $shorten_month
	 * @return string
	 */
	static function get_date_format( $shorten_month = false ) {
		$format = wc_date_format();

		if ( $shorten_month ) {
			$format = str_replace( 'F', 'M', $format );
		}

		return $format;
	}


	/**
	 * @param integer $timestamp
	 * @return string
	 */
	private static function human_time_diff( $timestamp ) {
		$now = current_time( 'timestamp' );

		$diff = $timestamp - $now;

		if ( $diff < 55 && $diff > -55 ) {
			$diff_string = sprintf( _n( '%d second', '%d seconds', abs( $diff ), 'email-subscribers' ), abs( $diff ) );
		} else {
			$diff_string = human_time_diff( $now, $timestamp );
		}

		if ( $diff > 0 ) {
			return sprintf( __( '%s from now', 'email-subscribers' ), $diff_string );
		} else {
			return sprintf( __( '%s ago', 'email-subscribers' ), $diff_string );
		}
	}


	/**
	 * @param int|string|DateTime $date
	 * @return int|bool
	 */
	static function mixed_date_to_timestamp( $date ) {
		if ( ! $date ) {
			return false;
		}

		$timestamp = 0;

		if ( is_numeric( $date ) ) {
			$timestamp = $date;
		} else {
			if ( is_a( $date, 'DateTime' ) ) { // maintain support for \DateTime
				$timestamp = $date->getTimestamp();
			} elseif ( is_string( $date ) ) {
				$timestamp = strtotime( $date );
			}
		}

		if ( $timestamp < 0 ) {
			return false;
		}

		return $timestamp;
	}


	/**
	 * @param integer $day - 1 (for Monday) through 7 (for Sunday)
	 * @return string|false
	 */
	static function weekday( $day ) {

		global $wp_locale;

		$days = array(
			1 => $wp_locale->get_weekday( 1 ),
			2 => $wp_locale->get_weekday( 2 ),
			3 => $wp_locale->get_weekday( 3 ),
			4 => $wp_locale->get_weekday( 4 ),
			5 => $wp_locale->get_weekday( 5 ),
			6 => $wp_locale->get_weekday( 6 ),
			7 => $wp_locale->get_weekday( 0 ),
		);

		if ( ! isset( $days[ $day ] ) ) {
			return false;
		}

		return $days[ $day ];
	}
}
