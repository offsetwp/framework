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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * BundleInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface BundleInterface {
	/**
	 * Boots the Bundle.
	 *
	 * @return void
	 */
	public function boot(): void;

	/**
	 * Shutdowns the Bundle.
	 *
	 * @return void
	 */
	public function shutdown(): void;

	/**
	 * Builds the bundle.
	 * It is only ever called once when the cache is empty.
	 *
	 * @param ContainerBuilder $container The service container.
	 * @return void
	 */
	public function build( ContainerBuilder $container ): void;

	/**
	 * Returns the bundle name (the class short name).
	 */
	public function getName(): string;

	/**
	 * Gets the Bundle namespace.
	 */
	public function getNamespace(): string;

	/**
	 * Gets the Bundle directory path.
	 * The path should always be returned as a Unix path (with /).
	 */
	public function getPath(): string;

	/**
	 * Sets the container.
	 *
	 * @param ContainerInterface|null $container The service container.
	 * @return void
	 */
	public function setContainer( ?ContainerInterface $container ): void;
}
