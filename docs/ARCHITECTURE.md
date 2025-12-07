# OOPress Project Architecture

This document describes the core architectural principles, design patterns, and folder structure for OOPress.

---

## 🧠 Core Principles

1. **Object-Oriented**  
   - All core components are OOP-based
   - SOLID principles applied

2. **Modular Design**  
   - Core separated from modules/plugins/themes
   - Each module encapsulated and independently testable

3. **PSR Standards**  
   - PSR-4 autoloading
   - PSR-12 coding standards

4. **Event-Driven**  
   - Hooks/events for plugins to extend behavior without modifying core

5. **Dependency Injection**  
   - Services injected instead of hard-coded dependencies
   - Facilitates testing and maintainability


## 📂 Folder Structure

/oopress
├─ /docs            # Project documentation (roadmap, architecture, guides)
├─ /src             # Core source code (PSR-4)
│   ├─ /Core        # Framework classes (DI, routing, events)
│   └─ bootstrap.php
├─ /tests           # PHPUnit tests
├─ README.md
├─ LICENSE
└─ NOTICE

- `/src/Core` → Base classes (Application, Router, EventDispatcher, ServiceProvider)
- `/bootstrap.php` → Single entry point for bootstrapping CLI or web
- `/tests` → Automated tests
- `/docs` → Project documentation

## 🛠 Core Components

| Component | Responsibility |
|-----------|----------------|
| **Application** | Main app container, service registration |
| **Router** | Maps requests to controllers (web + CLI) |
| **Controller** | Handles request logic, returns responses |
| **EventDispatcher** | Fires events/hooks for extensibility |
| **ServiceProvider** | Registers services/plugins into container |
| **Database Layer** | ORM/Query builder, database abstraction |
| **Plugin System** | Event-driven, isolated, auto-loadable |
| **Theme System** | Templating and rendering of frontend views |


## 🔗 Namespace Convention

- Root namespace: `OOPress\`
- Core: `OOPress\Core\`
- Plugins: `OOPress\Plugins\PluginName\`
- Themes: `OOPress\Themes\ThemeName\`


## ⚡ Architectural Notes

- **Single Responsibility:** Each class has one primary role
- **Open/Closed Principle:** Easily extendable without modifying core
- **Inversion of Control:** Dependencies injected rather than hard-coded
- **Event-Based Extensions:** Plugins never hack the core; they subscribe to events


