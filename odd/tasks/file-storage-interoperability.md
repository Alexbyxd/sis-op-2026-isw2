# Feature: File Storage Interoperability System

## Objective
Build a multi-tenant file storage interoperability API service for police systems (Correspondence and Officer Management) with strict tenant isolation, UUID-based file referencing, direct authenticated binary streaming, and temporary HMAC signed URLs for frontend embedding.

## Problem & Motivation
The police department operates multiple independent internal systems (System 1: Correspondence, System 2: Officers). They need a centralized, on-premise, secure storage microservice where:
- Each system uploads files (.docx, .xls, .pdf, .jpg, .png, etc.) and receives a persistent UUID reference.
- System 1 can NEVER access or view System 2's files and vice versa (strict tenant isolation).
- Frontend clients can render files directly without leaking system API tokens via temporary HMAC signed URLs.
- Backend systems can directly stream, download, update status, and physically delete files.

## Scope & Constraints
- **Framework:** Laravel 13 on PHP 8.4
- **Database:** PostgreSQL running in a container via Podman
- **Testing:** Pest PHP
- **Auth:** Predefined tenant accounts (`sistema1`, `sistema2`) authenticated via Laravel Sanctum API Tokens
- **Identifiers:** RFC 4122 UUIDs for all public file keys
- **Storage:** Local isolated directories per tenant with configurable file size limits

## Checklist

- [ ] `TASK-1`: Setup Podman container for PostgreSQL, configure environment (`.env`), and install dependencies (Sanctum, Pest PHP).
- [ ] `TASK-2`: Create database migrations (Users/Tenants, Files table with UUID and metadata) and seeders for `sistema1` and `sistema2`.
- [ ] `TASK-3`: Implement Authentication API (`POST /api/v1/auth/login`) with Sanctum tokens and multi-tenant authorization guards.
- [ ] `TASK-4`: Implement File Upload, Listing, Metadata, Status toggle, and Physical Deletion endpoints with strict validation and tenant isolation.
- [ ] `TASK-5`: Implement Direct Binary Streaming (`/view`, `/download`) and HMAC Temporary Signed URLs (`/signed/files/{uuid}/view`, `/signed/files/{uuid}/download`).
- [ ] `TASK-6`: Write comprehensive Pest test suite covering auth, tenant isolation, file uploads, size limits, downloads, signed URLs, and physical deletion.

## Verification Evidence
- [ ] Podman PostgreSQL container running and accepting connections.
- [ ] Database migrations & seeders executed cleanly.
- [ ] Pest test suite passing with 100% green tests.
- [ ] Larastan / Pint passing.
