# Extensions

Other packages can extend `composer diff` in three ways:

- [Custom formatters](#custom-formatters) add new values for the `--format` option.
- [Custom URL generators](#custom-url-generators) add compare, release and project links for more hosting services.
- [The `post-composer-diff` event](#post-composer-diff-event) lets you filter the results, change them, or fail the command before the report is rendered.

Extensions only work when the command runs as `composer diff`. The standalone `composer-diff` binary does not load Composer plugins or scripts.

## Creating an extension plugin

Formatters and URL generators are registered by a Composer plugin, using the same capability system Composer uses for plugin commands.
The plugin package needs the `composer-plugin` type and a dependency on this package:

```json
{
    "name": "acme/composer-diff-extras",
    "type": "composer-plugin",
    "require": {
        "composer-plugin-api": "^2.0",
        "ion-bazan/composer-diff": "^2.3"
    },
    "autoload": {
        "psr-4": {
            "Acme\\ComposerDiffExtras\\": "src/"
        }
    },
    "extra": {
        "class": "Acme\\ComposerDiffExtras\\Plugin"
    }
}
```

Extension support is available from the first release after `v2.2.1`.

The plugin class lists the capabilities it provides:

```php
namespace Acme\ComposerDiffExtras;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use IonBazan\ComposerDiff\Formatter\FormatterProvider;
use IonBazan\ComposerDiff\Url\UrlGeneratorProvider;

class Plugin implements PluginInterface, Capable
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public function getCapabilities(): array
    {
        return [
            FormatterProvider::class => AcmeFormatterProvider::class,
            UrlGeneratorProvider::class => AcmeUrlGeneratorProvider::class,
        ];
    }
}
```

Composer creates provider classes with a single array argument containing `composer` (the `Composer\Composer` instance),
`io` (the `Composer\IO\IOInterface` instance) and `plugin` (your plugin instance).

Install the extension next to `ion-bazan/composer-diff`, globally or in the project, and allow it to run:

```shell script
composer config allow-plugins.acme/composer-diff-extras true
composer require --dev acme/composer-diff-extras
```

To keep an extension inside a project without publishing it, put it in a subdirectory and install it from a
[path repository](https://getcomposer.org/doc/05-repositories.md#path).

## Custom formatters

Implement `IonBazan\ComposerDiff\Formatter\FormatterProvider` and return your formatters keyed by format name:

```php
namespace Acme\ComposerDiffExtras;

use IonBazan\ComposerDiff\Formatter\FormatterProvider;
use Symfony\Component\Console\Output\OutputInterface;

class AcmeFormatterProvider implements FormatterProvider
{
    /**
     * @param array<string, mixed> $args
     */
    public function __construct(array $args)
    {
    }

    public function getFormatters(OutputInterface $output): array
    {
        return [
            'csv' => new CsvFormatter($output),
        ];
    }
}
```

Each formatter implements `IonBazan\ComposerDiff\Formatter\Formatter`. Extending `IonBazan\ComposerDiff\Formatter\AbstractFormatter` gives you the output and a helper for linked package names:

```php
namespace Acme\ComposerDiffExtras;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\AbstractFormatter;

class CsvFormatter extends AbstractFormatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls, bool $withLicenses): void
    {
        $this->renderSingle($prodEntries, 'prod', $withUrls, $withLicenses);
        $this->renderSingle($devEntries, 'dev', $withUrls, $withLicenses);
    }

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls, bool $withLicenses): void
    {
        /** @var DiffEntry $entry */
        foreach ($entries as $entry) {
            $this->output->writeln(implode(',', [$title, $entry->getPackageName(), $entry->getType(), $entry->getBaseVersion(), $entry->getTargetVersion()]));
        }
    }
}
```

The format is then available like any built-in one:

```shell script
composer diff --format=csv
```

A format name can only be registered once. Registering a name that is already taken, either by a built-in formatter
or by another extension, stops the command with an error.

## Custom URL generators

Implement `IonBazan\ComposerDiff\Url\UrlGeneratorProvider` and return a list of generators:

```php
namespace Acme\ComposerDiffExtras;

use IonBazan\ComposerDiff\Url\UrlGeneratorProvider;

class AcmeUrlGeneratorProvider implements UrlGeneratorProvider
{
    /**
     * @param array<string, mixed> $args
     */
    public function __construct(array $args)
    {
    }

    public function getUrlGenerators(): array
    {
        return [new GiteaGenerator('git.acme.com')];
    }
}
```

Each generator implements `IonBazan\ComposerDiff\Url\UrlGenerator`, described in [URL generators](url-generators.md).
For Git hosting services, extending `IonBazan\ComposerDiff\Url\GitGenerator` handles domain matching and repository URL parsing:

```php
namespace Acme\ComposerDiffExtras;

use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Url\GitGenerator;

class GiteaGenerator extends GitGenerator
{
    /** @var string */
    private $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    protected function getDomain(): string
    {
        return $this->domain;
    }

    public function getCompareUrl(PackageInterface $initialPackage, PackageInterface $targetPackage): ?string
    {
        return sprintf('%s/compare/%s...%s', $this->getRepositoryUrl($targetPackage), $this->getCompareRef($initialPackage), $this->getCompareRef($targetPackage));
    }

    public function getReleaseUrl(PackageInterface $package): ?string
    {
        return $package->isDev() ? null : sprintf('%s/releases/tag/%s', $this->getRepositoryUrl($package), $package->getPrettyVersion());
    }

    public function getProjectUrl(PackageInterface $package): ?string
    {
        return $this->getRepositoryUrl($package);
    }
}
```

Generators from extensions are checked before the built-in ones, so an extension can take over packages that a built-in
generator would also match. The first generator whose `supportsPackage()` returns `true` is used.

## post-composer-diff event

After the diff is computed, filtered and sorted, and before the report is rendered, the command dispatches the
`post-composer-diff` event through Composer's event dispatcher. The event object is
`IonBazan\ComposerDiff\Event\PostDiffEvent`, which extends `Composer\Script\Event`, so listeners also get
`getComposer()` and `getIO()`.

| Method                                     | Description                                                              |
|--------------------------------------------|--------------------------------------------------------------------------|
| `getProdEntries()` / `setProdEntries()`    | Read or replace the prod package changes that will be rendered.          |
| `getDevEntries()` / `setDevEntries()`      | Read or replace the dev package changes that will be rendered.           |
| `getExitCode()` / `setExitCode()`          | Read or set an exit code. It is combined (bitwise OR) with the `--strict` exit code. |

The `--strict` exit code is calculated from the entries after all listeners have run.
If you set your own exit code, use `1` or values from `32` upwards so it does not overlap with the
[strict mode flags](../README.md#strict-mode).

### Listening from the project's composer.json

A project can listen to the event without writing a plugin, using a static method from its own autoloaded code:

```json
{
    "scripts": {
        "post-composer-diff": "App\\Composer\\DiffPolicy::check"
    }
}
```

```php
namespace App\Composer;

use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Event\PostDiffEvent;

class DiffPolicy
{
    public static function check(PostDiffEvent $event): void
    {
        $event->setProdEntries($event->getProdEntries()->matching(['acme/*']));

        /** @var DiffEntry $entry */
        foreach ($event->getProdEntries() as $entry) {
            if ($entry->isDowngrade()) {
                $event->getIO()->writeError(sprintf('<error>%s must not be downgraded</error>', $entry->getPackageName()));
                $event->setExitCode(32);
            }
        }
    }
}
```

Composer also exposes every custom script as a command, so `composer post-composer-diff` exists too. Running it
directly passes a plain `Composer\Script\Event` instead of `PostDiffEvent`, so the listener is only meant to run through `composer diff`.

### Listening from a plugin

A plugin can subscribe to the event like any other Composer event:

```php
namespace Acme\ComposerDiffExtras;

use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Plugin\PluginInterface;
use IonBazan\ComposerDiff\Event\PostDiffEvent;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    // activate(), deactivate() and uninstall() omitted

    public static function getSubscribedEvents(): array
    {
        return [PostDiffEvent::NAME => 'onPostDiff'];
    }

    public function onPostDiff(PostDiffEvent $event): void
    {
        $event->setDevEntries($event->getDevEntries()->matching(['acme/*']));
    }
}
```

## Public API

Extensions can rely on these classes and interfaces. Their behaviour only changes in a major release:

- `IonBazan\ComposerDiff\Formatter\FormatterProvider`, `Formatter` and `AbstractFormatter`
- `IonBazan\ComposerDiff\Url\UrlGeneratorProvider`, `UrlGenerator` and `GitGenerator`
- `IonBazan\ComposerDiff\Event\PostDiffEvent`
- `IonBazan\ComposerDiff\Diff\DiffEntries` and `DiffEntry`

Other classes, such as the command and the formatter and URL generator containers, are internal and may change in any release.
