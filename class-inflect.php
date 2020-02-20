<?php
/**
 * Static Inflector class
 *
 * Author: T. Brian Jones
 * Author URI: https://github.com/tbrianjones
 *
 * @author @tbrianjones
 * @link https://gist.github.com/tbrianjones/ba0460cc1d55f357e00b
 * @package WPGraphQL\WooCommerce
 * @since 0.0.4
 */

if ( ! class_exists( 'Inflect' ) ) :
	/**
	 * Class Inflect
	 */
	class Inflect { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
		/**
		 * Stores plural suffixes.
		 *
		 * @var array $plural
		 */
		private static $plural = array(
			'/(quiz)$/i'                     => '$1zes',
			'/^(ox)$/i'                      => '$1en',
			'/([m|l])ouse$/i'                => '$1ice',
			'/(matr|vert|ind)ix|ex$/i'       => '$1ices',
			'/(x|ch|ss|sh)$/i'               => '$1es',
			'/([^aeiouy]|qu)y$/i'            => '$1ies',
			'/(hive)$/i'                     => '$1s',
			'/(?:([^f])fe|([lr])f)$/i'       => '$1$2ves',
			'/(shea|lea|loa|thie)f$/i'       => '$1ves',
			'/sis$/i'                        => 'ses',
			'/([ti])um$/i'                   => '$1a',
			'/(tomat|potat|ech|her|vet)o$/i' => '$1oes',
			'/(bu)s$/i'                      => '$1ses',
			'/(alias)$/i'                    => '$1es',
			'/(octop)us$/i'                  => '$1i',
			'/(ax|test)is$/i'                => '$1es',
			'/(us)$/i'                       => '$1es',
			'/s$/i'                          => 's',
			'/$/'                            => 's',
		);

		/**
		 * Stores singular suffixes
		 *
		 * @var array $singular
		 */
		private static $singular = array(
			'/(quiz)zes$/i'              => '$1',
			'/(matr)ices$/i'             => '$1ix',
			'/(vert|ind)ices$/i'         => '$1ex',
			'/^(ox)en$/i'                => '$1',
			'/(alias)es$/i'              => '$1',
			'/(octop|vir)i$/i'           => '$1us',
			'/(cris|ax|test)es$/i'       => '$1is',
			'/(shoe)s$/i'                => '$1',
			'/(o)es$/i'                  => '$1',
			'/(bus)es$/i'                => '$1',
			'/([m|l])ice$/i'             => '$1ouse',
			'/(x|ch|ss|sh)es$/i'         => '$1',
			'/(m)ovies$/i'               => '$1ovie',
			'/(s)eries$/i'               => '$1eries',
			'/([^aeiouy]|qu)ies$/i'      => '$1y',
			'/([lr])ves$/i'              => '$1f',
			'/(tive)s$/i'                => '$1',
			'/(hive)s$/i'                => '$1',
			'/(li|wi|kni)ves$/i'         => '$1fe',
			'/(shea|loa|lea|thie)ves$/i' => '$1f',
			'/(^analy)ses$/i'            => '$1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '$1$2sis',
			'/([ti])a$/i'                => '$1um',
			'/(n)ews$/i'                 => '$1ews',
			'/(h|bl)ouses$/i'            => '$1ouse',
			'/(corpse)s$/i'              => '$1',
			'/(us)es$/i'                 => '$1',
			'/s$/i'                      => '',
		);

		/**
		 * Stores irregular words
		 *
		 * @var array $irregular
		 */
		private static $irregular = array(
			'move'   => 'moves',
			'foot'   => 'feet',
			'goose'  => 'geese',
			'sex'    => 'sexes',
			'child'  => 'children',
			'man'    => 'men',
			'tooth'  => 'teeth',
			'person' => 'people',
			'valve'  => 'valves',
		);

		/**
		 * Stores words without plural tenses
		 *
		 * @var array $uncountable
		 */
		private static $uncountable = array(
			'sheep',
			'fish',
			'deer',
			'series',
			'species',
			'money',
			'rice',
			'information',
			'equipment',
		);

		/**
		 * Return plural tense of provide string
		 *
		 * @param string $string - word to be pluralized.
		 * @return string
		 */
		public static function pluralize( $string ) {
			// save some time in the case that singular and plural are the same.
			if ( in_array( strtolower( $string ), self::$uncountable, true ) ) {
				return $string;
			}

			// check for irregular singular forms.
			foreach ( self::$irregular as $pattern => $result ) {
				$pattern = '/' . $pattern . '$/i';

				if ( preg_match( $pattern, $string ) ) {
					return preg_replace( $pattern, $result, $string );
				}
			}

			// check for matches using regular expressions.
			foreach ( self::$plural as $pattern => $result ) {
				if ( preg_match( $pattern, $string ) ) {
					return preg_replace( $pattern, $result, $string );
				}
			}

			return $string;
		}

		/**
		 * Return singular tense of provided string
		 *
		 * @param string $string String to be singularized.
		 * @return string
		 */
		public static function singularize( $string ) {
			// save some time in the case that singular and plural are the same.
			if ( in_array( strtolower( $string ), self::$uncountable, true ) ) {
				return $string;
			}

			// check for irregular plural forms.
			foreach ( self::$irregular as $result => $pattern ) {
				$pattern = '/' . $pattern . '$/i';

				if ( preg_match( $pattern, $string ) ) {
					return preg_replace( $pattern, $result, $string );
				}
			}

			// check for matches using regular expressions.
			foreach ( self::$singular as $pattern => $result ) {
				if ( preg_match( $pattern, $string ) ) {
					return preg_replace( $pattern, $result, $string );
				}
			}

			return $string;
		}

		/**
		 * Return plural tense if provided count is greater than 1
		 *
		 * @param int    $count Count to be evaluated.
		 * @param string $string String potentially pluralized.
		 */
		public static function pluralize_if( $count, $string ) {
			if ( 1 === $count ) {
				return '1 $string';
			}

			return $count . ' ' . self::pluralize( $string );
		}

		/**
		 * Converts a camel case formatted string to a underscore formatted string.
		 *
		 * @param string  $string      String to be formatted.
		 * @param boolean $capitalize  Capitalize first letter of string.
		 *
		 * @return string
		 */
		public static function underscore_to_camel_case( $string, $capitalize = false ) {
			$str = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $string ) ) );

			if ( ! $capitalize ) {
				$str[0] = strtolower( $str[0] );
			}

			return $str;
		}

		/**
		 * Converts a camel case formatted string to a underscore formatted string.
		 *
		 * @param string $string  String to be formatted.
		 *
		 * @return string
		 */
		public static function camel_case_to_underscore( $string ) {
			preg_match_all(
				'!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!',
				$string,
				$matches
			);

			$ret = $matches[0];

			foreach ( $ret as &$match ) {
				$match = strtoupper( $match ) === $match ? strtolower( $match ) : lcfirst( $match );
			}

			return implode( '_', $ret );
		}
	}
endif;
