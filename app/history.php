<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
ensure_schema();
$rows = db()->query('SELECT original_name, mime_type, file_size, score, verdict, reasons, created_at FROM analyses ORDER BY id DESC LIMIT 50')->fetchAll();
function kb(int $bytes): string { return number_format($bytes / 1024, 1) . ' KB'; }
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>PixelProof — History</title><link rel="stylesheet" href="style.css"></head>
<body><header class="topbar"><div class="brand">PIXEL<span>PROOF</span></div><a href="index.php">New analysis</a></header><main class="shell"><section class="hero compact"><p class="eyebrow">03 / AUDIT TRAIL</p><h1>Recent analyses</h1><p class="lede">A small MariaDB-backed record of the evidence reviewed by the application.</p></section><div class="table-wrap"><table><thead><tr><th>File</th><th>Type</th><th>Size</th><th>Score</th><th>Verdict</th><th>Time</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= e($row['original_name']) ?><small><?= e($row['reasons']) ?></small></td><td><?= e($row['mime_type']) ?></td><td><?= kb((int) $row['file_size']) ?></td><td><strong><?= (int) $row['score'] ?>%</strong></td><td><?= e($row['verdict']) ?></td><td><?= e($row['created_at']) ?></td></tr><?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="6">No analyses have been recorded yet.</td></tr><?php endif; ?></tbody></table></div></main><footer><span>PixelProof audit trail</span><span>Maximum 50 recent records</span></footer></body></html>
