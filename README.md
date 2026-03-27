![OffsetWP Framework](https://raw.githubusercontent.com/offsetwp/offsetwp.github.io/refs/heads/main/public/common/cover/cover-framework-light.png#gh-light-mode-only)
![OffsetWP Framework](https://raw.githubusercontent.com/offsetwp/offsetwp.github.io/refs/heads/main/public/common/cover/cover-framework-dark.png#gh-dark-mode-only)

<h1 align="center">
    OffsetWP Framework
</h1>

<p align="center">
	A lightweight, modular, and typed framework for building modern WordPress applications.
</p>

<br/>

- 🔌 Dependency injection — Autowiring, autoconfiguration, and service container
- 🚀 Flexible architecture — Choose between configuration-driven or standalone mode
- 🧩 Extensible by design — Build and compose reusable, isolated features with bundles
- 🪶 Lightweight kernel — Minimal core with a small, focused footprint
- ⚡️ Type-safe & modern PHP — Strict typing and modern PHP practices
- 🔄 Works everywhere — Compatible with themes, plugins, and mu-plugins
- 🛠️ Developer-friendly API — Simple helpers for accessing services, parameters, and the application container

## Installation

**requirements:**
- PHP: 8.5+

**command:**
```bash
composer require offsetwp/framework
```

## Usage

The framework can work in two modes "Configuration" and "Standalone" :

- `Configuration mode` is a Symfony-like dependency injection mode that loads a full `config/` directory (recommended for structured projects).
- `Standalone mode` is a minimal mode where you register one or a few services directly (recommended for small themes, mu-plugins, prototypes).

### Configuration mode (directory)

```php
// functions.php or my-mu-plugin.php or my-plugin.php

require_once __DIR__ . '/vendor/autoload.php';

use OffsetWP\Framework\Kernel;
use OffsetWP\Support\Env;

Kernel::configure( __DIR__ )
    ->environment( Env::type() )
    ->debug( Env::isDebug() )
    ->config( __DIR__ . '/config' )
    ->boot();
```

```php
// config/services.php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function ( ContainerConfigurator $container ): void {
	$services = $container->services();

	// Configure services
	$services
		->defaults()
		->autowire()
		->autoconfigure()
		->public();

	// Set container global variables
	$container->parameters()
		->set( 'app.name', get_bloginfo( 'name' ) )
		->set( 'app.description', get_bloginfo( 'description' ) )
		->set( 'app.url', get_site_url() );

	/**
	 * Register in container services the "App\**\*" classes from "./app/" folder
	 * And auto call the "__construct()" method
	 */
	$services
		->load( 'App\\', './app/' )
		->tag( 'kernel.autoload' );
};
```

```php
// config/bundles.php

return array(
	\OffsetWP\Bundle\DemoBundle\DemoBundle::class => array( 'all' => true ), // all environment
);
```

Configuration mode — use this when your project is organized and you want the full power of a DI container (autowiring, bundles, environment-specific config). Pass the path to a `config/` folder that contains at least:
- `services.php` or `services.yaml` — service definitions
- `bundles.php` — list of bundles to register (each bundle can add its own DI extension)
- `packages/*` — optional per-package configuration files (PHP or YAML) that are loaded per environment

The kernel will scan and import files from the `config/` directory similarly to Symfony: global `services.*`, `packages/*`, and environment-specific overrides.

#### With theme

```
my-theme/          # root
├─ config/
│  ├─ packages/
│  │  └─ demo.php  # demo bundle configuration (.php|.yaml)
│  ├─ bundles.php  # bundle register
│  └─ services.php # services register (.php|.yaml)
├─ functions.php
```

#### With MU Plugin

```
mu-plugins/
├─ my-mu-plugin/      # root
│  ├─ config/
│  │  ├─ packages/
│  │  │  └─ demo.php  # demo bundle configuration (.php|.yaml)
│  │  ├─ bundles.php  # bundle register
│  │  ├─ services.php # services register (.php|.yaml)
├─ my-mu-plugin.php
```

#### With plugin

```
my-plugin/         # root
├─ config/
│  ├─ packages/
│  │  └─ demo.php  # demo bundle configuration (.php|.yaml)
│  ├─ bundles.php  # bundle register
│  └─ services.php # services register (.php|.yaml)
├─ my-plugin.php
```

### Standalone (PHP/YAML) mode

```php
// functions.php or my-mu-plugin.php or my-plugin.php

require_once __DIR__ . '/vendor/autoload.php';

use OffsetWP\Framework\Kernel;
use OffsetWP\Support\Env;

$kernel = Kernel::configure( __DIR__ )
    ->environment( Env::type() )
    ->debug( Env::isDebug() )
    ->services( __DIR__ . '/services.php' )
    ->boot();

// Get and use "MyService" instance
$my_service = $kernel->service( App\Service\MyService::class );
```

Standalone mode — use `->services( $file )` when you only need a few services and do not want the kernel to scan bundles or `packages/`. The single `services.php` file can use the Symfony dependency injection PHP configurator API (ContainerConfigurator) and behaves like a regular `services.php` but without bundle discovery.

```
my-theme/
├─ services.php  # services register (.php|.yaml)
├─ functions.php
```

## Extend

You can extend the `OffsetWP\Framework\Kernel` class to better organize your projects, for example:

- `Application`: for the root of your project
- `MyPlugin`: to better organize your plugin
- `MyTheme`: to create a modern theme

```php
// app/Application.php

use OffsetWP\Framework\Kernel;
use OffsetWP\Support\Env;

final class Application extends Kernel {
	protected string $environment   = Env::DEVELOPMENT;
	protected bool $is_debug        = true;
	protected string $services_path = __DIR__ . '/services.php';
}

new Application( __DIR__ )->boot();
```

## Methods

### Kernel

```php
use OffsetWP\Framework\Kernel;

$kernel = Kernel::configure( __DIR__ )
	->services( __DIR__ . '/services.php' )
	->boot();

// Set and get instances
app( 'app', $kernel ); // Register the application instance
echo app()->environment(); // Get the application instance and display the environment type

instance( MyTheme::class, new MyTheme() ); // Register a instance
instance( MyTheme::class )->doSomething(); // Get a instance

// Get service
app()->service( 'myservice' )->doSomething(); // with alias
app()->service( \App\Service\MyService::class )->doSomething(); // with classname
app()->hasService( 'myservice' );

// Get parameter
app()->parameter( 'kernel.root_path' );
app()->hasParameter( 'kernel.is_debug' );
```

### Env

```php
use OffsetWP\Support\Env;

// Basic
Env::has( 'DB_HOST' ); // Check if a environment variable exist
Env::raw( 'DB_HOST' ); // Get raw environment variable
Env::get( 'MY_VARIABLE' ); // Get casted environment variable (null, string, integer, float, boolean, array, json)
Env::get( 'MY_VARIABLE', 'localhost' ); // Get variable, if not exist, set a default value

// Casted
Env::string( 'DB_HOST' );
Env::integer( 'WP_POST_REVISIONS' );
Env::float( 'WP_POST_REVISIONS' );
Env::boolean( 'WP_DEBUG' );
Env::array( 'MY_ARRAY' );
Env::array( 'MY_ARRAY', '|' ); // With custom separator
Env::json( 'MY_JSON' );

// Environment
Env::type(); // 'local', 'development', 'staging', 'production', …
Env::isLocal();
Env::isDevelopment();
Env::isStaging();
Env::isProduction();
Env::isDebug();
```

## Bundles

Bundles are small, reusable packages that can extend the kernel by registering services, compiler passes and configuration. The kernel discovers bundles listed in `config/bundles.php` and, when using configuration mode, will register each bundle's container extension so it can load bundle-specific configuration from `config/packages/*`.

There are two common bundle flavors:

- Simple bundle — no configuration required, just a class that can register services or hooks in `boot()`.
- Configurable bundle — exposes configuration and a DI extension so the host application can configure the bundle via `config/packages/{bundle}.php` or `config/packages/{env}/{bundle}.php`.

### Creating a simple bundle

```
demo-bundle/
├─ src/
│  └─ DemoBundle.php
└─ composer.json
```

```php
// src/DemoBundle.php

namespace JohnDoe\Bundle\DemoBundle;

use OffsetWP\Framework\Bundle\Bundle;

final class DemoBundle extends Bundle {
	public function boot(): void {
		// register hooks or perform runtime initialization
	}
}
```

`composer.json`:

```json
{
	"name": "johndoe/demo-bundle",
	"type": "library",
	"autoload": {
		"psr-4": {
			"JohnDoe\\Bundle\\DemoBundle\\": "src/"
		}
	},
	"minimum-stability": "stable",
	"prefer-stable": true,
	"require-dev": {
		"offsetwp/framework": ">=1.0"
	}
}
```

Register the bundle in the application `config/bundles.php`:

```php
return array(
	\JohnDoe\Bundle\DemoBundle\DemoBundle::class => array( 'all' => true ),
);
```

### Creating a configurable bundle

Here is a minimal example showing how to create a configurable bundle that queries the GitHub API using Guzzle.

1) Create the `GithubBundle` bundle (namespace `JohnDoe\\Bundle\\GithubBundle`). The bundle exposes three configurable options:

- `enabled` (bool): Enable/disable the bundle
- `api_base_url` (string): base URL for requests to the GitHub API
- `token` (string|null): personal GitHub token (optional)

`composer.json` :
```json
{
  "name": "johndoe/github-bundle",
  "type": "library",
  "autoload": {
      "psr-4": {
          "JohnDoe\\Bundle\\GithubBundle\\": "src/"
      }
  },
  "require": {
      "guzzlehttp/guzzle": "^7.0 || ^8.0"
  },
  "require-dev": {
    "offsetwp/framework": ">=1.0"
  },
  "minimum-stability": "stable",
  "prefer-stable": true
}
```

```php
// src/GithubBundle.php

namespace JohnDoe\Bundle\GithubBundle;

use OffsetWP\Framework\Bundle\Bundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class GithubBundle extends Bundle {
	public function configure( DefinitionConfigurator $definition ): void {
		/**
		 * The bundle config definition
		 *
		 * @var ArrayNodeDefinition $root
		 */
		$root = $definition->rootNode();
		$root
			->children()
				->booleanNode( 'enabled' )->defaultTrue()->end()
				->scalarNode( 'api_base_url' )->defaultValue( 'https://api.github.com' )->end()
				->scalarNode( 'token' )->defaultNull()->end()
			->end();
	}

	public function loadExtension( array $config, ContainerConfigurator $container, ContainerBuilder $builder ): void {
		if ( ! $config['enabled'] ) {
			return;
		}

		$builder->setParameter( 'github.enabled', $config['enabled'] );
		$builder->setParameter( 'github.api_base_url', $config['api_base_url'] );
		$builder->setParameter( 'github.token', $config['token'] );

		$container->import( __DIR__ . '/Resources/config/services.php' );
	}
}
```

2) Example of a main service `GithubClient` (uses `guzzlehttp/guzzle`):

