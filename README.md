# PHP Composter PHPMND 

![standard-readme compliant](https://img.shields.io/badge/standard--readme-OK-green.svg?style=flat-square)

Automatically install a git pre-commit hook to check your PHP files upon each commit to scan for magic numbers. 

## Table Of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Built With](#built-with)
- [Contributing](#contributing)
- [Versioning](#versioning)
- [Authors](#authors)
- [License](#license)

## Installation

Add as a development requirement to your `composer.json`:

```bash
composer require --dev pascal-splotches/php-composter-phpmnd
```

## Usage

It automatically works whenever you execute a `git commit`.

## Built With

- [Composter](https://github.com/php-composter/php-composter)
- [PHP Magic Number Detector](https://github.com/povils/phpmnd)

## Contributing

All feedback, bug reports and pull requests are welcomed.

## Versioning

We use [SemVer](https://semver.org/) for versioning. For the versions available, see the [releases on this repository](https://github.com/pascal-splotches/php-composter-phpmd/releases).

## Authors

- Pascal Scheepers <pascal@splotch.es>

## License

This project is licensed under the GPL v3 License - see the [LICENSE](./LICENSE) file for details.
