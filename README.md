# PixelProof — Ubuntu LAMP AI-Image Analysis Lab

A classroom web application that runs in an Ubuntu-based Docker container with Apache, PHP, and MariaDB. Users upload an image, and the app returns an **educational heuristic** about whether it has characteristics commonly associated with AI-generated images.

> This is not a forensic detector. It does not prove image origin and should not be used for consequential decisions.

## Architecture

```text
Browser
  │ HTTP :8080
  ▼
Ubuntu 24.04 container
  ├── Apache 2.4
  ├── PHP 8.x + GD + PDO MySQL
  └── MariaDB
       └── analyses table
```

The assignment asks for a single Ubuntu container running a LAMP stack, so Apache, PHP, and MariaDB run together under Supervisor. In production, these services should normally be separated.

## Run it

Requirements: Docker Engine and Docker Compose.

```bash
git clone <your-repository-url>
cd ai-image-detector
docker compose up --build
```

Open [http://localhost:8080](http://localhost:8080).

Stop it:

```bash
docker compose down
```

Delete the database and uploaded files too:

```bash
docker compose down -v
```

## Demonstration flow

1. Open the application.
2. Upload a legal test image.
3. Explain the score and each evidence reason.
4. Open **Analysis history** to show the MariaDB record.
5. Inspect the code to explain MIME validation, random filenames, prepared SQL statements, and upload-directory restrictions.

## Security controls included

- MIME type checked using PHP `finfo`, not the filename extension.
- File size limited to 8 MB.
- Image decoding checked with `getimagesize()`.
- Random server-side filenames prevent path traversal and collisions.
- Original filenames are displayed with HTML escaping.
- SQL uses PDO prepared statements.
- Upload directory disables PHP/script execution and directory indexes.
- Apache adds `nosniff`, clickjacking, and referrer-policy headers.
- Application database user is separate from the MariaDB root user.
- The app never exposes the uploaded image path as a predictable URL.

## Security lab ideas

Only test containers and applications you own or have explicit permission to assess. Good controlled exercises include:

- Attempt to upload `shell.php` renamed as an image; it should be rejected.
- Attempt to upload a file with a fake `.jpg` extension; MIME validation should reject it.
- Submit a filename containing HTML; output should be escaped.
- Inspect the database queries and explain why prepared statements prevent SQL injection.
- Run `docker inspect` and discuss why secrets in Compose files are suitable for a classroom demo but not production.
- Review the Docker image as a non-root hardening exercise.

## Limitations and future work

The detector is deliberately simple. A real system would need a validated model, a labeled evaluation dataset, calibration, confidence intervals, and abuse/privacy controls. Possible future work includes separating services into Compose containers, adding authentication, adding CSRF protection for multi-user deployments, storing files in object storage, and adding automated tests.
