![OOPress Logo](public/assets/images/logo/oopress_logo_dark_xs.png)

# OOPress CMS

**Stability for developers. Transparency for site owners.**

OOPress is a modern PHP CMS built on 20+ years of experience with what CMS platforms get right — and what they get wrong. It combines the simplicity of XOOPS, the accessibility of WordPress, and the stability of Backdrop, built on a clean modern foundation.

> This project is in early development. The architecture and APIs are being established. Not ready for production use.

---

## Philosophy

OOPress makes two promises:

**To module authors** — API stability is guaranteed for the life of a major version. Build for OOPress 1.x and your module will work across 1.x. Breaking changes only happen in major versions, with documented migration paths.

**To site owners** — your admin panel tells you the truth. Every installed module shows its health status: maintained, unverified, or flagged. You always know what you're running.

Read the full [MANIFESTO.md](MANIFESTO.md).

---

## Requirements

- PHP 8.2 or higher
- MySQL 8.0+ / MariaDB 10.6+ / PostgreSQL 14+
- Apache or Nginx

---

## Built On

- [Symfony Components](https://symfony.com/) — HttpFoundation, Routing, Translation, Security, Validator, EventDispatcher, Console
- [Doctrine DBAL](https://www.doctrine-project.org/) — database abstraction
- [Twig](https://twig.symfony.com/) — templating

---

## Project Structure

```
oopress/
├── public/          ← web root (only this is exposed)
├── core/            ← OOPress framework (@api surfaces)
├── modules/         ← first-party bundled modules
├── themes/          ← bundled themes
├── config/          ← environment configuration
├── var/             ← runtime data (cache, logs, sessions)
└── files/           ← user uploads
```

---

## Development Status

- [x] Project manifesto
- [x] Architecture design
- [x] Module manifest format
- [x] Update/upgrade strategy
- [ ] Core kernel bootstrap
- [ ] Module loader
- [ ] Content type system
- [ ] Admin panel
- [ ] Installer

---

## License

Apache License 2.0 — see [LICENSE](LICENSE) for details.

See [NOTICE.md](NOTICE.md) for third-party component attributions.

---

## Contributing

Contribution guidelines will be published when the project reaches a stable alpha. Watch the repository for updates.

---

*OOPress — [oopress.org](https://oopress.org)*
