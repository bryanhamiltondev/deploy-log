<?php
// bh-pages1036.php - fix the live DJC page count on the BH homepage card.
// Why: bh-pages1090c's proxy counted ONLY artists+cities+venues children
// (FAQ excluded) but the card copy says "~ pages", so the live rewrite
// dropped the card to 257. Real full-sitemap total right now is 1,036
// (artists 99 + cities 79 + venues 83 + FAQ 762 + genres 13). The old
// ~1,090 static was an overestimate.
// 1) index.html span: ~1,090 -> ~1,036 (keeps data-prefix + data-live).
// 2) bh-djc-count.php: rewritten to crawl the sitemap.xml INDEX and count
//    every <loc> across ALL child sitemaps (age-proof to new sections),
//    browser UA, 6h cache, stale fallback. Stale cache file deleted on GO
//    so the card updates immediately.
// rack.js is untouched by this worker. DRY default; &go=go writes;
// &selftest=1 fixtures. Backups *.bak-p1036. Self-deletes after GO.
// Token: bh-PgD!
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
define('BUILD', 'bh-pages1036 v1 2026-11-22');
define('TOKEN', 'bh-PgD!');
if (!isset($_GET['t']) || !hash_equals(TOKEN, (string)$_GET['t'])) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: ' . BUILD . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (read-only fixtures)' : 'DRY (nothing written)')) . $NL;

// ---- Patch 1: index.html span ~1,090 -> ~1,036 ----
$N1 = '<span class="bh-cnt" data-to="1090" data-prefix="~" data-live="djc-pages" data-grp="1">~1,090</span>+ pages, live since 2015';
$R1 = '<span class="bh-cnt" data-to="1036" data-prefix="~" data-live="djc-pages" data-grp="1">~1,036</span>+ pages, live since 2015';

// ---- Patch 2: bh-djc-count.php rewritten as a full sitemap-index crawler ----
$PROXY = <<<'PHPEOF'
<?php
// bh-djc-count.php - live thedjcalendar.com TOTAL page count for the BH card.
// Crawls the sitemap.xml index and counts every <loc> in every child
// sitemap (artists, cities, venues, FAQ, genres, ...). Age-proof: new
// child sitemaps are counted automatically. 6h disk cache, stale fallback.
$CACHE = __DIR__ . '/.djc-count-cache.json';
$TTL = 21600;
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (is_file($CACHE) && (time() - filemtime($CACHE)) < $TTL) { echo file_get_contents($CACHE); exit; }
ini_set('user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 BHCount/1.0');
function bhcc_children($xml) {
    $out = array();
    if (preg_match_all('#<loc>([^<]+)</loc>#', $xml, $m)) { $out = $m[1]; }
    return $out;
}
function bhcc_total($idx) {
    $total = 0;
    foreach (bhcc_children($idx) as $u) {
        $u = trim($u);
        if ($u === '') { continue; }
        if (substr($u, -4) === '.xml') {
            $x = @file_get_contents($u);
            if ($x === false) { continue; }
            $total += count(bhcc_children($x));
        } else {
            $total++;
        }
    }
    return $total;
}
$idx = @file_get_contents('https://thedjcalendar.com/sitemap.xml');
$total = ($idx === false) ? 0 : bhcc_total($idx);
if ($total === 0) {
    if (is_file($CACHE)) { echo file_get_contents($CACHE); exit; }
    http_response_code(502);
    echo '{"total":0}';
    exit;
}
$out = json_encode(array('total' => $total, 'ts' => time()));
@file_put_contents($CACHE, $out, LOCK_EX);
echo $out;
PHPEOF;

function rd($f, &$msg) { $s = @file_get_contents($f); if ($s === false) { $msg = 'ABORT: cannot read ' . $f; return false; } return $s; }
function cnt1($s, $needle, $name, &$msg) {
    $n = substr_count($s, $needle);
    if ($n === 0) { $msg = 'ABORT: ' . $name . ' not found'; return false; }
    if ($n > 1) { $msg = 'ABORT: ' . $name . ' count ' . $n . ', want 1'; return false; }
    return true;
}

