![OOPress Logo](assets/images/logo/oopress_logo_xs.png)

# The OOPress Project

**OOPress** is a modern, object-oriented, extensible CMS platform inspired by the strengths of WordPress and XOOPS — but architected from the ground up for maintainability, modularity, scalability, and clean coding standards.

Our mission is simple:
> **Bring modern architecture, OOP principles, and clean extensibility to content management.**

---

## 🚀 Why OOPress?

WordPress is powerful, but its legacy procedural architecture introduces:
- Hidden coupling
- Global namespace pollution
- Tight interdependencies
- Slow evolution of code structure

**OOPress exists to do it right.**

This project will provide:
- Modern PHP standards (PSR-4 autoloading, DI containers, etc.)
- Clear APIs with separation of concerns
- Event-driven extensibility
- Fast and secure base core
- Modular architecture for plugins and themes
- Easy upgrade paths
- Strong developer experience

---

## 🧠 Core Objectives

1. **Modern PHP Architecture**
   - OOP / SOLID practices  
   - Separation of concerns (services, interfaces, repositories, etc.)
   - Event/Hook system (clean dispatcher pattern)

2. **Scalability & Maintainability**
   - Plugin system based on service providers
   - Independent modules with explicit boundaries
   - DI/IoC container pattern

3. **Clean UI / UX for End Users**
   - Modern admin dashboard
   - Dataset/structure-oriented editing
   - Optional headless mode

4. **Developer-First Approach**
   - Stable, versioned API
   - CLI tooling
   - Testable system
   - Minimal coupling and no global clutter

---

## 📦 Planned Features

```
| Feature | Goal |
|--------|------|
| Plugin Architecture | Lightweight, event-driven, isolated |
| Theme System | Templating engine based |
| Database Layer | ORM/Query Builder |
| Routing | Clean HTTP controller routing |
| Authentication | User roles + permissions |
| CLI Tools | Scaffolding, migrations, debugging |
| API Mode | Optional fully headless mode |
| Admin UI | Modern interface with modular structure |
```

---

## 📁 Repository Structure (proposed)

```
/oopress
 ├─ /assets/images/logo/
 ├─ /config
 ├─ /docs
 │   ├─ ROADMAP.md
 │   └─ ARCHITECTURE.md
 ├─ /public
 ├─ /src
 │   ├─ /Core
 │   │   └─ Application.php
 │   ├─ /Modules
 │   ├─ /Themes
 │   ├─ /Plugins
 │   └─ bootstrap.php
 ├─ /storage
 ├─ /tests
 │   └─ ExampleTest.php
 ├─ composer.json
 ├─ .env.example
 ├─ .gitignore
 ├─ README.md
 ├─ LICENSE
 ├─ NOTICE
 └─ vendor
```

---

## 🧪 Technology Stack

- **Language:** PHP (8.2+)
- **Core Principles:** OOP • SOLID • PSR-12
- **Autoloading:** PSR-4 (Composer)
- **Database:** PDO + ORM layer
- **Templating:** (To be decided)
- **Caching:** Adapter-based abstraction
- **Testing:** PHPUnit
- **API:** JSON REST endpoints

---

## 🛡️ Philosophy

> The CMS should be *extendable without hacks*.

This means:
- No global variables
- No shared massive namespace pollution
- No spaghetti procedural injection
- No breaking compatibility surprises

---

## 📅 Project Status

🚧 **ACTIVE DEVELOPMENT — NOT PRODUCTION READY**

This repository will initially contain:
- Architecture documentation
- Boilerplate foundation
- Namespace and concept mapping
- Early prototypes

Milestone roadmap will be added soon.

---

## 🤝 Contribution & Community

We welcome:
- Developers interested in modern PHP CMS architecture
- Contributors with experience in framework design
- UI/UX designers
- QA testers

Ways to get involved:
1. Open issues with suggestions or RFCs
2. Start discussions on architectural choices
3. Contribute to prototype modules (when announced)

A full contribution guide will follow.

---

## 💬 Communication

Discussions will primarily occur via:
- GitHub Issues  
- GitHub Discussions (as soon as enabled)

Slack/Discord/Matrix may follow based on interest.

---

## ⚖️ License

The OOPress Project is licensed under the **Apache License 2.0**.

You are free to use, modify, distribute, and build commercial products on top of OOPress, provided you comply with the terms of the Apache 2.0 license.

See the full `LICENSE` file for more details.


---

## 🧭 Vision

> OOPress is not *another WordPress fork.*  
> It's a **clean-slate rethinking** of CMS architecture.

If this mission resonates with you,  
**welcome to The OOPress Project.**

---


