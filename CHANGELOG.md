<!-- markdownlint-disable MD024 -->
# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

## [0.9.0](https://github.com/MESH-Research/CCR/compare/v0.8.0...v0.9.0) (2021-03-04)


### Features

* Enable client side user updating ([#156](https://github.com/MESH-Research/CCR/issues/156)) ([7e7d2f4](https://github.com/MESH-Research/CCR/commit/7e7d2f42cce236d8fac0dd4515105d4730036143)), closes [#132](https://github.com/MESH-Research/CCR/issues/132) [#133](https://github.com/MESH-Research/CCR/issues/133)


### Bug Fixes

* resolve console error on page load ([#153](https://github.com/MESH-Research/CCR/issues/153)) ([a375373](https://github.com/MESH-Research/CCR/commit/a375373e57bc3efe031b6e14e04c7670e0772725))


### Build System

* add cypress custom commands for integration testing ([#154](https://github.com/MESH-Research/CCR/issues/154)) ([68724d7](https://github.com/MESH-Research/CCR/commit/68724d73254ed42c7ae6dcadc79e5bfe2e103fbd))
* Add cypress-axe plugin for accessibility testing ([#155](https://github.com/MESH-Research/CCR/issues/155)) ([e275749](https://github.com/MESH-Research/CCR/commit/e2757493b0cbf7de55cbd545be5e9a28d4c296a9)), closes [#130](https://github.com/MESH-Research/CCR/issues/130)


### Tests

* add integration tests for login and register ([#157](https://github.com/MESH-Research/CCR/issues/157)) ([9fbaf6d](https://github.com/MESH-Research/CCR/commit/9fbaf6d7fa7386e3951d775f94204d21fe22e552))

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


### Build System

* add cypress for e2e testing ([#121](https://github.com/MESH-Research/CCR/issues/121)) ([9cd1992](https://github.com/MESH-Research/CCR/commit/9cd19928a8e4901e6203800dcbeead27f1b1ef14))
* Install Laravel permissions ([#114](https://github.com/MESH-Research/CCR/issues/114)) ([7cbbb1c](https://github.com/MESH-Research/CCR/commit/7cbbb1cd94fc272d554966c2cce24947d9b378d2))
* upgrade quasar/app to 2.1 ([#117](https://github.com/MESH-Research/CCR/issues/117)) ([8a2914f](https://github.com/MESH-Research/CCR/commit/8a2914fd81bb4e89c6bdbe3a9c53d8e7319d1a11))


### Documentation

* improve developer and public project documentation ([#112](https://github.com/MESH-Research/CCR/issues/112)) ([3a63367](https://github.com/MESH-Research/CCR/commit/3a63367677e6ee7424b786fe68dc8a42090fa73e)), closes [#110](https://github.com/MESH-Research/CCR/issues/110)

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


### Tests

* generate coverage imfornation automatically ([5b043f6](https://github.com/MESH-Research/CCR/commit/5b043f651d9d007038bb14b4aefc30d10e86b689))
* **backend:** add authentication plugin to application test ([7f6977c](https://github.com/MESH-Research/CCR/commit/7f6977c8164b834dda5955d55902ec0feed687ed))
* **backend:** add unit tests for user controller methods ([de81f19](https://github.com/MESH-Research/CCR/commit/de81f193287405a77eda7ad8ca0fcc89804ea9d4))


### Build System

* **deps:** bump laravel/framework from 8.20.1 to 8.22.1 in /backend ([#98](https://github.com/MESH-Research/CCR/issues/98)) ([4791952](https://github.com/MESH-Research/CCR/commit/4791952923c045ddc1a961bfa07e991a91fd8970))
* test netlify deploy hooks for docs ([a01528d](https://github.com/MESH-Research/CCR/commit/a01528d7f81cc26e33e6127bd1b31d979ee5e0dc))
* **ci:** Fix indenting error in php ci workflow ([6b9bb12](https://github.com/MESH-Research/CCR/commit/6b9bb1236d720bf5e196109c11ffa7f84424b12d))
* **ci:** Pin PHP version in CI scripts. ([2d4b299](https://github.com/MESH-Research/CCR/commit/2d4b2996ac5aece252aa908cf33ad7c5c3476c39))
* **deps:** Fix dependabot alerts for node-forge and ini. ([ce6af0c](https://github.com/MESH-Research/CCR/commit/ce6af0c0b6d85b20b487176a7ebaf490dc9cd801))
* **dev:** change to lando for dev setup ([cef9275](https://github.com/MESH-Research/CCR/commit/cef927511757a246efec5a9a86ccb40edc4eec70))
* **dev:** replace homegrown develop script with lando ([3908e15](https://github.com/MESH-Research/CCR/commit/3908e157bcc7595724ba9a9d12484df203f40135)), closes [#27](https://github.com/MESH-Research/CCR/issues/27)
* **develop:** improve develop script portability and functions ([136e874](https://github.com/MESH-Research/CCR/commit/136e87469e91916ca9863fa26b89a6318dc7a6f4))
* **develop:** remove initial delay in develop script waits ([a48e394](https://github.com/MESH-Research/CCR/commit/a48e394bffefdb556892dce3a7ef954c7922d7e6))
* **develop:** remove transient containers after use ([9c9a15e](https://github.com/MESH-Research/CCR/commit/9c9a15edbfdca77f26c92e928f7b68857315a3dc))
* **docs:** add missing config value for docs deployment ([78a8057](https://github.com/MESH-Research/CCR/commit/78a8057ad6c97056f2cd0d5af937775ea6fc85b9))
* **docs:** fix incorrect config option in docs deploy script ([f8927f3](https://github.com/MESH-Research/CCR/commit/f8927f39ba13b59da7a9ef0a62e0ae5b70020eff))
* **docs:** fix incorrect directory on docs build ([730e179](https://github.com/MESH-Research/CCR/commit/730e17934e14143f277886fc810d604dea070690))
* **docs:** move build environment variables to build step ([d5c3422](https://github.com/MESH-Research/CCR/commit/d5c3422dfb8ed843ce172314aef41f45ecb02755))
* **docs:** switch github action for docs build ([8c88d31](https://github.com/MESH-Research/CCR/commit/8c88d31689c47248e5eb56158b3cd0b7db9d1f93))
* **docs:** switch github actions for docs deployment ([e4aab8e](https://github.com/MESH-Research/CCR/commit/e4aab8ee56fce85ec1c3dc1331d56c0905499165))
* **docs:** trigger deployment to enable gh-pages ([cc7a9e1](https://github.com/MESH-Research/CCR/commit/cc7a9e17150ae0ca904a42fd9643606016fa40ac))
* **front-end:** add front-end dependencies ([5d20102](https://github.com/MESH-Research/CCR/commit/5d20102a1960fe011f0526d348a4c860dcff21fc))
* **frontend:** remove dependency on vue-auth plugin ([842d3fd](https://github.com/MESH-Research/CCR/commit/842d3fd1271c34a6720edefe6810617cce714a9d))
* **github:** fix failing database creation ([263a179](https://github.com/MESH-Research/CCR/commit/263a1790e17cd50656f6b585835684e501a475f7))
* **github:** fix failing front end build script ([a93a91b](https://github.com/MESH-Research/CCR/commit/a93a91bb0d29d4c5c4b23f2b73833fe2cda3c251))
* **github:** fix failing test harnesses ([059788a](https://github.com/MESH-Research/CCR/commit/059788a2951b5754b52a8738fb7f3b6643af7539))
* **github:** remove duplicate run statement from backend job ([96b1bf9](https://github.com/MESH-Research/CCR/commit/96b1bf975420897a7639d285463ccb826ea6c8ee))
* **github:** remove errant character in build script ([bce6102](https://github.com/MESH-Research/CCR/commit/bce6102a8475ad43dafd61414e5752cea5281c11))
* **github:** use testing environment in github actions ([f6afae2](https://github.com/MESH-Research/CCR/commit/f6afae2d94661f1a6baed66348c04f89a004ab2f))
* **github:** validate composer installation on each build ([327dd67](https://github.com/MESH-Research/CCR/commit/327dd6795aa492ccaa3d5b86cb7b402edefac5e0))
* **lando:** copy default .env file if non-existent ([3c2045e](https://github.com/MESH-Research/CCR/commit/3c2045e42bbb97e44d4dfca5ac55ca35c4839be7))
* **lando:** disable scanning on node service ([735ac57](https://github.com/MESH-Research/CCR/commit/735ac573973c450b93c30eb60c41e4d211c2c139))
* **lando:** fix permission denied error running no env script ([df41068](https://github.com/MESH-Research/CCR/commit/df41068613cfe3d71c9842528bea50f76c05d0bc))
* **lando:** remove directory overrides for tooling ([81eab34](https://github.com/MESH-Research/CCR/commit/81eab3489aafc9e44d06f9fe8f1ffe7671d721b9))
* **lando:** remove port option from node service ([a3e98e4](https://github.com/MESH-Research/CCR/commit/a3e98e466e149a89503ecc425ac7057d061ae996))
* **phpfpm:** remove prestissimo from Dockerfile ([db64fe3](https://github.com/MESH-Research/CCR/commit/db64fe301a048848ce115a0104d074c6a0317254))
* **vscode:** add tasks file to vscode folder ([531458d](https://github.com/MESH-Research/CCR/commit/531458d2628b6d8149a26f8f96675bede4949cc3))


### Documentation

* setup documentation site framework ([#92](https://github.com/MESH-Research/CCR/issues/92)) ([01eff99](https://github.com/MESH-Research/CCR/commit/01eff99fcdbdfbe8a5f39cc3dce752eaec12abd4))
* testing documentation updates ([#93](https://github.com/MESH-Research/CCR/issues/93)) ([bf3100b](https://github.com/MESH-Research/CCR/commit/bf3100b84946ebe1790b4819fe72a729af16cccb))
* update contribution guidelines and add PR linting ([#29](https://github.com/MESH-Research/CCR/issues/29)) ([65cff51](https://github.com/MESH-Research/CCR/commit/65cff51ccf921083fa8701e3cc24b26f8b11760a))
* update user story issue template to add comments ([c7584d9](https://github.com/MESH-Research/CCR/commit/c7584d99903f021bebd321c11a5bd8e9e66a3087))
* **docker:** update help text to display script name as called ([b51fb49](https://github.com/MESH-Research/CCR/commit/b51fb494ce739863ba7c24d233f1b06ca2485aac))
* **lando:** remove extra steps from lando section in readme ([79349bb](https://github.com/MESH-Research/CCR/commit/79349bbbbe6ccfe02f521dbcbe70ae3e980f69fe))
* **lando:** update readme for lando ([a047a28](https://github.com/MESH-Research/CCR/commit/a047a28b5d947ceb0ee050c0e7b9d93c03fa4110))
