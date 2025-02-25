# Contributing

Thank you for considering to contribute to Socialstream - we try and welcome everyone's ideas to improve the package. We just ask that you take a couple of minutes to carefully read through this contribution guide before you start making your changes.

## Coding Style

Like Laravel, Socialstream tries to keep to the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding style and [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) autoloading standard.

### PHPDoc

Below is an example of a valid documentation block. Where possible, please fully type your method arguments and return types.

```php
/**
 * Register a binding with the container.
 *
 * @throws \Exception
 */
public function bind(string $abstract, callable|string|null $concrete = null, bool $shared = false): void
{
    //
}
```

### Code Style

Don't worry if your code styling isn't perfect! Code styling will be resolved when your PR is merged into `main`.

## Creating Pull Requests

Before you create n pull request, please check through our [issue tracker](https://github.com/joelbutcher/socialstream/issues) to make sure that no one has had the same idea! If you've noticed something similar to your request, please "upvote" it so that it get's more attention from the maintainers.

When making a pull request, please make sure to outline as concisely as possible the reason for it, what benefits it brings or what it fixes - screenshots and code-snippets that support your request are **highly** encouraged. When you are ready to make your pull request, please be sure to give it a good name (PR's like 'patch-1' will be rejected). As an example we recommnend pre-fixing your request with the verion number you are targeting. e.g. `[3.x] Allow accounts to be removed`

## Testing

When making you changes or additions, we **strongly** encourage you to write tests to ensure compatibility. Make sure to check out our [existing test suite](https://github.com/joelbutcher/socialstream/tree/2.x/tests) for examples of how to do this and create equivalent tests accordingly
