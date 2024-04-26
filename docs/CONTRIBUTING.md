# Contributing guidelines

Any contributions (issues, pull requests, code review, ideas, etc.) are welcome. 
Here are a few guidelines to be aware of:

- Include tests for any new features or bug fixes.
- All new features should target the `main` branch.
- All code must follow the project coding style standards which will be enforced by [StyleCI](https://styleci.io/).

Since this project has rather strict coding standards, which include:
  - PHPStan level 6
  - 100% test coverage
  - 100% MSI (Mutation Score Indicator)
  - Compatibility with PHP 5.3.2 up to 8.0 and newer

It might be a bit challenging to get started but fret not, 
as the maintainers will be happy to assist you should you have any questions or need help with your contribution.
Even if the tests fail, don't worry, as the maintainers will help you fix them.

## Getting started

1. Fork the repository on GitHub.
2. Clone your fork locally.
3. Run `composer install` to install the dependencies.
4. Create a new branch for your feature or bug fix.
5. Write code and tests for your new feature or bug fix.
6. Run the tests (`vendor/bin/simple-phpunit`) to be sure everything is working.
7. Push your branch to your fork on GitHub.
8. Create a pull request to the `main` branch.
9. Wait for the maintainers to review your pull request.

## Testing against a real project

Sometimes it's easier to check your changes when you have an actual project where a bug occurs rather than reproducing it in a test.
While this package works as a Composer plugin, it might seem difficult to test your changes against a real project.

Consider a following example:

- `~/work/my-project` - path to my project
- `~/work/composer-diff` - path to this repository

Running the `composer-diff` command with your changes against your project is as simple as:

```shell
cd ~/work/my-project # Navigate to your project directory
~/work/composer-diff/composer-diff # Run the composer-diff command from this repository
```

You can specify any other options as well like `--no-dev`, `--with-platform`, etc.
