# Eleanor CMS

[Русская версия](README.ru.md)

## Project principles

* **MIT/X11 license.** The system does not require copyright notices, referral links, or any other mandatory attribution indicating that the CMS is being used.
* **Zero dependency on third-party PHP code.** Eleanor CMS does not use third-party PHP libraries or frameworks.
* **No frontend build step.** Built-in JavaScript and CSS files do not require compilation through `npm` or other build tools. Any plain text editor is sufficient for making changes.
* **Documented source code.** The project structure, directories, files, and major code elements include explanations describing their purpose and making the system easier to understand.

## Users and security

* **Multiple users at the same time.** Multiple accounts can be opened simultaneously in separate browser tabs on the same device. The same user can also be signed in on multiple devices.
* **Two-factor authentication and access recovery.** To strengthen account security, TOTP-based two-factor authentication using an authenticator app can be enabled. If the password is lost, access can be recovered using one-time recovery codes. With two-factor authentication enabled, access can be confirmed using any two of three factors: password, TOTP, or a recovery code.
* **Modular architecture and group-based access control.** System functionality is organized into units. User permissions are determined by group membership and the roles assigned to those groups; a user can belong to multiple groups.
* **Minimal cookie usage.** Authentication uses a single cookie. Together with the explicit “Remember me” option, it falls under [authentication cookies that do not require separate user consent](https://europa.eu/youreurope/business/dealing-with-customers/data-protection/online-privacy/index_en.htm#main-article), so this mechanism does not require a separate cookie banner.
* **XSS protection through CSP.** Inline scripts are prohibited by [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CSP), making classic [XSS](https://en.wikipedia.org/wiki/Cross-site_scripting) attacks based on injected JavaScript impossible under the enforced policy.

## Architecture and tools

* **Asynchronous events.** Actions that do not require immediate execution can be processed separately from the main request; for example, sending a notification does not block page loading.
* **[Clean URLs](https://en.wikipedia.org/wiki/Clean_URL) based on the classic directory-and-file model.** URL structure is built without a centralized router, so addresses directly reflect the organization of system sections and resources.
* **Extensibility without deep integration.** A minimal unit can consist of a single file and does not require registration in the system configuration. Classes, interfaces, traits, and enums are autoloaded when placed in the corresponding directories.
* **Command-line support.** Eleanor CMS provides a common CLI interface that allows units to expose their own commands. The system can be installed either from a browser or from the command line; the CLI installer supports configuration validation and dry-run mode. Installer documentation is available in English and Russian.

Inspired by https://thebestmotherfucking.website/