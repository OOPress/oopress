# OOPress Project Roadmap

This document outlines the planned milestones, version goals, and priorities for the OOPress Project.

---

## 🚀 Vision

OOPress is a modern, object-oriented, extensible CMS platform designed to combine:

- Modern PHP architecture (PSR-4, DI, SOLID principles)
- Modular, event-driven plugin system
- Clean, maintainable core code
- Flexible headless and web-first deployment

---

## 🎯 Milestones

### 0.1 — Foundation (Initial Prototype)
- [x] Repository setup
- [x] PSR-4 / Composer autoloading
- [x] Basic folder structure (`/src`, `/tests`, `/docs`)
- [x] Initial `bootstrap.php`
- [ ] Hello world CLI command
- [x] Initial README and LICENSE (Apache 2.0)

---

### 0.2 — Core Architecture
- [ ] Core service container / DI setup
- [ ] Event dispatcher / hook system
- [ ] Routing system (web + CLI)
- [ ] User authentication & roles
- [ ] Database layer (ORM or Query Builder)
- [ ] Basic logging & error handling

---

### 0.5 — Plugin & Theme System
- [ ] Plugin interface + loader
- [ ] Theme interface + templating engine
- [ ] Sample plugin & theme
- [ ] Plugin/theme dependency management
- [ ] CLI scaffolding for plugin/theme development

---

### 1.0 — First Stable Release
- [ ] Fully functional CMS core
- [ ] Core modules: pages, posts, media
- [ ] Admin dashboard (modular UI)
- [ ] Headless API (REST)
- [ ] Unit tests coverage 80%+
- [ ] Documentation: user + developer guides

---

## 📅 Future Goals (1.x+)
- Multi-language support
- Advanced caching & performance optimizations
- Continuous integration & deployment workflow
- Official plugin marketplace
- Optional SaaS / cloud deployment support

---

## ⚡ Notes
- Priorities: maintainability, security, and modularity
- Community contributions encouraged
- All versions strictly follow PSR-12 coding standards

