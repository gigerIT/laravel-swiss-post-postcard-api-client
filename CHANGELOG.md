# Changelog

## [1.1.2](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/compare/v1.1.1...v1.1.2) (2025-09-01)


### Miscellaneous Chores

* fix CI ([53d902c](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/53d902c056969814552a28b2d866178683c6966b))

## [1.1.1](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/compare/v1.1.0...v1.1.1) (2025-09-01)


### Bug Fixes

* update README with revised Swiss Post API configuration ([b5509c8](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/b5509c8dbacc2f588156ff5d1f17e12746049681))

## [v1.1.0](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/compare/v1.1.0...v1.1.0) - 2025-08-27

### Features

- enhance PostcardMessage and PostcardChannel with branding support

## [1.1.0](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/compare/v1.0.0...v1.1.0) (2025-08-27)

### Features

* enhance PostcardMessage and PostcardChannel with branding support ([8fa609d](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/8fa609d5767982f89a37516ca14643abe38a5d19))

### Miscellaneous Chores

* add orchestra/testbench dependency ([3c3b07e](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/3c3b07e95f124d14812cad6cd0149f2234025653))
* enhance GitHub Actions workflow for release management ([3ed1c9c](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/3ed1c9c1d9f5c73935ce32a38f43614c8f607044))
* remove testbench commands ([ca4501d](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/ca4501d50c307ee81083272bb9222c5fce5bc33b))

## 1.0.0 (2025-08-27)

### Features

* add API debugging features and improve error handling in SwissPostConnector ([579206c](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/579206c5d9f23785ae1a623631978164dee9b54a))
* adds Laravel notification channel ([0cd019a](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/0cd019a679f27e36afc61baa4609e682fe3dca60))
* api client implementation ([a857641](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/a857641732577132721718a1637c7caad00a596a))
* demo-app ([70c3995](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/70c3995ca77a3f941a5dea7774885c75482ef0a0))
* **demo-app:** enhance branding options in postcard creation process ([85b8258](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/85b8258035a213cc50132b1d93bbf5a69195d6d9))
* **demo-app:** implement step-by-step postcard creation process ([cf4b578](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/cf4b578b8ce50524a37f1c26fbed8347974f943d))
* enhance Swiss Post Postcard API client with OAuth2 support and testing interface ([36a8c1d](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/36a8c1d2d333289862d14dd674df9ed1f8761c3b))
* **github:** add release automation configuration and workflow ([5ebeef7](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/5ebeef7291fae30a0de727c5147b64c4272d937e))
* implement HasBody and AcceptsJson traits in request classes for improved API handling ([b28e577](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/b28e57777ba10054f3cad35bfcc4568c3154d66f))
* implement HasBody interface in UploadImageRequest for multipart handling ([03c2639](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/03c26396b279a97f8897646736c8751bf0a69dd9))
* install saloon ([80f7c2b](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/80f7c2b766736051b4f20a29ab85fe736f5a769e))
* package configured ([0e73896](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/0e738963526da947358726f1caabb3a2b763f03a))
* **tests:** add branding and stamp images for testing ([140a2d2](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/140a2d210b3099e24cedc935f3712f4161cca7f1))

### Bug Fixes

* : oauth2 ([5ad8de2](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/5ad8de2bcda6960f8d5d6470da53c115b4056c56))
* **demo-app:** update request parameter name for adding text to postcards ([f9cd2c0](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/f9cd2c0ecd5dcadaf9ccaf43ac379dc5646e7150))
* **demo-app:** update storage disk for preview image uploads ([3b805ce](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/3b805ceed9af2e9d06564962a5407417c401c0a0))
* image upload ([21e5aac](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/21e5aacdad90684e75808c249dba82e78b346514))
* increase body preview length in error messages for SwissPostConnector ([8a630cb](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/8a630cb7cfd209f078fd6d561ca9b4f40b2b489b))
* remove testbench ([f130d5c](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/f130d5c64f12b06271c47ab22fe5d690d56fdf15))
* **requests:** implement multipart body handling for branding image and stamp uploads ([51adc31](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/51adc31513d134e78b9153352927e85a1493517e))

### Miscellaneous Chores

* add release-please manifest file for versioning ([ab614b4](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/ab614b41c3c677bb2fcf0bb3cc43e1fd21ed358b))
* remove PHP 8.4 from test workflow matrix ([fe6165c](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/fe6165c9b1dadc76b909c5663491462153f88b31))
* simplify test workflow by removing Windows OS and adjusting stability settings ([979b79e](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/979b79e049b156b9793e3cc08726ddcaad1e83f9))
* update test workflow to use Laravel 12 only ([2169c52](https://github.com/gigerIT/laravel-swiss-post-postcard-api-client/commit/2169c52ed557e57bf88c694e2ebd98a3b3c360c9))

## Changelog

All notable changes to `laravel-swiss-post-postcard-api-client` will be documented in this file.
