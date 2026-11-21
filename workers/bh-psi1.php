<?php
// bh-psi1.php - swaps the homepage terminal's old LCP line for the current PageSpeed category scores.
// Dry run by default. &go=go writes. Backup kept as *.bak-psi1. Self-deletes on success.
header("Content-Type: text/plain; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
$TOKEN = "bh-Psi1Sc0re!";
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo "Not Found"; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
chdir(__DIR__);
$PAT = '#LCP \.{3,} 2\.3s OK#';
$REPL = "PSI ................ PERF 96 / A11Y 94 / BP 96 / SEO 100 OK";
$MARK = "PSI ................ PERF 96";
echo "MODE: " . ($GO ? "GO (writes enabled)" : "DRY RUN (nothing will be written)") . "\n";
$self = basename(__FILE__);
$files = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
  if (!$f->isFile()) continue;
  $rel = substr($f->getPathname(), strlen(__DIR__) + 1);
  if ($rel[0] === '.' || $rel === $self || strpos($rel, 'assets/') === 0 || strpos($rel, 'og-cache/') === 0 || strpos($rel, '.bak') !== false) continue;
  $ext = strtolower($f->getExtension());
  if ($ext !== 'html' && $ext !== 'htm' && $ext !== 'php') continue;
  if ($f->getSize() > 3000000) continue;
  $files[] = $rel;
}
sort($files);
$plan = array();
$total = 0;
foreach ($files as $rel) {
  $s = file_get_contents($rel);
  if ($s === false) continue;
  $c = preg_match_all($PAT, $s, $m);
  if ($c > 0) { $plan[$rel] = $c; $total += $c; echo "FILE $rel: $c match(es)\n"; }
}
echo "total matches: $total in " . count($plan) . " file(s)\n";
if ($total === 0) { echo "ABORT: old LCP line not found (if the PSI line is already live, this is done); nothing written\n"; exit; }
if ($total !== 1) { echo "ABORT: expected exactly one match; nothing written\n"; exit; }
if (!$GO) { echo "DRY RUN OK. Nothing written. Run again with &go=go to write.\n"; exit; }
$rel = array_keys($plan)[0];
$s = file_get_contents($rel);
$cnt = 0;
$new = preg_replace($PAT, $REPL, $s, -1, $cnt);
if ($new === null || $cnt !== 1) { echo "ABORT: replace failed; nothing written\n"; exit; }
$bak = $rel . ".bak-psi1";
if (!copy($rel, $bak)) { echo "ABORT: backup failed; nothing written\n"; exit; }
echo "backup: $bak\n";
if (file_put_contents($rel, $new, LOCK_EX) !== strlen($new)) { copy($bak, $rel); echo "WRITE SHORT; restored\n"; exit; }
clearstatcache();
$back = file_get_contents($rel);
if ($back !== $new || substr_count($back, $MARK) !== 1) { copy($bak, $rel); echo "VERIFY FAILED; restored\n"; exit; }
echo "wrote $rel and verified\n";
@unlink(__FILE__);
echo "worker self-deleted. Backup kept as $bak.\n";
