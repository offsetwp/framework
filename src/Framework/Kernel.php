<?php
/**
 * OffsetWP Framework
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Framework
 *
 * This file includes code derived from the Symfony component.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * Licensed under the MIT License.
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

declare( strict_types=1 );

namespace OffsetWP\Framework;

use OffsetWP\Framework\Bundle\Bundle;
use OffsetWP\Framework\Configuration\KernelBuilder;
use OffsetWP\Framework\DependencyInjection\Compiler\KernelAutoloadPass;
use OffsetWP\Support\Env;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Kernel
 */
class Kernel {
	/**
	 * The root path
	 *
	 * @var string
	 */
	protected string $root_path = '';

	/**
	 * Environment type
	 *
	 * @var string
	 */
	protected string $environment = Env::PRODUCTION;

	/**
	 * Charset
	 *
	 * @var string
	 */
	protected string $charset = 'UTF-8';

	/**
	 * Is debugging
	 *
	 * @var bool
	 */
	protected bool $is_debug = false;

	/**
	 * Dependency Injection config path
	 *
	 * @var string
	 */
	protected string $config_path = '';

	/**
	 * Dependency Injection standalone service file
	 *
	 * @var string
	 */
	protected string $services_path = '';

	/**
	 * Dependency Injection
	 *
	 * @var ContainerBuilder $container
	 */
	protected ?ContainerBuilder $container;

	/**
	 * Registered bundles
	 *
	 * @var array<string, \OffsetWP\Framework\Bundle\BundleInterface>
	 */
	protected $bundles = array();

	/**
	 * Is kernel booted
	 *
	 * @var bool $is_booted
	 */
	protected bool $is_booted = false;

	/**
	 * The kernel
	 *
	 * @param string $root_path The root path.
	 * @return void
	 */
	public function __construct( string $root_path ) {
		$this->setRootPath( $root_path );
	}

	/**
	 * Create a kernel builder.
	 *
	 * @param string $root_path The root path.
	 * @return KernelBuilder
	 */
	public static function configure( string $root_path ): KernelBuilder {
		return new KernelBuilder( $root_path );
	}

	/**
	 * Gets the application root path (path of the project's composer file).
	 *
	 * @return string
	 */
	public function rootPath(): string {
		return $this->root_path;
	}

	/**
	 * Set root path
	 *
	 * @param string $root_path  The root path.
	 * @throws \RuntimeException The path of root folder does not exist.
	 * @return self
	 */
	public function setRootPath( string $root_path ): self {
		if ( empty( $root_path ) || ! is_dir( $root_path ) ) {
			throw new \RuntimeException( 'The path of root folder does not exist.' );
		}
		$this->root_path = $root_path;
		return $this;
	}

	/**
	 * Get environment
	 *
	 * @return string The environment type.
	 */
	public function environment(): string {
		return $this->environment;
	}

	/**
	 * Set environment
	 *
	 * @param string $environment The environment type.
	 * @return self
	 */
	public function setEnvironment( string $environment ): self {
		$this->environment = $environment;
		return $this;
	}

	/**
	 * Get charset
	 *
	 * @return string The charset.
	 */
	public function charset(): string {
		return $this->charset;
	}

	/**
	 * Set charset
	 *
	 * @param string $charset The charset.
	 * @return self
	 */
	public function setCharset( string $charset ): self {
		$this->charset = $charset;
		return $this;
	}

	/**
	 * Get debug
	 *
	 * @return bool Debug is enable or not.
	 */
	public function isDebug(): bool {
		return $this->is_debug;
	}

	/**
	 * Set debug
	 *
	 * @param bool $is_debug Debug is enable or not.
	 * @return self
	 */
	public function setDebug( bool $is_debug ): self {
		$this->is_debug = $is_debug;
		return $this;
	}

	/**
	 * Gets the path to the configuration directory.
	 *
	 * @return string
	 */
	public function configPath(): string {
		return $this->config_path;
	}

	/**
	 * Set the config path
	 *
	 * @param string $config_path The config directory path.
	 * @throws \RuntimeException  The config folder does not exist.
	 * @return self
	 */
	public function setConfigPath( string $config_path ): self {
		if ( empty( $config_path ) || ! is_dir( $config_path ) ) {
			throw new \RuntimeException( 'The config folder does not exist.' );
		}
		$this->config_path = $config_path;
		return $this;
	}

	/**
	 * Gets the path to the services file.
	 *
	 * @return string
	 */
	public function servicesPath(): string {
		return $this->services_path;
	}

