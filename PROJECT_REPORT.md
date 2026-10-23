# PixelProof Project Report

## Objective

Build an Ubuntu-based Docker container running a LAMP stack and a web application that accepts an image upload and produces an educational estimate of whether the image may have been AI-generated.

## Implementation

The project uses Ubuntu 24.04 as the base image. Apache serves the PHP application, PHP performs upload validation and image analysis, and MariaDB stores analysis history. Supervisor starts Apache and MariaDB inside the single container required by the assignment.

## Application flow

1. The browser submits an image using `multipart/form-data`.
2. PHP checks the upload error and 8 MB size limit.
3. `finfo` detects the actual MIME type.
4. `getimagesize()` confirms that the file is a decodable image.
5. The server gives the upload a random filename.
6. A heuristic analyzes metadata, dimensions, aspect ratio, and compression characteristics.
7. The score and reasons are displayed and stored in MariaDB.

## Security design

The upload endpoint uses allow-listed MIME types, size limits, random filenames, prepared SQL statements, HTML output encoding, and an upload directory that denies script execution. Apache also sends basic browser hardening headers.

The project is intentionally suitable for an authorized security lab. Students can test file validation, path traversal handling, output escaping, SQL injection resistance, container configuration, and service separation without targeting anyone else's systems.

## Important limitation

The detector is not a trained forensic model. Image origin cannot be reliably proven from these heuristics alone. A production system would require a validated model, a labeled dataset, calibration, rate limiting, authentication, malware scanning, privacy controls, and an explicit retention policy.

## Verification

The image was built successfully with Docker using host networking in the sandbox because the sandbox Docker bridge is restricted. The container was then run in an isolated network namespace. The homepage, invalid-file rejection, valid-image analysis, MariaDB-backed history route, and application startup were verified.

## Run command

```bash
docker compose up --build
```

Then open `http://localhost:8080`.
