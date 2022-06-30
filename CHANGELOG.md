# Changelog

All notable changes to `repgenerator` will be documented in this file

## [TODO]
- Factory and seeder generation for CRUD
- Composite index generation
- How it works section
- CRUD UI in other Frontend frameworks
- CSV import


## [Unreleased]


- CRUD create
- CRUD edit
- CRUD delete
- CRUD search

## [1.3.4] - 2022-06-30
### Fixed
- Namespace typeing error fixed

## [1.3.3] - 2022-06-30
### Fixed
- Values type error for column adapter

## [1.3.2] - 2022-06-29
### Fixed
- Error during index generation
- Enum generation
## Added
- Search to listing page
- Migration automatic run
- CRUD create
- CRUD edit
- CRUD delete
- CRUD search

## [1.3.1] - 2022-06-23
### Fixed
- CRUDController now returns the proper unwrapped json data

## [1.3.0] - 2022-06-23
## Added
- In the final result list somehow we should outline the things to still do: "Add this line to your ...."
- The review step should scroll at the top and show this is a review phase
### Fixed
- Fixed routing issue
## Removed
- Remove Generate model selection it is a redundant question we always need to generate model

## [1.2.9] - 2022-06-22
### Fixed
- Fixed factory generation error, removed not yet finished method call from live code base

## [1.2.8] - 2022-06-21
### Fixed
- Fixed install command error

## [1.2.7] - 2022-06-21
### Added
- Added install command

## [1.2.6] - 2022-06-21
### Fixed
- Fixed typo in composer.json

## [1.2.5] - 2022-06-21
### Added
- Cleanup code structure into DDD
- Column types is now an enum
- Start adding tests
- Start working on factory generation

## [1.2.4] - 2022-06-21
### Added
- Added migration generation
- Added CRUD generation
### Fixed
- Refactored domain static layer

## [1.2.3] - 2021-12-22
### Added
- Added basic index method for listing
### Fixed
- Fixed wrong file paths,

## [1.2.2] - 2021-12-22
### Added
- Added missing Model and Api Controller path check
- Added new blank view template
- Added proper path for uses
### Fixed
- Fixed wrong file paths,
- Fixed file exists bug
- Refactored view insert
- Fixed update method response type
- Fixed wrong interface use
### Removed
- Removed unused usese
- Removed auth middleware from routes

## [1.2.1] - 2021-12-22
- Small fixes 

## [1.2.0] - 2021-12-21
- Added repository check
- Added name check
- Fixed QeeryFilter.stub wrong use statement

## [1.1.9] - 2021-12-21
- Migration updated
- Fixed missing service interface bug

## [1.1.8] - 2021-12-21
- Base migration creation added

## [1.1.7] - 2021-12-21
- Printed console text fix, replaced slashes

## [1.1.6] - 2021-12-21
- Directory creation bug fixed

## [1.1.5] - 2021-12-21
- Added more abstract separation
- Fixed some small bugs
- Refactored route handling

## [1.1.4] - 2021-05-31
- Added more abstract separation
- Fixed some small bugs

## [1.1.3] - 2021-05-18
- Missing use from web route added
- AbstractEloquentrepository now uses pivot and relations
- Api controller http header status codes added
- BaseQueryFilter refactored, added default id search for all Resource
- BaseTransactionController updated
- Return types changed for better separation

## [1.1.2] - 2021-05-06
- Upgrade to PHP 8
- Added searching options for filtering

## [1.1.1] - 2021-05-03

- Refactored filtering, for better DI separation
- Removed DataTable depedency
- Fixed wrong namespace in requests, 
- Fixed destroy method in abstract eloquent repository

## [1.1.0]- 2021-04-28

- Added missing BaseModel
- Fixed api route use missing
- Removed Requests under Domain

## [1.0.9] - 2021-04-28

- Refactored pattern generation

## [1.0.8] - 2021-04-27

- New pattern generation signature `pattern:generate
  {name : Class (singular) for example User}
  {--model : Whether the generator should generate a model}`

## [1.0.7] - 2021-04-27

- Added optional model generation flag

## [1.0.6] - 2021-04-23

- Fixed return types

## 1.0.5 - 2021-04-23

- Refactor Stubs

## 1.0.4 - 2021-04-23

- Typo fixings

## 1.0.3 - 2021-04-23

- Typo fixing in Service.stub update method, now the good repository method is called
- Refactor API Controller

## 1.0.2 - 2021-03-04

- If Web directory not represented in the Controllers directory we got a permission error. Web directory creation fixed

## 1.0.1 - 2021-03-01

- file_get_contentst() BUG Fixed in PatternGenerator.php

## 1.0.0 - 2021-02-25

- initial release
