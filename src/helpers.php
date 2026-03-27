<?php
/**
 * OffsetWP Helpers
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP
 */

declare( strict_types=1 );

use OffsetWP\Support\Facade\Instance;

if ( ! function_exists( 'app' ) ) {
	/**
	 * Get or register the application instance.
	 *
	 * Example:
	 * * `app( 'app', new \App\Application() );`
	 * * `app()->doSomething();`
	 *
	 * @template TClass of object
	 *
	 * @param string|class-string<TClass>|null $name Application name.
	 * @param null|TClass                      $instance_to_register Application instance.
	 * @return ($name is class-string<TClass> ? TClass : ($name is null ? \OffsetWP\Framework\Kernel : mixed))
	 */
	function app( ?string $name = null, ?object $instance_to_register = null ): ?object {
		return instance( null === $name ? 'app' : $name, $instance_to_register );
	}
}

if ( ! function_exists( 'instance' ) ) {
	/**
	 * Get or register a instance.
	 *
	 * Example:
	 * * `instance( 'my_theme', new \OffsetWP\Theme\MyTheme() );`
	 * * `instance( 'my_theme' )->doSomething();`
	 *
	 * @template TClass of object
	 *
	 * @param string|class-string<TClass>|null $name Instance name.
	 * @param null|TClass                      $instance_to_register The Instance.
	 * @return ($name is class-string<TClass> ? TClass : ($name is null ? \OffsetWP\Framework\Kernel : mixed))
	 */
	function instance( string $name, ?object $instance_to_register = null ): ?object {
		if ( ! empty( $instance_to_register ) ) {
			return Instance::register( $name, $instance_to_register );
		}

		return Instance::get( $name );
	}
}
