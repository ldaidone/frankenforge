# **Lightweight PHP “FrankenForge” Kernel**

## **Purpose**

This repository provides a minimal, high-performance PHP kernel built on FrankenPHP.

It is designed for experienced developers who want full architectural control without traditional framework overhead.

Agents working in this codebase should prioritize:

- Performance
- Explicit architecture
- Minimal abstraction layers

---

## **Core Principles**

### **1. Performance First**

- The application runs in **FrankenPHP worker mode******
- Avoid per-request bootstrapping
- Reuse in-memory state when safe (routing, config, connections)

### **2. HTML-First Interactivity**

- Use **HTMX** for all dynamic UI behavior
- Do NOT introduce React, Vue, or client-side frameworks
- Prefer server-rendered HTML fragments

### **3. Architecture Agnostic (DDD-Friendly)**

- Follow **Domain-Driven Design (DDD)** or **Hexagonal Architecture******
- Keep domain logic isolated from infrastructure
- Avoid framework-style “magic”

### **4. Content Negotiation**

Controllers (actions) must support multiple response types:

- Full HTML (default)
- HTMX partials (when HX-Request: true)
- JSON (when Accept: application/json)

### **5. Zero-JS Runtime Tooling**

- No Node.js required
- Tailwind via CLI or CDN only
- Keep runtime dependencies minimal

---

## **Technical Stack**

- Runtime: FrankenPHP
- Language: PHP 8.3+
- Routing: nikic/fast-route
- Templating: Native PHP (output buffering)
- Frontend: HTMX
- Styling: Tailwind CSS
- Database: PDO (Data Mapper / Repository pattern)
- Distribution: Composer (library + skeleton)

---

## **Execution Model**

### **Worker Loop**

- The application is **stateful across requests******
- Initialize once:

- Router
- Database connections
- Configuration
- Avoid re-initializing services per request

Agents must:

- Treat global state carefully
- Prevent memory leaks
- Avoid storing request-specific data in long-lived objects

---

## **Rendering Rules**

### **View Behavior**

The rendering system must:

1. Detect HX-Request header:

- true → return fragment only
- false → wrap in layout
2. Detect Accept: application/json:

- Return serialized data (no HTML)
3. Use output buffering for templates:

- No external templating engines

---

## **Project Structure Guidelines**

Agents should enforce:

- /Domain → business logic (pure, framework-independent)
- /Application → use cases / services
- /Infrastructure → DB, external services
- /UI → controllers, views, HTTP layer

Rules:

- Domain must not depend on Infrastructure
- Controllers must stay thin
- No hidden dependencies

---

## **Coding Standards**

- Use strict types (declare(strict_types=1);)
- Prefer immutability (readonly where possible)
- Avoid static state unless explicitly required
- No service locators or global containers
- Dependency injection should be explicit

---

## **What NOT to Do**

Agents must avoid:

- Adding heavy frameworks (Laravel, Symfony, etc.)
- Introducing frontend JS frameworks
- Reintroducing request bootstrapping overhead
- Mixing domain logic into controllers or views
- Overengineering abstractions

---

## **What TO Optimize For**

- Fast request handling (microseconds mindset)
- Clear separation of concerns
- Readable, explicit code
- Low cognitive overhead
- Long-term maintainability

---

## **Mental Model**

Think of this kernel as:

> A thin execution engine, not a framework.

Every addition should be justified.

If something feels “automatic” or “magical”, it’s probably wrong.

---

## **Contribution Heuristics**

Before adding code, ask:

- Does this improve performance or clarity?
- Can this be done with fewer abstractions?
- Does this respect DDD boundaries?
- Will this still make sense in 6 months?

If not, simplify.

---

:::