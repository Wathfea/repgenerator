# Changelog

All notable changes to `repgenerator` will be documented in this file

## [TODO]
- Nuxt frontend esetén az Editben a router nem jó helyről van származtatva
- RouterLink a dashboardra
- Default gate és policy
- Menu kezelés újragondolása (bitmaszkolás)
- Filterben a relation method név nem jó
- Pivot migráció táblanév nem többesszámos
- Pivo táblába begenerálni a mezőket + relációkat
- Enum generálás
- Table.vue-ba be kell kötni az url prefixet az új és edit gombra
- Relation type selector
- public vagy nem public file választó
- Relation párok beírása
- CSV import
- Composite index generation
- How it works section
- Mezősorrend változtatása drag & drop-al

## [Unreleased]
- Verzió követés, ha módosul a generátor frontend akkor újrahúzni automatikusan
- getFirstByColumn, getAllByColumn, getFirstByColumns, getAllByColumns
- Generált factoryban az enum nincs apasztófok között
- Regenerate ugorja át a mappákat amiben nincs config.php
- Api controllerben hiányzik vessző a relation generáláskor
- Boolean mezőre ne tegyen requiredet
- Ha valaminek nincs FE generálva ne tegye be a menus táblába
- Api route generálásnál kötőjel használata a resourceban
- routes.js-ben kötőjel használata


## [1.5.5] - 2022-09-15
## Added
- CRUD Url prefix

## [1.5.4] - 2022-09-15
## Added
- Generate frontend option selector

## [1.5.3] - 2022-09-14
## Added
- Fully refactored frontend ui
- Gradient generator for tailwind config
- Factory generation for the CRUD
## Fixed
- Clear up frontend depedency tree
- Duplicated request validation
- Removed file field require from update request
- In the file relation domain layer resource the badly generated relation name now points the correct relation


## [1.5.2] - 2022-09-09
## Fixed
- Frontend generation errors


## [1.5.1] - 2022-09-09
## Added
- DIRECTORY_SEPARATOR use for multi platform usage
- Fixed api route name generation
- New frontend generation
- Lot of small tweaks

## [1.5.0] - 2022-09-01
## Added
- Model name checks
- Refactored name transformation
- Renamed route

## [1.4.9] - 2022-08-30
## Fixed 
- config load error

## [1.4.8] - 2022-08-30
## Added
- New theme added for the wizzard
- Added new config.php for each Domain for store the generation data there instead of DB
- Added exists rule to the request validation based on the relations
- When you choose any unsigned type you no longer need to check the unsigned checkbox
- Model name auto upper case for the first letter when you type
- Added a reload button for reload the database for the references selection
- Added timestamp as an option at the begining of the generation
- Added model name check
- Added auto provider 
- File store auto detect file count and change directory level based on that
- Wizzard moved under new route for avoid route collision
- In the model relations now the generator adds the local and owner keys as well

## Fixed
- In migrations the id field no longer add the 'id' name to the method as param
- Hashed, Crypted, Picture and File selectors are now radio buttons
- Now request validation is working properly
- Model names which contained space generated a wrong file relation. 
- File type request validation removed type check

## Removed
- Unused commands
- RepgeneratorDomain model
- Repgenerator Domains table base migration

## [1.4.7] - 2022-08-10
## Added
- filefeltöltéskor eredeti név elvetése új néven mentsük a filet, mime type adatok mentése a db-be


## [1.4.6] - 2022-08-10
## Fixed
- Windows compatible npm exec
- Changed migration name for no conflic at different branch publish situation

## [1.4.5] - 2022-08-09
## Added
- Hashed and Crypted fields option

## [1.4.4] - 2022-08-09
## Added
- Soft Delete option
## Fixed
- Removed enum strlower from options

## [1.4.3] - 2022-08-03
## Fixed
- Generation error

## [1.4.2] - 2022-08-03
## Fixed
- package.json typo

- ## [1.4.1] - 2022-08-03
## Fixed
- package.json typo

## [1.4.0] - 2022-08-03
## Added
- Regenerate frontend section

## [1.3.9] - 2022-08-02
## Added 
- Boolean mezők edit oldalon nem olvasódnak vissza
- Multiple file upload
- Frontend wizzard file upload refactor

## [1.3.8] - 2022-07-28
## Fixed
- package.json typo

## [1.3.7] - 2022-07-28

## Added
- Api controller index and show override on files relation
- Icon generation
- Refactored crud frontend
- Loading screen
- File upload handling and file upload at edit page
- File upload to prefixed folder
- Url inserted to resource if file upload
- File update
- Date and dateTime types are got special formatting in resource
- Reload and notification after crud actions

## Fixed
- Fixd migration time generation


## [1.3.6] - 2022-07-07

## Added

- Added key shortcut (ctrl+A) on wizzard for add new column
- Resource generation for file relation
- Store and Update request generation with validation

### Fixed

- Boolean fields save from CRUD
- We are no longer generating frontend for file relations
- In Resource there was a nameing error for the field and relation name, now it is fixed
- In Model there was a relation generation error now it is fixed
- Fixed typo in edit.vue

## [1.3.5] - 2022-07-05

## Added

- File upload
- CRUD create
- CRUD edit
- CRUD delete
- CRUD search

### Fixed

- Make sure name is transformed
    - Rules:
        - singular
        - remove space
        - not registered already
        - for migration:
            - replace space to _
            - lower case

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

- New pattern generation
  signature `pattern:generate {name : Class (singular) for example User} {--model : Whether the generator should generate a model}`

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
