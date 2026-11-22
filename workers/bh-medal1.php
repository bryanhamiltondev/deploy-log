<?php
// bh-medal1.php - READ-ONLY probe. Writes nothing.
// 1) Lists every file/line where a medal word (gold/silver/bronze) appears near a digit.
// 2) Locates every CODER LEGION block (wide context) so the SVG-badge swap gets exact needles.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
$TOKEN = 'bh-M3dalPr0b3!';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo 'Not Found'; exit; }
chdir(__DIR__);
$self = basename(__FILE__);
$NL = chr(10);
echo 'PROBE (read-only, writes nothing)' . $NL;
echo 'target live profile: gold 1 / silver 7 / bronze 41 + Community Voice badge' . $NL . $NL;
$files = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
  if (!$f->isFile()) continue;
  $rel = substr($f->getPathname(), strlen(__DIR__) + 1);
  if ($rel === '' || $rel[0] === '.' || $rel === $self) continue;
  if (strpos($rel, 'og-cache/') === 0 || strpos($rel, '.bak') !== false) continue;
  $ext = strtolower($f->getExtension());
  if ($ext !== 'html' && $ext !== 'htm' && $ext !== 'php' && $ext !== 'js') continue;
  if ($f->getSize() > 3000000) continue;
  $files[] = $rel;
}
sort($files);
echo '=== PART 1: medal words near digits ===' . $NL;
$total = 0;
foreach ($files as $rel) {
  $s = file_get_contents($rel);
  if ($s === false) continue;
  if (!preg_match('/gold|silver|bronze/i', $s)) continue;
  $lines = preg_split('/(\r\n|\n|\r)/', $s);
  foreach ($lines as $i => $line) {
    if (!preg_match('/gold|silver|bronze/i', $line)) continue;
    if (preg_match_all('/.{0,70}(?:gold|silver|bronze).{0,70}/i', $line, $m)) {
      foreach ($m[0] as $ctx) {
        if (!preg_match('/\d/', $ctx)) continue;
        $total++;
        echo $rel . ' line ' . ($i + 1) . ': ' . str_replace(chr(9), ' ', trim($ctx)) . $NL;
      }
    }
  }
}
echo 'medal-count mentions with digits: ' . $total . $NL . $NL;
echo '=== PART 2: CODER LEGION blocks (wide context) ===' . $NL;
$cltotal = 0;
foreach ($files as $rel) {
  $s = file_get_contents($rel);
  if ($s === false) continue;
  if (stripos($s, 'coder legion') === false && stripos($s, 'coderlegion') === false) continue;
  $lines = preg_split('/(\r\n|\n|\r)/', $s);
  foreach ($lines as $i => $line) {
    if (stripos($line, 'coder legion') === false && stripos($line, 'coderlegion') === false) continue;
    $cltotal++;
    $start = max(0, $i - 3);
    $end = min(count($lines) - 1, $i + 3);
    echo '--- ' . $rel . ' lines ' . ($start + 1) . '-' . ($end + 1) . ' ---' . $NL;
    for ($j = $start; $j <= $end; $j++) {
      $t = trim($lines[$j]);
      if ($t === '') continue;
      echo ($j + 1) . '| ' . $t . $NL;
    }
    echo $NL;
  }
}
echo 'coder-legion line hits: ' . $cltotal . $NL;
echo 'nothing was written. Probe self-deletes on second run with the same token.' . $NL;
$runfile = __DIR__ . '/.bh-medal1-ran';
if (file_exists($runfile)) { @unlink(__FILE__); @unlink($runfile); echo 'second run: probe self-deleted.' . $NL; }
else { @file_put_contents($runfile, 'x'); }
