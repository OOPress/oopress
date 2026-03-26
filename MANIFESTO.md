# OOPress Manifesto

**Version 1.0**  
*March 22, 2026*

---

## Why OOPress Exists

I've been building and maintaining websites since the early 2000s. I've worked with XOOPS, Drupal, WordPress, Backdrop — sometimes for years at a time. I've seen what works. More importantly, I've seen what doesn't.

WordPress powers a huge part of the web, but its codebase carries decisions from 2003. It cannot modernize without breaking millions of sites. Drupal reinvents itself with every major version, leaving module authors and site owners to rebuild from scratch. Backdrop offers stability but inherits the architecture it was built to preserve.

OOPress starts fresh. It's built on 20+ years of experience with what CMS platforms get right and what they get wrong. It's built with modern PHP, clean architecture, and a clear philosophy:

**Stability for developers. Transparency for site owners.**

Not one or the other. Both.

---

## Promise to Module Authors

If you invest time in building for OOPress, we respect that investment.

- **API stability** is guaranteed for the life of a major version. If you build for OOPress 1.x, your module will work in 1.x. No surprises.
- **Breaking changes** are only introduced in major versions. When they are, they're clearly documented, explained, and accompanied by a migration path.
- **We don't hide behind "clever" optimizations** that sacrifice stability. Your module's behavior should be predictable.

We want you to build for OOPress with confidence. Not fear that the next update will break everything. Not frustration that the platform changed direction without notice.

**Build for OOPress. We'll keep the platform steady.**

---

## Promise to Site Owners

Your site is your investment. You should know exactly what's running on it — and whether it's healthy.

- **The admin panel tells you the truth.** Not just which modules are installed, but their status: maintained, unverified for this version, or flagged with security issues.
- **Module manifests require transparency.** Every module declares its API compatibility, minimum PHP version, last verified date, and a security contact. You know what you're installing before you install it.
- **The registry is a health monitor, not just a download directory.** It knows which modules have open security advisories, which haven't been tested with the current stable, which dependencies are outdated.

We want you to run your site with confidence. Not guessing. Not hoping. Knowing.

**Run OOPress. You'll always know where you stand.**

---

## What OOPress Will Never Do

- **We will never release a major version that breaks all existing modules without a clear migration path.** You will not wake up to find your site broken because we decided to rewrite the world.
- **We will never hide module health information.** Your dashboard will always show you the status of every installed module. We don't believe in "optimistic ignorance."
- **We will never force you to rely on a single repository.** OOPress modules can be distributed anywhere. The registry is a tool, not a gatekeeper.
- **We will never sacrifice stability for cleverness.** If a choice is between "cool but breaks things" and "boring but works," we choose boring. Every time.

---

## The Technical Decisions That Follow

A clear philosophy makes technical decisions easier.

**The module manifest** is minimal but complete: API compatibility range, minimum PHP version, last verified date, security contact. Everything else is optional. Authors declare what matters.

**The registry** exists to inform, not control. It tracks module health across versions, not just popularity. Site owners can see at a glance what's safe to use.

**The admin panel** is a dashboard of truth. It shows you your site's health, not just your site's content. Maintained modules. Unverified modules. Security flags. All in one place.

**API stability** means major versions are stable. 1.x modules work in 1.x. 2.0 introduces changes — with documentation and migration paths. No surprises.

---

## This Is The Foundation

This manifesto exists before a single line of code. It will guide every decision:

- When to break an API
- What information the admin panel must show
- How the module system should work
- What the registry should track

When I'm six months into development and debating whether to change something that will break existing modules, I'll come back here.

When a contributor suggests a feature that would hide information from site owners, I'll point here.

**This is what OOPress stands for.**

---

**Build for OOPress. We'll keep the platform steady.**  
**Run OOPress. You'll always know where you stand.**

---

*This document is the foundation of OOPress. It will not change without good reason. When it does, the change will be documented, explained, and justified.*

---

