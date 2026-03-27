<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package OffsetWP\Framework\Bundle
 */

declare( strict_types=1 );

namespace OffsetWP\Framework\Bundle;

use Symfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionTrait;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * BundleExtension.
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class BundleExtension extends Extension implements PrependExtensionInterface {
	use ExtensionTrait;

	/**
	 * Constructor.
	 *
	 * @param Bundle $bundle The bundle extension subject.
	 * @param string $alias   The extension alias.
	 * @return void
	 */
	public function __construct( private Bundle $bundle, private string $alias ) {
	}

	/**
	 * Gets the extension alias.
	 *
	 * @return string
	 */
	public function getAlias(): string {
		return $this->alias;
	}

	/**
	 * Gets the configuration instance.
	 *
	 * @param array            $config    The configuration.
	 * @param ContainerBuilder $container The service container.
	 * @return null|ConfigurationInterface
	 */
	public function getConfiguration( array $config, ContainerBuilder $container ): ?ConfigurationInterface {
		return new Configuration( $this->bundle, $container, $this->getAlias() );
	}

	/**
	 * Allows an extension to prepend the extension configurations.
	 *
	 * @param ContainerBuilder $container The service container.
	 * @return void
	 */
	public function prepend( ContainerBuilder $container ): void {
		$callback = function ( ContainerConfigurator $configurator ) use ( $container ) {
			$this->bundle->prependExtension( $configurator, $container );
		};

		$this->executeConfiguratorCallback( $container, $callback, $this->bundle, true );
	}

	/**
	 * Loads a specific configuration.
	 *
	 * @param array<string|int, array<string|int, mixed>> $configs   The configuration.
	 * @param ContainerBuilder                            $container The service container.
	 * @return void
	 */
	public function load( array $configs, ContainerBuilder $container ): void {
		$config = $this->processConfiguration( $this->getConfiguration( array(), $container ), $configs );

		$callback = function ( ContainerConfigurator $configurator ) use ( $config, $container ) {
			$this->bundle->loadExtension( $config, $configurator, $container );
		};

		$this->executeConfiguratorCallback( $container, $callback, $this->bundle );
	}
}
