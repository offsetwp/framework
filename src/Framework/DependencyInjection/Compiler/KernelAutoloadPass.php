<?php
/**
 * OffsetWP Framework
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Framework\DependencyInjection\Compiler
 */

declare( strict_types=1 );

namespace OffsetWP\Framework\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * KernelAutoloadPass
 */
final class KernelAutoloadPass implements CompilerPassInterface {

	/**
	 * {@inheritDoc}
	 *
	 * @param ContainerBuilder $container The container.
	 */
	public function process( ContainerBuilder $container ): void {
		foreach ( $container->findTaggedServiceIds( 'kernel.autoload' ) as $id => $tags ) {
			$container->get( $id );
		}
	}
}
