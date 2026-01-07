# Changelog

All notable changes to `valuestore` will be documented in this file

## 2.0.0 - 2025-01-07

### 🚀 Major Release - PHP 8.2+ Migration

#### Breaking Changes
- **Minimum PHP version increased from 7.2 to 8.2**
- PHPUnit upgraded from 8.x to 11.x

#### Added
- ✨ Typed properties for all class properties
- ✨ Return type declarations for all methods
- ✨ Union types support (`string|array`)
- ✨ PHP 8 attributes for tests (`#[Test]`)
- ✨ `static` return type for fluent methods
- ✨ `mixed` type for flexible parameters
- ✨ Modern PHP 8 functions (`str_starts_with()`)

#### Changed
- 🔧 Replaced PHPDoc type hints with native PHP types
- 🔧 Updated all test methods to use `#[Test]` attribute instead of `/** @test */`
- 🔧 Improved code consistency with strict comparisons
- 🔧 Updated `assertFileNotExists()` to `assertFileDoesNotExist()` (PHPUnit 11)
- 🔧 All test methods now have `: void` return type

#### Improved
- ⚡ Better performance with PHP 8.2+ JIT compiler
- 🛡️ Enhanced type safety throughout the codebase
- 📝 Cleaner and more maintainable code
- 🔍 Better IDE support and autocomplete
- 🎯 More precise error messages

#### Documentation
- 📚 Added `MIGRATION_SUMMARY.md` - Thai language migration summary
- 📚 Added `PHP8_UPGRADE_GUIDE.md` - Comprehensive upgrade guide
- 📚 Added `check-php-version.php` - System compatibility checker

#### Technical Details
- All methods now use proper type hints
- Constructor property promotion ready (can be applied if needed)
- Null coalescing operator used consistently
- Removed unnecessary PHPDoc blocks where types are declared
- Modern PHP 8 syntax throughout

#### Migration Guide
See `PHP8_UPGRADE_GUIDE.md` for detailed migration instructions.

**Note**: This is a breaking change. If you need PHP 7.x support, please use version 1.x

---

## 1.2.4 - 2020-08-23

- `all` will now always return an array (#23)

## 1.2.3 - 2019-02-15

- update deps

## 1.2.2 - 2018-05-15

- small performance improvement

## 1.2.1 - 2017-10-10

- avoid writing to the json file when putting an empty array

## 1.2.0 - 2017-09-29

- add second parameter to `make`

## 1.1.0 - 2017-01-20

- add `prepend` method

## 1.0.0 - 2016-04-06

- initial release
