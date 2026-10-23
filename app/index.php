<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/analyzer.php';

$message = null;
$error = null;
$result = null;

try {
    ensure_schema();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Choose an image smaller than 8 MB.');
        }
        $file = $_FILES['image'];
        if ((int) $file['size'] > MAX_UPLOAD_BYTES) {
            throw new RuntimeException('The maximum upload size is 8 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ALLOWED_MIMES, true)) {
            throw new RuntimeException('Only JPEG, PNG, WebP, and GIF images are accepted.');
        }
        if (@getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('The uploaded file is not a valid image.');
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
            default => 'bin'
        };
        $stored = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = __DIR__ . '/uploads/' . $stored;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('The server could not store the upload.');
        }
        chmod($destination, 0640);

        $result = analyze_image($destination, $mime);
        $reasons = implode('; ', $result['reasons']);
        $stmt = db()->prepare('INSERT INTO analyses (original_name, stored_name, mime_type, file_size, score, verdict, reasons) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([basename((string) $file['name']), $stored, $mime, (int) $file['size'], $result['score'], $result['verdict'], $reasons]);
        $message = 'Analysis complete. Remember: this result is a heuristic, not proof.';
    }
} catch (Throwable $exception) {
    $error = $exception->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Educational image-origin analysis application">
    <title>PixelProof — Image Origin Lab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar"><div class="brand">PIXEL<span>PROOF</span></div><a href="history.php">Analysis history</a></header>
<main class="shell">
    <section class="hero"><p class="eyebrow">SECURITY ARCHITECTURE LAB / LAMP STACK</p><h1>Can we tell if an image was made by AI?</h1><p class="lede">Upload a picture and inspect a transparent, educational heuristic. The app shows its evidence instead of pretending that detection is certain.</p></section>
    <?php if ($message): ?><div class="notice success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
    <section class="workspace">
        <div class="panel upload-panel">
            <p class="eyebrow">01 / SUBMIT EVIDENCE</p>
            <h2>Upload an image</h2>
            <form method="post" enctype="multipart/form-data">
                <label class="dropzone" for="image"><span class="drop-icon">+</span><strong>Select a picture</strong><small>JPEG, PNG, WebP, or GIF · up to 8 MB</small></label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
                <button type="submit">Run analysis <span>→</span></button>
            </form>
        </div>
        <div class="panel result-panel">
            <p class="eyebrow">02 / INTERPRETATION</p>
            <?php if ($result): ?>
                <div class="score-ring"><strong><?= (int) $result['score'] ?>%</strong><span>AI-likeness</span></div>
                <h2><?= e($result['verdict']) ?></h2>
                <ul class="reasons"><?php foreach ($result['reasons'] as $reason): ?><li><?= e($reason) ?></li><?php endforeach; ?></ul>
            <?php else: ?>
                <div class="empty-state"><span>◌</span><h2>Waiting for an image</h2><p>Your result and the evidence behind it will appear here.</p></div>
            <?php endif; ?>
        </div>
    </section>
    <section class="disclaimer"><strong>Important limitation</strong><p>This classroom prototype uses file metadata and image characteristics. It is not a trained forensic model and cannot reliably prove whether an image was AI-generated.</p></section>
</main>
<footer><span>Built with Ubuntu · Apache · PHP · MariaDB</span><span>Educational prototype / v1.0</span></footer>
</body>
</html>
