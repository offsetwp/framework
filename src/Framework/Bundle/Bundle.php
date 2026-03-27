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

use OffsetWP\Framework\Bundle\BundleInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * A Bundle that provides configuration hooks.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
abstract class Bundle implements BundleInterface, ConfigurableExtensionInterface {
	/**
	 * Bundle name
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Bundle extension
	 *
	 * @var ExtensionInterface|false|null
	 */
	protected ExtensionInterface|false|null $extension = null;

	/**
	 * The extension alias
	 *
	 * @var string
	 */
	protected string $extension_alias = '';

	/**
	 * Bundle namespace
	 *
	 * @var string
	 */
	private string $namespace;

	/**
	 * Bundle path
	 *
	 * @var string
	 */
	protected string $path;

	/**
	 * The service container
	 *
	 * @var ContainerInterface|null
	 */
	protected ?ContainerInterface $container;

	/**
	 * Boots the Bundle.
	 *
	 * @return void
	 */
	public function boot(): void {
	}

	/**
	 * Shutdowns the Bundle.
	 *
	 * @return void
	 */
	public function shutdown(): void {
	}

	/**
	 * This method can be overridden to register compilation passes, other extensions, ...
	 *
	 * @param ContainerBuilder $container The container builder.
	 * @return void
	 */
	public function build( ContainerBuilder $container ): void {
	}

	/**
	 * Returns the bundle name (the class short name).
	 *
	 * @return string
	 */
	final public function getName(): string {
		if ( ! isset( $this->name ) ) {
			$this->parseClassName();
		}

		return $this->name;
	}

	/**
	 * Get namespace
	 *
	 * @return string
	 */
	public function getNamespace(): string {
		if ( ! isset( $this->namespace ) ) {
			$this->parseClassName();
		}

		return $this->namespace;
	}

	/**
	 * Parses the class name to set the namespace and name properties.
	 *
	 * @return void
	 */
	private function parseClassName(): void {
		$pos             = strrpos( static::class, '\\' );
		$this->namespace = false === $pos ? '' : substr( static::class, 0, $pos );
		$this->name    ??= false === $pos ? static::class : substr( static::class, $pos + 1 );
	}

	/**
	 * Get path
	 *
	 * @return string
	 */
	public function getPath(): string {
		if ( ! isset( $this->path ) ) {
			$reflected = new \ReflectionObject( $this );
			// assume the modern directory structure by default.
			$this->path = \dirname( $reflected->getFileName(), 2 );
		}

		return $this->path;
	}

	/**
	 * Sets the container.
	 *
	 * @param ContainerInterface|null $container The service container.
	 */
	public function setContainer( ?ContainerInterface $container ): void {
		$this->container = $container;
	}

	/**
	 * Configure the bundle extension.
	 *
	 * @param DefinitionConfigurator $definition The definition configurator.
	 * @return void
	 */
	public function configure( DefinitionConfigurator $definition ): void {
	}

	/**
	 * Allows an extension to prepend the extension configurations.
	 *
	 * @param ContainerConfigurator $container The container configurator.
	 * @param ContainerBuilder      $builder   The service container.
	 * @return void
	 */
	public function prependExtension( ContainerConfigurator $container, ContainerBuilder $builder ): void {
	}

	/**
	 * Loads a specific configuration.
	 *
	 * @param array                 $config    The configuration.
	 * @param ContainerConfigurator $container The container configurator.
	 * @param ContainerBuilder      $builder   The service container.
	 * @return void
	 */
	public function loadExtension( array $config, ContainerConfigurator $container, ContainerBuilder $builder ): void {
	}

	/**
	 * Returns the bundle's container extension.
	 *
	 * @throws \LogicException If the extension does not implement ExtensionInterface.
	 * @throws \LogicException If the extension alias does not follow the naming conventions.
	 * @return null|ExtensionInterface
	 */
	public function getContainerExtension(): ?ExtensionInterface {
		if ( '' === $this->extension_alias ) {
			$this->extension_alias = Container::underscore( preg_replace( '/Bundle$/', '', $this->getName() ) );
		}

		return $this->extension ??= new BundleExtension( $this, $this->extension_alias );
	}
}