if ($SELFTEST) {
    $fail = 0;
    // fmtc math + child extraction fixtures
    $idxfix = '<?xml version="1.0"?><sitemapindex><sitemap><loc>https://thedjcalendar.com/sitemap-artists.xml</loc></sitemap><sitemap><loc>https://thedjcalendar.com/sitemap-cities.xml</loc></sitemap></sitemapindex>';
    preg_match_all('#<loc>([^<]+)</loc>#', $idxfix, $m);
    $kids = $m[1];
    if (count($kids) === 2 && $kids[0] === 'https://thedjcalendar.com/sitemap-artists.xml') { echo 'selftest 1 index-child extraction: PASS' . $NL; } else { $fail++; echo 'selftest 1 index-child extraction: FAIL' . $NL; }
    $urlfix = '<?xml version="1.0"?><urlset><url><loc>https://thedjcalendar.com/artist/mau-p/</loc></url><url><loc>https://thedjcalendar.com/city/chicago/</loc></url></urlset>';
    preg_match_all('#<loc>([^<]+)</loc>#', $urlfix, $m2);
    if (count($m2[1]) === 2) { echo 'selftest 2 urlset extraction: PASS' . $NL; } else { $fail++; echo 'selftest 2 urlset extraction: FAIL' . $NL; }
    // html needle present / absent / doubled
    $fh = '<div><span class="bh-cnt" data-to="1090" data-prefix="~" data-live="djc-pages" data-grp="1">~1,090</span>+ pages, live since 2015</div>';
    if (cnt1($fh, $N1, 'index needle', $m3)) { echo 'selftest 3 html needle once: PASS' . $NL; } else { $fail++; echo 'selftest 3 html needle once: FAIL (' . $m3 . ')' . $NL; }
    $m3 = '';
    if (cnt1($fh . $fh, $N1, 'index needle', $m3) === false && strpos($m3, 'count 2') !== false) { echo 'selftest 4 doubled-needle abort: PASS' . $NL; } else { $fail++; echo 'selftest 4 doubled-needle abort: FAIL' . $NL; }
    $r1ok = strpos($R1, 'data-to="1036"') !== false && strpos($R1, '~1,036') !== false && strpos($R1, 'data-prefix="~"') !== false && strpos($R1, 'data-live="djc-pages"') !== false;
    if ($r1ok) { echo 'selftest 5 replacement intact attrs: PASS' . $NL; } else { $fail++; echo 'selftest 5 replacement intact attrs: FAIL' . $NL; }
    echo 'selftest: build ' . BUILD . ' - ' . ($fail === 0 ? 'ALL PASS' : $fail . ' FAILURE(S)') . $NL;
    exit;
}

$HTML = __DIR__ . '/index.html';
$h = rd($HTML, $msg); if ($h === false) { echo $msg . $NL; exit; }
echo 'index.html bytes: ' . strlen($h) . $NL;
$msg = '';
if (!cnt1($h, $N1, 'index needle', $msg)) { echo $msg . $NL; exit; }
$h2 = str_replace($N1, $R1, $h);
if (substr_count($h2, 'data-to="1036"') !== 1) { echo 'ABORT: html post-check' . $NL; exit; }
echo 'patch ok (html delta ' . (strlen($h2) - strlen($h)) . ')' . $NL;
if (!$GO) { echo 'DRY OK. &go=go to write.' . $NL; exit; }
$bh = $HTML . '.bak-p1036';
if (!copy($HTML, $bh)) { echo 'ABORT: backup failed' . $NL; exit; }
echo 'backup: index.html.bak-p1036' . $NL;
if (file_put_contents($HTML, $h2, LOCK_EX) !== strlen($h2)) { copy($bh, $HTML); echo 'HTML WRITE SHORT; restored' . $NL; exit; }
clearstatcache();
if (file_get_contents($HTML) !== $h2) { copy($bh, $HTML); echo 'HTML VERIFY FAIL; restored' . $NL; exit; }
@file_put_contents(__DIR__ . '/bh-djc-count.php', $PROXY);
echo 'wrote bh-djc-count.php (sitemap-index crawler, 6h cache)' . $NL;
@unlink(__DIR__ . '/.djc-count-cache.json');
echo 'stale count cache deleted' . $NL;
@unlink(__FILE__);
echo 'self-deleted. Live check: ?cb=<rand> on homepage; hit bh-djc-count.php twice if first read is the old 257 (cache rebuild).' . $NL;
