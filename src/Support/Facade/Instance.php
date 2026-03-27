<?php
/**
 * OffsetWP Support
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Support\Facade
 */

declare( strict_types=1 );

namespace OffsetWP\Support\Facade;

/**
 * Register of designated bodies.
 *
 * Allows you to store and retrieve objects by name in isolation,
 * without using a global singleton.
 */
class Instance {
	/**
	 * Registered instances.
	 *
	 * @var array<string, object>
	 */
	private static array $registry = array();

	/**
	 * Register a named instance.
	 *
	 * @param  string $name     The unique name to register the instance under.
	 * @param  object $instance The instance to register.
	 * @return object The registered instance.
	 *
	 * @throws \LogicException If a instance is already registered under this name.
	 */
	public static function register( string $name, object $instance ): object {
		if ( isset( self::$registry[ $name ] ) ) {
			throw new \LogicException( sprintf( 'A instance is already registered under "%s".', $name ) );
		}

		self::$registry[ $name ] = $instance;

		return self::$registry[ $name ];
	}

	/**
	 * Retrieve a named instance.
	 *
	 * Usage without type hint — returns object:
	 *   $kernel = Instance::get( 'app' );
	 *
	 * @param  string $name  The name the instance was registered under.
	 * @return object The registered instance.
	 *
	 * @throws \LogicException If no instance is registered under this name.
	 */
	public static function get( string $name ): object {
		if ( ! isset( self::$registry[ $name ] ) ) {
			throw new \LogicException( sprintf( 'No instance registered under "%s".', $name ) );
		}

		return self::$registry[ $name ];
	}
}
