<?php
/**
 * OffsetWP Framework
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Framework\Configuration
 */

declare( strict_types=1 );

namespace OffsetWP\Framework\Configuration;

use OffsetWP\Framework\Kernel;
use OffsetWP\Support\Env;

/**
 * KernelBuilder
 */
class KernelBuilder {
	/**
	 * The root path
	 *
	 * @var string The root path.
	 */
	private string $root_path = '';

	/**
	 * Environment type
	 *
	 * @var string
	 */
	private string $environment = Env::PRODUCTION;

	/**
	 * Charset
	 *
	 * @var string
	 */
	private string $charset = 'UTF-8';

	/**
	 * Is debugging
	 *
	 * @var bool
	 */
	private bool $is_debug = false;

	/**
	 * Dependency Injection config path
	 *
	 * @var string
	 */
	private string $config_path = '';

	/**
	 * Dependency Injection standalone service file
	 *
	 * @var string
	 */
	private string $services_file = '';

	/**
	 * The kernel
	 *
	 * @param string $root_path The root path.
	 * @return void
	 */
	public function __construct( string $root_path ) {
		$this->rootPath( $root_path );
	}

	/**
	 * Set root path
	 *
	 * @param string $root_path  The root path.
	 * @throws \RuntimeException The path of root folder does not exist.
	 * @return self
	 */
	public function rootPath( string $root_path ): self {
		if ( empty( $root_path ) || ! is_dir( $root_path ) ) {
			throw new \RuntimeException( 'The path of root folder does not exist.' );
		}
		$this->root_path = $root_path;
		return $this;
	}

	/**
	 * Set environment
	 *
	 * @param string $environment The environment type.
	 * @return self
	 */
	public function environment( string $environment ): self {
		$this->environment = $environment;
		return $this;
	}

	/**
	 * Set charset
	 *
	 * @param string $charset The charset.
	 * @return self
	 */
	public function charset( string $charset ): self {
		$this->charset = $charset;
		return $this;
	}

	/**
	 * Set debug
	 *
	 * @param bool $is_debug Debug is enable or not.
	 * @return self
	 */
	public function debug( bool $is_debug ): self {
		$this->is_debug = $is_debug;
		return $this;
	}

	/**
	 * Set services file for standalone configuration
	 * Use this method only if you want to work only with services from your project and you are not using bundles.
	 *
	 * @param string $services_file The services file path.
	 * @return self
	 */
	public function services( string $services_file ): self {
		$this->services_file = $services_file;
		return $this;
	}

	/**
	 * Set config path for configuration files
	 * The config path should contain the configuration files for the container, such as services.yaml, parameters.yaml, etc.
	 *
	 * @param string $config_path The config path.
	 * @return self
	 */
	public function config( string $config_path ): self {
		$this->config_path = $config_path;
		return $this;
	}

	/**
	 * Build the kernel
	 *
	 * @return Kernel
	 */
	private function build(): Kernel {
		$kernel = new Kernel( $this->root_path );
		$kernel
			->setEnvironment( $this->environment )
			->setCharset( $this->charset )
			->setDebug( $this->is_debug );

		if ( $this->services_file ) {
			$kernel->setServicesPath( $this->services_file );
		} elseif ( $this->config_path ) {
			$kernel->setConfigPath( $this->config_path );
		}

		return $kernel;
	}

	/**
	 * Build and boot the kernel
	 *
	 * @return Kernel
	 */
	public function boot(): Kernel {
		return $this->build()->boot();
	}
}