	/**
	 * Set the services file
	 *
	 * @param string $services_path The services file path.
	 * @throws \RuntimeException  The services file does not exist.
	 * @return self
	 */
	public function setServicesPath( string $services_path ): self {
		if ( empty( $services_path ) || ! is_file( $services_path ) ) {
			throw new \RuntimeException( 'The services file does not exist.' );
		}
		$this->services_path = $services_path;
		return $this;
	}

	/**
	 * Get container
	 *
	 * @throws \LogicException Cannot retrieve the container from a non-booted kernel.
	 * @return ContainerInterface Services container
	 */
	public function container(): ContainerInterface {
		if ( empty( $this->container ) ) {
			throw new \LogicException( 'Cannot retrieve the container from a non-booted kernel.' );
		}

		return $this->container;
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string|class-string<C> $id The service id.
	 * @return ($id is class-string<C> ? (B is 0|1 ? C|object : C|object|null) : (B is 0|1 ? object : object|null)) The service.
	 */
	public function service( string $id ): ?object {
		return $this->container()->get( $id );
	}

	/**
	 * Check if a service exists in the container.
	 *
	 * @param string $id The service id.
	 * @return bool True if the service exists, false otherwise.
	 */
	public function hasService( string $id ): bool {
		return $this->container()->has( $id );
	}

	/**
	 * Get a parameter from the container.
	 *
	 * @param string $name The parameter name.
	 * @return array|bool|string|int|float|\UnitEnum|null The parameter value.
	 */
	public function parameter( string $name ): array|bool|string|int|float|\UnitEnum|null {
		return $this->container()->getParameter( $name );
	}

	/**
	 * Check if a parameter exists in the container.
	 *
	 * @param string $name The parameter name.
	 * @return bool True if the parameter exists, false otherwise.
	 */
	public function hasParameter( string $name ): bool {
		return $this->container()->hasParameter( $name );
	}

	/**
	 * Gets the path to the bundles configuration file.
	 *
	 * @return string
	 */
	public function bundlesPath(): string {
		return $this->configPath() . DIRECTORY_SEPARATOR . 'bundles.php';
	}

	/**
	 * Gets the cache path.
	 *
	 * @return string
	 */
	public function cachePath(): string {
		if ( ! empty( $_ENV['APP_CACHE_PATH'] ) ) {
			return (string) $_ENV['APP_CACHE_PATH'];
		}
		return $this->rootPath() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->environment();
	}

	/**
	 * Gets the build path.
	 *
	 * @return string
	 */
	public function buildPath(): string {
		if ( ! empty( $_ENV['APP_BUILD_PATH'] ) ) {
			return (string) $_ENV['APP_BUILD_PATH'];
		}
		return $this->cachePath();
	}

	/**
	 * The extension point similar to the Bundle::build() method.
	 *
	 * Use this method to register compiler passes and manipulate the container during the building process.
	 *
	 * @param ContainerBuilder $container The service container.
	 * @return void
	 */
	protected function build( ContainerBuilder $container ): void {
		$container->addCompilerPass( new KernelAutoloadPass(), PassConfig::TYPE_OPTIMIZE );
	}

	/**
	 * Boot the kernel
	 *
	 * @return self
	 */
	public function boot(): self {
		if ( $this->is_booted ) {
			return $this;
		}

		if ( empty( $this->container ) ) {
			$this->preBoot();
		}

		foreach ( $this->bundles as $bundle ) {
			$bundle->setContainer( $this->container );
			$bundle->boot();
		}

		$this->is_booted = true;

		return $this;
	}

	/**
	 * Pre boot the kernel
	 *
	 * @return self
	 */
	private function preBoot(): self {
		$this
			->initializeBundles()
			->initializeContainer();
		return $this;
	}

	/**
	 * Initializes bundles.
	 *
	 * @throws \LogicException If two bundles share a common name.
	 * @return self
	 */
	protected function initializeBundles(): self {
		if ( ! empty( $this->servicesPath() ) ) {
			return $this;
		}

		$this->bundles = array();

		foreach ( $this->registerBundles() as $bundle ) {
			$name = $bundle->getName();

			if ( isset( $this->bundles[ $name ] ) ) {
				throw new \LogicException( sprintf( 'Trying to register two bundles with the same name "%s".', $name ) );
			}

			$this->bundles[ $name ] = $bundle;
		}

		return $this;
	}

	/**
	 * Registers the bundles to load.
	 *
	 * @return iterable<\OffsetWP\Framework\Bundle\BundleInterface>
	 */
	protected function registerBundles(): iterable {
		if ( ! is_file( $this->bundlesPath() ) ) {
			return;
		}

		$contents = require $this->bundlesPath();

		foreach ( $contents as $class => $envs ) {
			if ( $envs[ $this->environment() ] ?? $envs['all'] ?? false ) {
				yield new $class();
			}
		}
	}

	/**
	 * Initializes the service container.
	 *
	 * The built version of the service container is used when fresh, otherwise the
	 * container is built.
	 *
	 * @return self
	 */
	protected function initializeContainer(): self {
		$this->buildContainer();
		$this->container->compile();
		return $this;
	}

	/**
	 * Builds the service container.
	 *
	 * @return self
	 */
	protected function buildContainer(): self {
		$this->containerBuilder();
		$this->container->addObjectResource( $this );
		$this
			->prepareContainer()
			->registerContainerConfiguration();
		return $this;
	}

	/**
	 * Gets a new ContainerBuilder instance used to build the service container.
	 *
	 * @return self
	 */
	protected function containerBuilder(): self {
		$this->container = new ContainerBuilder();
		$this->container->getParameterBag()->add( $this->kernelParameters() );
		return $this;
	}

	/**
	 * Prepares the ContainerBuilder before it is compiled.
	 *
	 * @return self
	 */
	protected function prepareContainer(): self {
		if ( empty( $this->servicesPath() ) ) {
			foreach ( $this->bundles as $bundle ) {
				if ( $bundle instanceof Bundle ) {
					$this->container->registerExtension( $bundle->getContainerExtension() );
				}

				if ( $this->isDebug() ) {
					$this->container->addObjectResource( $bundle );
				}
			}

			foreach ( $this->bundles as $bundle ) {
				$bundle->build( $this->container );
			}
		}

		$this->build( $this->container );

		$extensions = array();

		foreach ( $this->container->getExtensions() as $extension ) {
			$extensions[] = $extension->getAlias();
		}

		// ensure these extensions are implicitly loaded.
		$this->container->getCompilerPassConfig()->setMergePass( new MergeExtensionConfigurationPass( $extensions ) );

		return $this;
	}

	/**
	 * Returns a loader for the container.
	 *
	 * @return DelegatingLoader A loader instance.
	 */
	protected function containerLoader(): DelegatingLoader {
		$locator  = new FileLocator( $this->configPath() );
		$resolver = new LoaderResolver(
			array(
				new YamlFileLoader( $this->container(), $locator, $this->environment() ),
				new PhpFileLoader( $this->container(), $locator, $this->environment() ),
				new GlobFileLoader( $this->container(), $locator, $this->environment() ),
				new DirectoryLoader( $this->container(), $locator, $this->environment() ),
				new ClosureLoader( $this->container(), $this->environment() ),
			)
		);

		return new DelegatingLoader( $resolver );
	}

	/**
	 * Registers the container configuration.
	 *
	 * @return self
	 */
	protected function registerContainerConfiguration(): self {
		$loader = $this->containerLoader();

		if ( ! empty( $this->servicesPath() ) ) {
			$loader->import( $this->servicesPath() );
			return $this;
		}

		$config_path = preg_replace( '{/config$}', '/{config}', $this->configPath() );

		$loader->import( $config_path . '/{packages}/*.{yaml,php}', 'glob' );
		$loader->import( $config_path . '/{packages}/' . $this->environment() . '/*.{yaml,php}', 'glob' );

		$loader->import( $config_path . '/{services}.{yaml,php}', 'glob' );
		$loader->import( $config_path . '/{services}_' . $this->environment() . '.{yaml,php}', 'glob' );

		foreach ( $this->bundles as $bundle ) {
			if ( ! $bundle instanceof Bundle ) {
				continue;
			}

			$alias = $bundle->getContainerExtension()->getAlias();

			if ( ! $this->container->hasExtension( $alias ) ) {
				continue;
			}

			if ( empty( $this->container->getExtensionConfig( $alias ) ) ) {
				$this->container->loadFromExtension( $alias, array() );
			}
		}

		return $this;
	}

	/**
	 * Get the kernel parameters.
	 *
	 * @return array
	 */
	protected function kernelParameters(): array {
		$bundles          = array();
		$bundles_metadata = array();

		foreach ( $this->bundles as $name => $bundle ) {
			$bundles[ $name ]          = $bundle::class;
			$bundles_metadata[ $name ] = array(
				'path'      => $bundle->getPath(),
				'namespace' => $bundle->getNamespace(),
			);
		}

		return array(
			'kernel.root_path'        => realpath( $this->rootPath() ) ?: $this->rootPath(),
			'kernel.environment'      => $this->environment(),
			'kernel.is_debug'         => $this->isDebug(),
			'kernel.charset'          => $this->charset(),
			'kernel.bundles'          => $bundles,
			'kernel.bundles_metadata' => $bundles_metadata,
			'kernel.build_dir'        => realpath( $this->buildPath() ) ?: $this->buildPath(),
		);
	}
}
