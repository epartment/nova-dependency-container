# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.4] - 2023-24-07

### Changed

- Added .gitattributes file to reduce vendor size

## [2.0.3] - 2022-19-12

### Changed

- Fix error in FormField.fill()

## [2.0.2] - 2022-26-09

### Changed

- Fix crash when attempting to fill unfillable fields.

## [2.0.1] - 2022-02-09

### Changed

- Fixed crash when used with Actions

## [2.0.0] - 2022-19-08

### Added

- Nova 4 support

## [1.0.1] - 2021-09-07

### Changed

- Fixed DependencyContainer inside Action picking up fields outside the modal and using wrong initial value

## [1.0.0] - 2021-09-07

Initial release.
Forked from [epartment/nova-dependency-container](https://github.com/epartment/nova-dependency-container) version 1.3.3.

### Changed

- Use `data_get` to access resource properties to support array/object values
- Updated dependencies
- Updated packages
