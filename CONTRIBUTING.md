# Contributing to rumenx/php-assets

Thank you for your interest in contributing to rumenx/php-assets! We welcome contributions of all kinds, from bug reports to feature requests to code improvements.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to [contact@rumenx.com](mailto:contact@rumenx.com).

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to see if the problem has already been reported. When you are creating a bug report, please include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples to demonstrate the steps**
- **Describe the behavior you observed after following the steps**
- **Explain which behavior you expected to see instead and why**
- **Include your PHP version and framework details**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- **Use a clear and descriptive title**
- **Provide a step-by-step description of the suggested enhancement**
- **Provide specific examples to demonstrate the steps**
- **Describe the current behavior and explain which behavior you expected to see instead**
- **Explain why this enhancement would be useful**

### Pull Requests

- Fill in the required template
- Follow the PHP coding standards (PSR-12)
- Include thoughtful, well-structured tests
- Document new code with clear docblocks
- Make sure all tests pass
- Make sure PHPStan analysis passes

## Development Process

### Setting Up Your Development Environment

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR-USERNAME/php-assets.git`
3. Install dependencies: `composer install`
4. Create a feature branch: `git checkout -b my-feature-branch`

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
./vendor/bin/pest --coverage

# Run static analysis
composer analyze
```

### Code Standards

- Follow PSR-12 coding standards
- Use strict typing (`declare(strict_types=1)`)
- Write comprehensive docblocks for all public methods
- Include type hints for all parameters and return values
- Write tests for new functionality
- Maintain or improve test coverage

### Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

### Testing Guidelines

- Write tests for all new functionality
- Ensure edge cases are covered
- Use descriptive test names
- Follow the existing test structure using Pest

## Framework Adapters

When contributing to framework adapters:

- **Laravel**: Test against supported Laravel versions
- **Symfony**: Test against supported Symfony versions
- Ensure adapters remain lightweight and focused
- Follow framework-specific conventions

## Documentation

- Update README.md if your changes affect usage
- Update docblocks for any changed methods
- Add examples for new features
- Update CHANGELOG.md following the Keep a Changelog format

## Release Process

Releases are handled by maintainers:

1. Update CHANGELOG.md
2. Update version constraints if needed
3. Create a release tag
4. Publish to Packagist

## Questions?

Don't hesitate to ask questions! You can:

- Open an issue for discussion
- Contact the maintainer at [contact@rumenx.com](mailto:contact@rumenx.com)

## Recognition

Contributors will be recognized in the CHANGELOG.md and can be added to the package credits.

Thank you for contributing! 🎉
