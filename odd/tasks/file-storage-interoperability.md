# Feature: File Storage Interoperability System

## Objective
Build a multi-tenant file storage interoperability API service for police systems (Correspondence and Officer Management) with strict tenant isolation, UUID-based file referencing, direct authenticated binary streaming, temporary HMAC signed URLs for frontend embedding, and 100% offline Scalar interactive documentation.

## Checklist

- [x] `TASK-1`: Setup Podman container for PostgreSQL, configure environment (`.env`), and install dependencies (Sanctum, Pest PHP).
- [x] `TASK-2`: Create database migrations (Users/Tenants, Files table with UUID and metadata) and seeders for `sistema1` and `sistema2`.
- [x] `TASK-3`: Implement Authentication API (`POST /api/v1/auth/login`) with Sanctum tokens and multi-tenant authorization guards.
- [x] `TASK-4`: Implement File Upload, Listing, Metadata, Status toggle, and Physical Deletion endpoints with strict validation and tenant isolation.
- [x] `TASK-5`: Implement Direct Binary Streaming (`/view`, `/download`) and HMAC Temporary Signed URLs (`/signed/files/{uuid}/view`, `/signed/files/{uuid}/download`).
- [x] `TASK-6`: Write comprehensive Pest test suite covering auth, tenant isolation, file uploads, size limits, downloads, signed URLs, and physical deletion.
- [x] `TASK-7`: Configure 100% offline Scalar interactive documentation (`/scalar`) with self-hosted standalone JS bundle and local OpenAPI 3.1 specification.

## Verification Evidence
- [x] Podman PostgreSQL container running (`police_storage_postgres` on port 5432).
- [x] Database migrations & seeders executed cleanly on PostgreSQL (`sistema1` & `sistema2` created).
- [x] Pest test suite passing with 59/59 green tests.
- [x] Larastan (PHPStan) static analysis passing with 0 errors.
- [x] Laravel Pint code style checks passing with 0 formatting issues.
- [x] Scalar UI renders offline from `/vendor/scalar/scalar.js` and `storage/app/openapi.json`.
