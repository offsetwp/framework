<?php
/**
 * OffsetWP Support
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Support
 */

declare( strict_types=1 );

namespace OffsetWP\Support;

/**
 * Env
 */
class Env {

	public const PRODUCTION  = 'production';
	public const STAGING     = 'staging';
	public const DEVELOPMENT = 'development';
	public const LOCAL       = 'local';

	/**
	 * Check if a environment variable exist
	 *
	 * @param string $key The variable key.
	 * @return bool
	 */
	public static function has( string $key ): bool {
		return isset( $_ENV[ $key ] );
	}

	/**
	 * Get casted environment variable
	 *
	 * @param string $key    The variable key.
	 * @param mixed  $default Default value if none exists.
	 * @return mixed
	 */
	public static function get( string $key, $default = null ): mixed {
		if ( ! self::has( $key ) ) {
			return $default;
		}

		$value = trim( $_ENV[ $key ] );
		$lower = mb_strtolower( $value );

		// null.
		if ( 'null' === $lower ) {
			return null;
		}

		// boolean.
		if ( in_array( $lower, array( 'true', 'false', 'yes', 'no', 'on', 'off', '1', '0' ), true ) ) {
			return in_array( $lower, array( 'true', 'yes', 'on', '1' ), true );
		}

		// integer.
		if ( '' !== $value && ( ctype_digit( $value ) || '-' === $value[0] && ctype_digit( substr( $value, 1 ) ) ) ) { // phpcs:ignore
			return (int) $value;
		}

		// float.
		if ( is_numeric( $value ) ) {
			return (float) $value;
		}

		// json.
		if ( str_starts_with( $value, '{' ) || str_starts_with( $value, '[' ) ) {
			$result = json_decode( $value, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $result;
			} else {
				return $default;
			}
		}

		// array.
		if ( str_contains( $value, '|' ) ) {
			return self::array( $key, '|', $default );
		}

		// string.
		return $_ENV[ $key ];
	}

	/**
	 * Get raw environment variable
	 *
	 * @param string $key     The variable key.
	 * @param mixed  $default Default value if none exists.
	 * @return string|null
	 */
	public static function raw( string $key, mixed $default = null ): string|null {
		return self::has( $key ) ? $_ENV[ $key ] : $default;
	}

	/**
	 * Get string environment variable
	 *
	 * @param string $key     The variable key.
	 * @param string $default Default value if none exists.
	 * @return string
	 */
	public static function string( string $key, string $default = '' ): string {
		return (string) self::raw( $key, $default );
	}

	/**
	 * Get integer environment variable
	 *
	 * @param string $key     The variable key.
	 * @param int    $default Default value if none exists.
	 * @return int
	 */
	public static function integer( string $key, int $default = 0 ): int {
		return (int) self::raw( $key, $default );
	}

	/**
	 * Get float environment variable
	 *
	 * @param string $key     The variable key.
	 * @param float  $default Default value if none exists.
	 * @return float
	 */
	public static function float( string $key, float $default = 0.0 ): float {
		return (float) self::raw( $key, $default );
	}

	/**
	 * Get boolean environment variable
	 *
	 * @param string $key     The variable key.
	 * @param bool   $default Default value if none exists.
	 * @return bool
	 */
	public static function boolean( string $key, bool $default = false ): bool {
		if ( ! self::has( $key ) ) {
			return $default;
		}
		$value = trim( mb_strtolower( self::raw( $key, '' ) ) );
		return 'true' === $value || '1' === $value;
	}

	/**
	 * Get array environment variable
	 *
	 * @param string $key       The variable key.
	 * @param string $separator The array separator.
	 * @param array  $default   Default value if none exists.
	 * @return array
	 */
	public static function array( string $key, string $separator = '|', array $default = array() ): array {
		if ( ! self::has( $key ) ) {
			return $default;
		}
		return explode( $separator, self::raw( $key, '' ) );
	}

	/**
	 * Get json environment variable
	 *
	 * @param string     $key     The variable key.
	 * @param mixed|null $default Default value if none exists.
	 * @return mixed
	 */
	public static function json( string $key, mixed $default = null ): mixed {
		if ( ! self::has( $key ) ) {
			return $default;
		}
		$value  = self::raw( $key, '' );
		$result = json_decode( $value, true );
		return json_last_error() === JSON_ERROR_NONE ? $result : $default;
	}

	/**
	 * Get environment type
	 *
	 * @return string
	 */
	public static function type(): string {
		return \wp_get_environment_type();
	}

	/**
	 * Get is local environment
	 *
	 * @return bool
	 */
	public static function isLocal(): bool {
		return self::type() === self::LOCAL;
	}

	/**
	 * Get is development environment
	 *
	 * @return bool
	 */
	public static function isDevelopment(): bool {
		return self::type() === self::DEVELOPMENT;
	}

	/**
	 * Get is staging environment
	 *
	 * @return bool
	 */
	public static function isStaging(): bool {
		return self::type() === self::STAGING;
	}

	/**
	 * Get is production environment
	 *
	 * @return bool
	 */
	public static function isProduction(): bool {
		return self::type() === self::PRODUCTION;
	}

	/**
	 * Get is debug environment
	 *
	 * @return bool
	 */
	public static function isDebug(): bool {
		return (bool) WP_DEBUG;
	}
}
