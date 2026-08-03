<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [2.0.17](https://github.com/liquiddesign/security/compare/v2.0.16...v2.0.17) (2026-08-03)

### Bug Fixes

##### Authenticator

* Resolve the identity from the shop-scoped account instead of from the login. `getByAccountLogin()` is not always shop-scoped and selects an account by login alone, so with the same login present in several shops it could pair an identity from one shop with an account from another; `validateAuthentication()` then ran against a foreign account. `authenticate()` now looks up the account via the always-shop-scoped `findByLogin()` and binds the identity to it through the new `IUserRepository::getByAccount()`, which supersedes and generalises the v2.0.16 cross-shop guard ([2.0](https://github.com/liquiddesign/security/tree/2.0))

---

## [2.0.16](https://github.com/liquiddesign/security/compare/v2.0.15...v2.0.16) (2026-08-03)

### Bug Fixes

##### Authenticator

* Reject a login whose identity is found but whose account is out of the current shop scope. `getByAccountLogin()` is not always shop-scoped (Administrator, Merchant) while `findByLogin()` always is, so a cross-shop login resolved an identity without an account; the loop then fell through to `return $identity` while `validateAuthentication()` — the only password/active/authorized check — was skipped, letting such accounts log in with any password. The identity is now discarded when its account is missing ([2.0](https://github.com/liquiddesign/security/tree/2.0))

---

## [2.0.12](https://github.com/liquiddesign/security/compare/v2.0.11...v2.0.12) (2025-04-02)

### Features


##### Account Contact Info

* Extend contact type options and add timestamps ([6c7244](https://github.com/liquiddesign/security/commit/6c7244720f8dccd18fe67a4a81b846b9cb7b2588))


---

## [2.0.11](https://github.com/liquiddesign/security/compare/v2.0.10...v2.0.11) (2025-01-23)

### Features


##### Account Contact Info

* Add externalId property ([887378](https://github.com/liquiddesign/security/commit/8873782887fbbaa73e9fec176e89e1ad04d4935c))


---

## [2.0.10](https://github.com/liquiddesign/security/compare/v2.0.9...v2.0.10) (2024-10-22)

### Features

* Add generic type hints to AccountContactInfo and Account repositories ([e3d619](https://github.com/liquiddesign/security/commit/e3d619ea085f50a3dd6bd4aed8b298087de43284))


---

## [2.0.9](https://github.com/liquiddesign/security/compare/v2.0.8...v2.0.9) (2024-10-21)


---

## [2.0.8](https://github.com/liquiddesign/security/compare/v2.0.7...v2.0.8) (2024-10-21)


---

## [2.0.7](https://github.com/liquiddesign/security/compare/v2.0.6...v2.0.7) (2024-03-08)


---

## [2.0.6](https://github.com/liquiddesign/security/compare/v2.0.5...v2.0.6) (2024-03-08)


---

## [2.0.5](https://github.com/liquiddesign/security/compare/v2.0.4...v2.0.5) (2024-03-07)


---

