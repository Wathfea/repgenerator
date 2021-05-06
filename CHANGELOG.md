# Changelog

All notable changes to `repgenerator` will be documented in this file

## 1.1.2 - 2021-05-06
- Upgrade to PHP 8
- Added searching options for filtering

## 1.1.1 - 2021-05-03

- Refactored filtering, for better DI separation
- Removed DataTable depedency
- Fixed wrong namespace in requests, 
- Fixed destroy method in abstract eloquent repository

## 1.1.0 - 2021-04-28

- Added missing BaseModel
- Fixed api route use missing
- Removed Requests under Domain

## 1.0.9 - 2021-04-28

- Refactored pattern generation

## 1.0.8 - 2021-04-27

- New pattern generation signature `pattern:generate
  {name : Class (singular) for example User}
  {--model : Whether the generator should generate a model}`

## 1.0.7 - 2021-04-27

- Added optional model generation flag

## 1.0.6 - 2021-04-23

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