```php
// src/Service/GithubClient.php

namespace JohnDoe\Bundle\GithubBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

final class GithubClient {
	private ClientInterface $http;

	public function __construct( private string $base_url, private ?string $token = null ) {
		$headers = array();

		if ( $this->token ) {
			$headers['Authorization'] = 'token ' . $this->token;
			$headers['Accept']        = 'application/vnd.github.v3+json';
		}

		$this->http = new Client(
			array(
				'base_uri' => $this->base_url,
				'headers'  => $headers,
			)
		);
	}

	public function repoInfo( string $owner, string $repo ): array {
		$response = $this->http->request( 'GET', sprintf( '/repos/%s/%s', $owner, $repo ) );
		$content  = $response->getBody()->getContents();

		return json_decode( $content, true ) ?: array();
	}
}
```

3) The `services.php` file in the bundle imports the settings provided by the configuration:

```php
// src/Resources/config/services.php

use JohnDoe\Bundle\GithubBundle\Service\GithubClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function ( ContainerConfigurator $configurator ) {
	$services = $configurator->services();

	$services->set( GithubClient::class )
		->arg( '$base_url', '%github.api_base_url%' )
		->arg( '$token', '%github.token%' )
		->alias( 'github', GithubClient::class ) // create a service alias.
		->public();
};
```

4) Save the bundle in `config/bundles.php`:

```php
// config/bundles.php

return array(
	\JohnDoe\Bundle\GithubBundle\GithubBundle::class => array( 'all' => true ),
);
```

5) Configure the bundle in `config/packages/github.php` (or `packages/`, depending on your organization):

```php
// config/packages/github.php

use JohnDoe\Bundle\GithubBundle\Service\GithubClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function ( ContainerConfigurator $configurator ) {
	$services = $configurator->services();

	$services->set( GithubClient::class )
		->arg( '$base_url', '%github.api_base_url%' )
		->arg( '$token', '%github.token%' ); // change with your generated Github API token
};
```

6) Using it in the application: Retrieve the `JohnDoe\\Bundle\\GithubBundle\\Service\\GithubClient` service from the container and call `repoInfo()`.

```php
$github = app()->service( 'github' ); // find service from alias

var_dump( $github->repoInfo( 'offsetwp', 'framework' ) );
```
