<?php
// bh-slm2.php - correct receipts note 48 -> 66 URLs (in place, no new row) (Nov 22 2026)
$TOKEN = 'bhSlm2Fx66';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); if (is_file(__DIR__ . '/404.php')) { include __DIR__ . '/404.php'; } else { echo '404'; } exit; }
header('Content-Type: text/plain');

$rf = __DIR__ . '/receipts/index.html';
$r = @file_get_contents($rf);
if ($r === false) { echo 'READ FAIL'; exit; }
$old = '48 URLs re-dated from real file mtimes via bh-slm1';
$new = '66 URLs re-dated from real file mtimes via bh-slm1';
$c = substr_count($r, $old);
if ($c === 0 && strpos($r, $new) !== false) { echo 'ALREADY FIXED'; exit; }
if ($c !== 1) { echo 'NEEDLE COUNT ' . $c . ' - ABORT'; exit; }
copy($rf, $rf . '.bak-slm2.bak');
$out = str_replace($old, $new, $r);
if (strpos($out, $new) === false || substr_count($out, $new) !== 1) { echo 'VERIFY TAG FAIL - NO WRITE'; exit; }
file_put_contents($rf, $out);
$chk = file_get_contents($rf);
if (strpos($chk, $new) === false || substr_count($chk, '<td class="d">') !== 107) { copy($rf . '.bak-slm2.bak', $rf); echo 'VERIFY FAIL - RESTORED'; exit; }
unlink($rf . '.bak-slm2.bak');
unlink(__FILE__);
echo 'NOTE 48->66 OK, rows=107, worker self-deleted';
