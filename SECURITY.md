# Security Policy

## Supported versions

| Version | Security fixes |
| --- | --- |
| Latest 1.0.x release | Supported |
| Earlier releases | Not supported |

Security fixes are evaluated and published as new releases where appropriate. This is a community-maintained project; no guaranteed response or remediation time is offered.

## Report a vulnerability privately

**Please do not open public issues for suspected security vulnerabilities.**

Use GitHub's [private vulnerability reporting](https://github.com/alkinbg/TwigPreviewBundle/security/advisories) and select **Report a vulnerability**. This allows maintainers to review the report before public disclosure.

Provide a concise description, affected versions, reproduction steps, potential impact, and a suggested fix if available. Please allow time for review before disclosing the issue publicly.

## Security boundaries

Twig Preview Bundle is intentionally **development-only**. Never enable it, or its preview routes, on a publicly reachable development environment.

Although the bundle itself does not write application data, it renders templates using the application's real Twig environment. A template may call application services, and those services may have side effects or access sensitive data. Use synthetic JSON fixtures and avoid real credentials or user information.

The preview controller rejects requests outside the `dev` environment, but `noindex` and `no-store` response headers are **not** a substitute for keeping development servers private.
