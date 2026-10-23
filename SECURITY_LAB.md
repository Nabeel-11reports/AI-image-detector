# Authorized Security Lab Worksheet

## Rules of engagement

Test only this local project or containers explicitly assigned by the instructor. Do not scan classmates' machines, public IPs, or services without written authorization.

## Baseline checks

```bash
docker compose up --build -d
curl -I http://localhost:8080
curl -s http://localhost:8080 | grep -E "PixelProof|form"
```

## Upload-validation tests

Use harmless files and record the result:

```bash
curl -i -F "image=@README.md" http://localhost:8080/
curl -i -F "image=@test.jpg;filename=shell.php" http://localhost:8080/
```

Expected result: the first request is rejected because its detected MIME type is not an allowed image. The second should also be rejected unless the content is genuinely a supported image; a filename alone is not trusted.

## Threat model

| Asset | Threat | Control |
|---|---|---|
| Uploaded files | Script execution | `.htaccess`, Apache `<FilesMatch>`, no CGI |
| Database | SQL injection | PDO prepared statements, least-privilege user |
| Web response | XSS through filename | `htmlspecialchars` output encoding |
| Host filesystem | Path traversal | Random generated filenames, `basename` for display |
| Service availability | Oversized upload | 8 MB application limit |
| User privacy | Sensitive image retention | Explain retention and add deletion policy before deployment |

## Findings to discuss

- The single-container LAMP design is convenient for an assignment but increases blast radius.
- Compose environment variables are visible to someone with Docker access; use secrets management in production.
- A heuristic score is not ground truth. False positives and false negatives must be expected.
- Add authentication, CSRF tokens, rate limits, malware scanning, and retention controls before public deployment.
