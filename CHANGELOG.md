<!-- markdownlint-disable MD024 MD001 -->
# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

## [0.9.0](https://github.com/MESH-Research/CCR/compare/v0.8.0...v0.9.0) (2021-03-04)


### Features

* Enable client side user updating ([#156](https://github.com/MESH-Research/CCR/issues/156)) ([7e7d2f4](https://github.com/MESH-Research/CCR/commit/7e7d2f42cce236d8fac0dd4515105d4730036143)), closes [#132](https://github.com/MESH-Research/CCR/issues/132) [#133](https://github.com/MESH-Research/CCR/issues/133)


### Bug Fixes

* resolve console error on page load ([#153](https://github.com/MESH-Research/CCR/issues/153)) ([a375373](https://github.com/MESH-Research/CCR/commit/a375373e57bc3efe031b6e14e04c7670e0772725))

## [0.8.0](https://github.com/MESH-Research/CCR/compare/v0.7.0...v0.8.0) (2021-02-18)


### Features

* add first user permission ([#128](https://github.com/MESH-Research/CCR/issues/128)) ([fa17906](https://github.com/MESH-Research/CCR/commit/fa179065787b361e1b6d6e3d4cc36e2df0a0abd7)), closes [#81](https://github.com/MESH-Research/CCR/issues/81)
* cleanup account pages layouts ([#136](https://github.com/MESH-Research/CCR/issues/136)) ([4751af3](https://github.com/MESH-Research/CCR/commit/4751af348c3b4a17e37cf8a324ad24510a28007a))
* implement email verification backend ([#129](https://github.com/MESH-Research/CCR/issues/129)) ([f175840](https://github.com/MESH-Research/CCR/commit/f175840aa68bd8cbb69885f0f90e340d100455e3))
* implement email verification in front end ([#131](https://github.com/MESH-Research/CCR/issues/131)) ([4d6a1b9](https://github.com/MESH-Research/CCR/commit/4d6a1b9e9b707c00b04f5bfadfb5d74e6cf71700)), closes [#87](https://github.com/MESH-Research/CCR/issues/87)
* Issue 120 update user backend ([#135](https://github.com/MESH-Research/CCR/issues/135)) ([1d383d6](https://github.com/MESH-Research/CCR/commit/1d383d6776bf120fed272b3bd2b7db5d88e05fd0)), closes [#120](https://github.com/MESH-Research/CCR/issues/120)

## [0.7.0](https://github.com/MESH-Research/CCR/compare/v0.6.0...v0.7.0) (2021-02-05)


### Features

* Add user roles ([#118](https://github.com/MESH-Research/CCR/issues/118)) ([4f7a584](https://github.com/MESH-Research/CCR/commit/4f7a584674a8cfd294461eaab593d7f3756e060b))
* extend graphql with user roles ([#123](https://github.com/MESH-Research/CCR/issues/123)) ([066c724](https://github.com/MESH-Research/CCR/commit/066c724be094125f1fcb54d8772c05a62489b5fe))
* improve authentication/registration user flow ([#113](https://github.com/MESH-Research/CCR/issues/113)) ([590f15e](https://github.com/MESH-Research/CCR/commit/590f15ecad62f5f8ee1ab5387c83f3b586b81929))
* upgrade lighthouse to version 5.1 ([#115](https://github.com/MESH-Research/CCR/issues/115)) ([bf3caf5](https://github.com/MESH-Research/CCR/commit/bf3caf5a263240bdce2949c494387952ce5888fd))


### Bug Fixes

* **api:** allow name to be null or empty in createUser migration ([#109](https://github.com/MESH-Research/CCR/issues/109)) ([d4b7820](https://github.com/MESH-Research/CCR/commit/d4b7820b51c64052325c1b9a123f40a8a17a9980)), closes [#103](https://github.com/MESH-Research/CCR/issues/103)

## [0.6.0](https://github.com/MESH-Research/CCR/compare/2dec23ec321a8ccb6c43e776fbb27a55483fd870...v0.6.0) (2021-01-22)


### Features

* add ability to create user account from client ([#96](https://github.com/MESH-Research/CCR/issues/96)) ([54ad038](https://github.com/MESH-Research/CCR/commit/54ad0382411e4b445693f8d73560c34c38a37e71))
* add basic login and logout functionality ([b4103a9](https://github.com/MESH-Research/CCR/commit/b4103a99634bec5bb5555fe0a62935a1a4717712))
* Add helpful tip to README ([148ef8f](https://github.com/MESH-Research/CCR/commit/148ef8fcaeea1f21151e708d0c444c0ea3f4cb8e))
* add user creation api ([#94](https://github.com/MESH-Research/CCR/issues/94)) ([e83803f](https://github.com/MESH-Research/CCR/commit/e83803fdd1f88c4f2f571487c20dcb4eeac13ee0))
* setup sanctum and connect login page ([#97](https://github.com/MESH-Research/CCR/issues/97)) ([32bdeee](https://github.com/MESH-Research/CCR/commit/32bdeee18064f8d61d58d4be5f7befda2cf999e0))
* use php sessions for auth instead of JWTs ([4e5942e](https://github.com/MESH-Research/CCR/commit/4e5942ea426dc38554a47113282de1a3c70140a6)), closes [#10](https://github.com/MESH-Research/CCR/issues/10)
* **schema:** add password field to users table ([2dec23e](https://github.com/MESH-Research/CCR/commit/2dec23ec321a8ccb6c43e776fbb27a55483fd870))


### Bug Fixes

* change lando config to utf8mb4 by default ([1f387a8](https://github.com/MESH-Research/CCR/commit/1f387a8dfb4901a909c065eda38b804b24e06cd0))
* merge migrations in graphql schema ([f8a9ae6](https://github.com/MESH-Research/CCR/commit/f8a9ae63f92854acabc2a754d2e0c90cdede8981))
* remove hostname from graphql api config ([#89](https://github.com/MESH-Research/CCR/issues/89)) ([e5ed42a](https://github.com/MESH-Research/CCR/commit/e5ed42a27d2d67e732808289a7d139181199bcff)), closes [#62](https://github.com/MESH-Research/CCR/issues/62)
* update axios dependency due to SSRF vulnerability ([#31](https://github.com/MESH-Research/CCR/issues/31)) ([b0a6054](https://github.com/MESH-Research/CCR/commit/b0a6054dbbeae18ffba5a551a612588af72979cd))
* upgrade laravel to 8.0 ([e926896](https://github.com/MESH-Research/CCR/commit/e9268968e0777dd9f96b3f0bb860a33755873b88))
* **backend:** remove reserved words from notifications table ([939c491](https://github.com/MESH-Research/CCR/commit/939c491f3ce8d6bc80e43c000aa445e4f83b9b5d))
* **deps:** upgrade client deps to clear bl dependabot alert ([c6fc5d5](https://github.com/MESH-Research/CCR/commit/c6fc5d5e9c9036960131815e34b2284b77ee597f))
* upgrade php dependencies ([89cbfdf](https://github.com/MESH-Research/CCR/commit/89cbfdf9a870207bcdc8a5dc9228f7df1020ec1f))
* **backend:** return 401 on incorrect login ([d95de28](https://github.com/MESH-Research/CCR/commit/d95de282792de147ea9bf86508b9364674dd25c9))
* **frontend:** change label on Username or Password to Username or Email ([e8742d7](https://github.com/MESH-Research/CCR/commit/e8742d7501e36f68d42b6c375efd85a7952a3da7))
* **frontend:** fix missing logout icon ([8c298f9](https://github.com/MESH-Research/CCR/commit/8c298f9bd5d3f234d6d82e73bbff9a191284bbe6)), closes [#11](https://github.com/MESH-Research/CCR/issues/11)
* **frontend:** patch vue-auth to work correctly with quasar ([f6292fe](https://github.com/MESH-Research/CCR/commit/f6292fed95799f9758979cbed0da29228f58f15b))
* **router:** change router mode to history ([08bc7fd](https://github.com/MESH-Research/CCR/commit/08bc7fd802ce8727bb6a30dec381c2a56f9f16bd))

