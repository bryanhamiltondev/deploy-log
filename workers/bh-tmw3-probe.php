<?php
// bh-tmw3-probe.php - READ-ONLY. Prints raw markup around the spots the Tomorrowland fix needs. Writes nothing. Self-deletes.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
$TOKEN = 'bh-Tmw3Pr0be!';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo "Not Found"; exit; }
chdir(__DIR__);

function snip($label, $html, $pos, $before, $after) {
  $start = max(0, $pos - $before);
  echo "== $label at $pos (file bytes " . strlen($html) . ")\n";
  echo substr($html, $start, $before + $after) . "\n\n";
}

function show_all($label, $html, $needle, $before, $after, $max) {
  $n = 0; $off = 0;
  while ($n < $max && ($p = strpos($html, $needle, $off)) !== false) {
    snip($label . ' hit ' . ($n + 1), $html, $p, $before, $after);
    $off = $p + 1;
    $n++;
  }
  if ($n === 0) { echo "== $label: NO HIT for $needle\n\n"; }
}

function grep_lines($label, $text, $pat, $max) {
  $n = 0;
  $lines = explode("\n", $text);
  foreach ($lines as $i => $ln) {
    if (stripos($ln, $pat) !== false) {
      echo "$label L" . ($i + 1) . ": " . substr(trim($ln), 0, 300) . "\n";
      $n++;
      if ($n >= $max) { break; }
    }
  }
  if ($n === 0) { echo "$label: no line containing $pat\n"; }
  echo "\n";
}

// 1. NEW ARTICLE: span from <article to the h1 (the leftover intro lives here)
$A = 'writing/tomorrowland-winter-colorado/index.html';
if (!is_file($A)) {
  echo "MISSING $A\n\n";
} else {
  $a = file_get_contents($A);
  echo "ARTICLE bytes: " . strlen($a) . "\n";
  $ao = strpos($a, '<article');
  $h1 = strpos($a, '<h1');
  echo "article open at: " . var_export($ao, true) . "  h1 at: " . var_export($h1, true) . "\n\n";
  $from = ($ao === false) ? 0 : $ao;
  $to = ($h1 === false) ? min(strlen($a), $from + 3000) : min(strlen($a), $h1 + 200);
  echo "== ARTICLE SPAN (article open to h1 + 200)\n";
  echo substr($a, $from, $to - $from) . "\n\n";
  grep_lines('ARTICLE', $a, 'application/ld+json', 5);
  grep_lines('ARTICLE', $a, 'dateModified', 5);
  grep_lines('ARTICLE', $a, 'datePublished', 5);
  grep_lines('ARTICLE', $a, 'dead-at-file', 10);
  grep_lines('ARTICLE', $a, 'canonical', 3);
  grep_lines('ARTICLE', $a, '<title', 3);
}

// 2. WRITING HUB: where the article count and the article cards live
$W = 'writing/index.html';
if (is_file($W)) {
  $w = file_get_contents($W);
  echo "HUB bytes: " . strlen($w) . "\n\n";
  grep_lines('HUB', $w, '27', 12);
  grep_lines('HUB', $w, 'articles', 8);
  show_all('HUB', $w, 'php-l-passed-my-bug', 700, 500, 1);
  show_all('HUB', $w, 'dead-at-file-30-of-62', 500, 300, 1);
} else {
  echo "MISSING $W\n\n";
}

// 3. HOMEPAGE: article card cap and expander
$H = 'index.html';
if (is_file($H)) {
  $h = file_get_contents($H);
  echo "HOME bytes: " . strlen($h) . "\n\n";
  show_all('HOME', $h, 'php-l-passed-my-bug', 600, 300, 1);
  grep_lines('HOME', $h, 'bhartcap', 6);
  grep_lines('HOME', $h, 'expander', 6);
  grep_lines('HOME', $h, 'Show more', 4);
  show_all('HOME', $h, 'bh-art10', 100, 200, 1);
} else {
  echo "MISSING $H\n\n";
}

// 4. RECEIPTS: first row markup and hero counters
$R = 'receipts/index.html';
if (is_file($R)) {
  $r = file_get_contents($R);
  echo "RECEIPTS bytes: " . strlen($r) . "\n\n";
  show_all('RECEIPTS', $r, '<tr><td class=d', 0, 700, 1);
  grep_lines('RECEIPTS', $r, 'changes shipped', 3);
  grep_lines('RECEIPTS', $r, 'live-verified', 3);
} else {
  echo "MISSING $R\n\n";
}

// 5. OG manifest shape (skipped last time)
if (is_file('og-manifest.json')) {
  $o = file_get_contents('og-manifest.json');
  echo "== OG MANIFEST bytes " . strlen($o) . " head\n" . substr($o, 0, 700) . "\n\n";
} else {
  echo "MISSING og-manifest.json\n\n";
}

echo "PROBE DONE. Nothing was written.\n";
@unlink(__FILE__);
echo "probe self-deleted\n";
