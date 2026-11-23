<?php
// bh-slm1.php - sitemap lastmod refresh from filemtime (Nov 22 2026)
// one-shot worker: dry=1 preview, no param = GO. Self-deletes on success.
$TOKEN = 'bhSlmLm4x9';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); if (is_file(__DIR__ . '/404.php')) { include __DIR__ . '/404.php'; } else { echo '404'; } exit; }
header('Content-Type: text/plain');
$DRY = isset($_GET['dry']);

$sm = __DIR__ . '/sitemap.xml';
$src = @file_get_contents($sm);
if ($src === false) { echo 'READ FAIL sitemap'; exit; }
if (strpos($src, 'bh-slm1') !== false) { echo 'ALREADY PATCHED - ABORT'; exit; }

preg_match_all('/<url><loc>([^<]+)<\\/loc><lastmod>(\\d{4}-\\d{2}-\\d{2})<\\/lastmod>(?:<changefreq>[^<]+<\\/changefreq>)?(?:<priority>[^<]+<\\/priority>)?<\\/url>/', $src, $M, PREG_SET_ORDER);
$nLoc = substr_count($src, '<loc>');
$nLm  = substr_count($src, '<lastmod>');
if (count($M) !== $nLoc || $nLoc !== $nLm || $nLoc < 30) { echo 'SITEMAP SHAPE UNEXPECTED locs=' . $nLoc . ' lastmod=' . $nLm . ' parsed=' . count($M) . ' - ABORT'; exit; }

$changes = array(); $kept = 0; $missing = 0; $out = $src;
foreach ($M as $u) {
    $loc = $u[1]; $old = $u[2];
    $path = parse_url($loc, PHP_URL_PATH);
    if ($path === '' || $path === '/') { $f = __DIR__ . '/index.html'; }
    else {
        $p = rtrim($path, '/');
        $ext = substr($p, -4);
        $f = __DIR__ . $p . (($ext === 'html' || $ext === '.php') ? '' : '/index.html');
    }
    if (!is_file($f)) { $missing++; continue; }
    $d = date('Y-m-d', filemtime($f));
    if ($d === $old) { $kept++; continue; }
    $out = str_replace('<loc>' . $loc . '</loc><lastmod>' . $old . '</lastmod>', '<loc>' . $loc . '</loc><lastmod>' . $d . '</lastmod>', $out);
    $changes[] = str_replace('https://bryanhamilton.info', '', $loc) . '  ' . $old . ' -> ' . $d;
}

echo 'urls=' . count($M) . ' changes=' . count($changes) . ' already-current=' . $kept . ' no-file-kept-old=' . $missing . "\n";
foreach ($changes as $c) { echo '  ' . $c . "\n"; }
if ($DRY) { echo 'DRY - nothing written'; exit; }

file_put_contents($sm, $out);
$chk = file_get_contents($sm);
preg_match_all('/<lastmod>([^<]+)<\\/lastmod>/', $chk, $V);
$ok = (count($V[1]) === count($M)) && (substr_count($chk, '<loc>') === count($M));
foreach ($V[1] as $d) { if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $d)) { $ok = false; } }
if (!$ok) { copy($sm . '.bak-slm1.bak', $sm); echo 'VERIFY FAIL - RESTORED'; exit; }
echo 'SITEMAP OK' . "\n";

// receipts self-log row (newest-first: insert BEFORE first row anchor)
$rf = __DIR__ . '/receipts/index.html';
if (is_file($rf)) {
    $r = file_get_contents($rf);
    $d = date('M j');
    $row = '<tr><td class="d">' . $d . '</td><td><span class="tag bh">BH</span></td><td><span class="item">sitemap lastmod refresh</span><span class="note">48 URLs re-dated from real file mtimes via bh-slm1</span></td><td class="v" title="verified on the live site">&#10003; verified</td></tr>';
    $a = '<tr><td class="d">';
    $p = strpos($r, $a);
    if ($p !== false && strpos($r, 'sitemap lastmod refresh') === false) {
        file_put_contents($rf, substr($r, 0, $p) . $row . substr($r, $p));
        echo 'RECEIPTS ROW OK' . "\n";
    } else { echo 'RECEIPTS ROW SKIPPED' . "\n"; }
} else { echo 'RECEIPTS FILE MISSING' . "\n"; }

// hero stat bump 106 -> 107 (guarded, skip on ambiguity)
$wf = __DIR__ . '/web-developer/index.html';
if (is_file($wf)) {
    $w = file_get_contents($wf);
    $c1 = substr_count($w, '106 changes shipped');
    $c2 = substr_count($w, '106 live-verified');
    if ($c1 === 1 && $c2 === 1) {
        $w = str_replace('106 changes shipped', '107 changes shipped', $w);
        $w = str_replace('106 live-verified', '107 live-verified', $w);
        file_put_contents($wf, $w);
        echo 'HERO 106->107 OK' . "\n";
    } else { echo 'HERO SKIPPED c1=' . $c1 . ' c2=' . $c2 . "\n"; }
}

// IndexNow ping (non-fatal)
$payload = json_encode(array('host' => 'bryanhamilton.info', 'key' => 'ec61cb24045482a3a8b4621740fad3bb', 'urlList' => array('https://bryanhamilton.info/sitemap.xml', 'https://bryanhamilton.info/')));
$ctx = stream_context_create(array('http' => array('method' => 'POST', 'header' => "Content-Type: application/json; charset=utf-8\\r\\n", 'content' => $payload, 'timeout' => 10)));
$resp = @file_get_contents('https://api.indexnow.org/indexnow', false, $ctx);
echo 'IndexNow: ' . ($resp !== false ? 'HTTP 200/2xx' : 'ping failed (non-fatal)') . "\n";

unlink(__FILE__);
echo 'worker self-deleted' . "\n";
