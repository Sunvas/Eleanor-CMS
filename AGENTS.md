# AGENTS.md

## Eleanor CMS

This document describes the development principles of Eleanor CMS.

Eleanor CMS intentionally follows its own architecture and conventions.
When contributing code, documentation, tests, or suggestions, preserve the
existing design instead of replacing it with currently popular practices.

---

# Philosophy

Eleanor CMS is designed to be:

- lightweight;
- fast;
- predictable;
- easy to debug;
- easy to maintain;
- suitable for both small and large projects.

The system should remain understandable after years of maintenance.

Simple solutions are preferred over sophisticated ones.

---

# Architecture

Every feature is implemented as a unit.

Units communicate through the shared `$CMS` runtime object.

Do not introduce additional service locators, dependency injection
containers, or alternative communication mechanisms unless they provide
clear architectural benefits.

The administration panel is part of the same application rather than a
separate project.

Prefer extending existing infrastructure over introducing parallel
implementations.

Avoid event-driven architecture for ordinary PHP request handling.

A PHP request should remain explicit and traceable: handle input, perform
required operations, send response, terminate.

Do not hide important control flow behind event buses, dispatchers, or
listener chains.

---

# Runtime

The CMS is built on top of Eleanor PHP Library.

Reuse existing library functionality whenever appropriate.

Do not duplicate functionality already provided by the library.

Lazy initialization is encouraged for expensive objects.

---

# Performance

Performance matters.

However:

- readability is more important than micro-optimizations;
- optimizations should remain understandable;
- avoid unnecessary object creation;
- avoid unnecessary filesystem access;
- prefer prepared statements whenever practical;
- avoid unnecessary database queries.

Expensive work should be postponed until it is actually required.

---

# Database

Keep the schema simple.

Database logic should be placed where it naturally belongs.

Use database triggers when they improve data integrity, reduce duplicated
logic, or eliminate unnecessary communication between PHP and MySQL.

Avoid moving SQL logic into PHP without a clear benefit.

Database design should remain understandable without extensive
documentation.

---

# Frontend

Complex user interface components should preferably be rendered on the client.

Use Vue.js for dynamic forms, lists, editors, and other interactive
components.

Server-side rendering should focus on page structure and initial content
rather than generating large interactive components.

Third-party JavaScript libraries should be loaded from public CDNs such as
jsDelivr.

Prefer URLs that automatically reference the latest available version
whenever backward compatibility is not affected.

---

# Technology

The project targets current stable versions of PHP and its dependencies.

Do not introduce compatibility code for obsolete environments unless
explicitly required.

---

# Build process

Eleanor CMS does not require a frontend build step.

The repository should remain directly usable after cloning.

Do not introduce npm, webpack, Vite, Rollup, or similar tooling.

Internal JavaScript files should not require compilation, bundling,
or minification.

---

# Documentation

Documentation is part of the source code.

Comments and PHPDoc should explain behavior, intent, or rationale rather
than repeat what is already obvious from the implementation.

Describe what the code does from the caller's perspective, not how it is
implemented internally.

When behavior is non-trivial, describe it explicitly instead of using
generic phrases such as "Get value" or "Set property".

Keep comments concise.

Avoid redundant comments.

Use natural technical English.

Prefer:

- Get...
- Create...
- Return...
- Resolve...
- Initialize...
- Generate...

Avoid:

- Obtaining...
- Making...
- Setting...

Boolean descriptions should usually begin with "Whether...".

---

# Naming

The project uses the term "unit" instead of "module".

Names should describe purpose rather than implementation.

Prefer clarity over brevity.

Avoid unnecessary abbreviations unless they are well established inside
the project.

---

# Dependencies

Avoid introducing external dependencies unless they provide significant
value that cannot reasonably be achieved inside the project.

The CMS should remain largely self-contained.

---

# Coding style

Prefer:

- strict typing;
- readonly properties;
- explicit control flow;
- descriptive exception messages;
- focused methods;
- lazy initialization.

Avoid:

- unnecessary inheritance;
- unnecessary interfaces;
- unnecessary design patterns;
- premature abstraction.

---

# Evolution

Eleanor CMS evolves continuously.

Do not preserve accidental complexity for historical reasons.

If a simpler and cleaner solution exists, prefer improving the architecture
instead of working around old limitations.

Backward compatibility is desirable but should not prevent meaningful
improvements.

Breaking changes are acceptable when they significantly improve
architecture, maintainability, or developer experience.

Prefer a clean design over preserving obsolete APIs forever.

---

# Simplicity

Every new dependency, abstraction, build step, or runtime requirement
should have a clear practical justification.

If a feature can be implemented using the existing architecture, prefer
improving the existing code over introducing another subsystem.

The simplest maintainable solution is usually the preferred one.

---

# AI contribution guidelines

When suggesting changes:

- preserve the existing architecture;
- preserve project terminology;
- preserve the public API whenever possible;
- improve readability without changing behavior;
- prefer evolutionary improvements over rewrites.

Do not automatically suggest:

- migrating to another framework;
- dependency injection containers;
- Symfony components;
- Laravel conventions;
- Composer-only solutions;
- event buses;
- routers;
- CQRS;
- DDD;
- unnecessary abstractions;
- unnecessary interfaces.

The goal is to improve Eleanor CMS while preserving its philosophy.

Suggestions should naturally integrate into the existing architecture
rather than replace it with another development model.