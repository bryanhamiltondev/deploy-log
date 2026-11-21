<?php
// bh-sl3.php - adds a Stew Leonard's showcase block with a border streak under the homepage Career timeline.
// Dry run by default. &go=go writes. Backup kept as index.html.bak-sl3. Self-deletes on success.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
$TOKEN = 'bh-Sl3Sh0w!';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo "Not Found"; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
chdir(__DIR__);
$F = 'index.html';
$MARK = 'bh-sl-showcase1';
echo "MODE: " . ($GO ? "GO (writes enabled)" : "DRY RUN (nothing will be written)") . "\n";
if (!is_file($F)) { echo "ABORT: index.html missing\n"; exit; }
$html = file_get_contents($F);
if ($html === false || strlen($html) < 2000) { echo "ABORT: index.html unreadable or too small\n"; exit; }
echo "index.html bytes: " . strlen($html) . "\n";
if (strpos($html, $MARK) !== false) { echo "ABORT: marker $MARK already present; nothing written\n"; exit; }
$nH = substr_count($html, 'Career timeline');
$nL = substr_count($html, 'See the build timeline');
echo "needle 'Career timeline' count: $nH (need 1+)\n";
echo "needle 'See the build timeline' count: $nL (need 1)\n";
if ($nH < 1 || $nL !== 1) { echo "ABORT: needle count mismatch; nothing written\n"; exit; }
$p = strpos($html, 'See the build timeline');
$h = ($p === false) ? false : strrpos(substr($html, 0, $p), 'Career timeline');
if ($h === false || $p - $h > 9000) { echo "ABORT: no Career timeline heading near the link; nothing written\n"; exit; }
if ($p <= $h) { echo "ABORT: link appears before heading; nothing written\n"; exit; }
$seg = substr($html, $h, $p - $h);
$best = -1; $bestTag = '';
foreach (array('</ol>', '</ul>', '</table>') as $tag) {
  $pos = strrpos($seg, $tag);
  if ($pos !== false && $pos > $best) { $best = $pos; $bestTag = $tag; }
}
if ($best < 0) { echo "ABORT: no list or table closes between heading and link; nothing written\n"; exit; }
$cut = $h + $best + strlen($bestTag);
echo "insert point: after $bestTag at byte $cut\n";
echo "--- before insert ---\n" . substr($html, max(0, $cut - 220), 220) . "\n";
echo "--- after insert ---\n" . substr($html, $cut, 220) . "\n---\n";
$block = <<<'BLOCK'
<!-- bh-sl-showcase1 -->
<style>
@property --bh-sl-a{syntax:'<angle>';inherits:false;initial-value:0deg}
@keyframes bh-sl-spin{to{--bh-sl-a:360deg}}
.bh-sl-card{position:relative;margin:22px 0 10px;padding:18px;border-radius:12px;display:flex;gap:16px;align-items:center;flex-wrap:wrap}
.bh-sl-ring{position:absolute;inset:0;border-radius:12px;padding:1px;pointer-events:none;background:conic-gradient(from var(--bh-sl-a),rgba(255,255,255,.12) 0deg,#ffffff 40deg,#22d3ee 70deg,rgba(34,211,238,.12) 110deg,rgba(255,255,255,.12) 360deg);-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask:linear-gradient(#000 0 0) content-box exclude,linear-gradient(#000 0 0);animation:bh-sl-spin 4s linear infinite}
@media (prefers-reduced-motion:reduce){.bh-sl-ring{animation:none}}
</style>
<div class="bh-sl-card">
<span class="bh-sl-ring" aria-hidden="true"></span>
<img src="/stew-leonards-logo.png" alt="Stew Leonard's logo" width="64" height="64" loading="lazy" style="width:64px;height:auto;flex:0 0 auto">
<div style="flex:1 1 220px;min-width:0">
<p style="margin:0 0 4px;font-weight:700;font-size:17px">Bryan is the new Hoedown chef at Stew Leonard's</p>
<p style="margin:0 0 6px;font-size:15px">Stop by today and see Bryan at work!</p>
<a href="https://www.stewleonards.com" rel="noopener" style="font-size:15px">Visit stewleonards.com &rarr;</a>
</div>
</div>
<!-- /bh-sl-showcase1 -->
BLOCK;
$new = substr($html, 0, $cut) . "\n" . $block . substr($html, $cut);
echo "new bytes: " . strlen($new) . " (+" . (strlen($new) - strlen($html)) . ")\n";
if (!$GO) { echo "DRY RUN OK. Nothing written. Run again with &go=go to write.\n"; exit; }
$bak = $F . '.bak-sl3';
if (!copy($F, $bak)) { echo "ABORT: backup failed; nothing written\n"; exit; }
echo "backup: $bak (" . filesize($bak) . " bytes)\n";
$w = file_put_contents($F, $new, LOCK_EX);
if ($w !== strlen($new)) { copy($bak, $F); echo "WRITE SHORT; restored from backup\n"; exit; }
clearstatcache();
$back = file_get_contents($F);
if ($back === $new && strpos($back, $MARK) !== false) {
  echo "verify index.html: OK\n";
  @unlink(__FILE__);
  echo "worker self-deleted. Backup kept as index.html.bak-sl3.\n";
} else {
  copy($bak, $F);
  echo "VERIFY FAILED; restored from backup\n";
}
