# Release Notes
# Changelog

This changelog follows [the Keep a Changelog standard](https://keepachangelog.com).

## [Unreleased](https://github.com/joelbutcher/socialstream/compare/v2.0.0...2.x)

## [v2.0.1 (2021-01-08)](https://github.com/joelbutcher/socialstream/compare/v2.0.0...v2.0.1)
### Fixed
- Fixed an issue with `view:clear` throwing DirectoryNotFound (#33)

## [v2.0.0 (2021-01-06)](https://github.com/joelbutcher/socialstream/compare/v1.1.1...v2.0.0)
Initial V2 release

## [v1.1.2 (2021-01-08)](https://github.com/joelbutcher/socialstream/compare/v1.1.1...v1.1.2)
### Fixed
- Fixed an issue with `view:clear` throwing DirectoryNotFound (#33)

## [v1.1.1 (2021-01-06)](https://github.com/joelbutcher/socialstream/compare/v1.1.0...v1.1.1)

### Fixed
- Fixed config name in service provider

### Removed
- Removed resource / view publishing from service provider `boot` method

### Fixed
- Fixed `Show.vue` showing the delete account form if a password hasn't been set.

## [v1.1.0 (2020-12-28)](https://github.com/joelbutcher/socialstream/compare/v1.0.0...v1.1.0)

### Fixed
- Fixed `ActionLink.vue` to use an anchor tag rather than inertia-link to avoid CORS issue. (#22)

### Removed
- Removed the duplicate method `getAccountForProvider` in `ConnectedAccountsForm.vue` (#20)

## [v1.0.0 (2020-12-28)](https://github.com/joelbutcher/socialstream/compare/v0.0.4...v1.0.0)

## [v0.0.4 (2020-12-28)](https://github.com/joelbutcher/socialstream/compare/v0.0.3...v0.0.4)

### Fixed
- Fixed namesace of `HandleInvalidState` action

## [v0.0.3 (2020-12-27)](https://github.com/joelbutcher/socialstream/compare/v0.0.2...v0.0.3)

### Added
- Added an error message to the connected accounts components (#3)
- Added an exception handler for `Laravel\Socialite\Two\InvalidStateException` (#3)

## [v0.0.2 (2020-12-27)](https://github.com/joelbutcher/socialstream/compare/v0.0.1...v0.0.2)

### Fixed
- Fixed README typo (#1)

### Removed
- Removed .DS_Store (#2)

## v0.0.1 (2020-12-26)

Initial release. 
