<?php
// bh-pages1090b.php - DJ Calendar highlight card: age-proofed "~1,090+ pages".
// Fixes two things:
// 1) TILDE: the count-up engine overwrites span text with plain digits
//    (toFixed, no grouping, no prefix - data-grp has no code path in rack.js).
//    run() now honors data-prefix and a grouped final frame (fmtc), so the
//    card shows ~1,090 during AND after the animation. Other .bh-cnt spans
//    have no data-prefix and are untouched (plain digits as before).
// 2) AGE-PROOF: rack.js bh-djclive1 block (appended after count-up) fetches
//    the same-origin proxy bh-djc-count.php (tokenless, 6h server cache,
//    counts sitemap-artists+cities+venues children) and rewrites the span's
//    data-to + text when the count moves. Static ~1,090 stays as fallback for
//    crawlers/no-JS. Proxy file written next to the worker.
// DRY default; &go=go writes; &selftest=1 fixture tests. Backups *.bak-p1090b.
// Self-deletes after GO. Token: bh-PgB!
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
define('BUILD', 'bh-pages1090b v2 2026-11-22');
define('TOKEN', 'bh-PgB!');
if (!isset($_GET['t']) || !hash_equals(TOKEN, (string)$_GET['t'])) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: ' . BUILD . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (read-only fixtures)' : 'DRY (nothing written)')) . $NL;

// ---- Patch 1: index.html highlight span ----
$N1 = '<span class="bh-cnt" data-to="1000" data-grp="1">1,000</span>+ pages, live since 2015';
$R1 = '<span class="bh-cnt" data-to="1090" data-prefix="~" data-live="djc-pages" data-grp="1">~1,090</span>+ pages, live since 2015';

// ---- Patch 2: rack.js count-up prefix + grouped finals ----
$N2 = 'function run(el){';
$R2 = 'function fmtc(n,grp){n=Math.round(n);return grp?(n>=1000?String(Math.floor(n/1000))+","+String(1000+(n%1000)).slice(1):String(n)):String(n);} '
    . "function run(el){var pre=el.getAttribute('data-prefix')||'';var grp=el.getAttribute('data-grp')==='1';";
$N3 = 'if(reduced||isNaN(to)){el.textContent=to.toFixed(dec);return;}';
$R3 = 'if(reduced||isNaN(to)){el.textContent=pre+(grp?fmtc(to,1):to.toFixed(dec));return;}';
$N4 = 'function step(ts){if(!t0)t0=ts;var p=Math.min(1,(ts-t0)/dur);p=1-Math.pow(1-p,3);el.textContent=(from+(to-from)*p).toFixed(dec);if(p<1)requestAnimationFrame(step);}';
$R4 = 'function step(ts){if(!t0)t0=ts;var p=Math.min(1,(ts-t0)/dur);p=1-Math.pow(1-p,3);var cur=from+(to-from)*p;var final=p>=1;el.textContent=(final&&pre)?pre+fmtc(to,1):(pre?pre+fmtc(cur,1):(cur).toFixed(dec));if(p<1)requestAnimationFrame(step);}';

// ---- Patch 3: rack.js age-proof block, appended inside main IIFE-safe zone ----
$N5 = '}catch(e){}

/* 2. case-studies';
$R5 = 'try{
 var live=document.querySelectorAll('[data-live="djc-pages"]');
 if(live.length){
  fetch('/bh-djc-count.php',{cache:'no-store'}).then(function(r){return r.ok?r.json():null;}).then(function(d){
   if(!d||!d.total)return;
   live.forEach(function(el){el.setAttribute('data-to',d.total);el.textContent='~'+d.total.toLocaleString('en-US');});
  }).catch(function(){});
 }
}catch(e){}

/* 2. case-studies';

$PROXY = '<?php
// bh-djc-count.php - live thedjcalendar.com page count for the BH homepage card.
// Counts sitemap-artists + sitemap-cities + sitemap-venues children (the
// ~main-site pages; FAQ long-tail deliberately excluded). 6h cache.
$CACHE = __DIR__ . "/.djc-count-cache.json";
$TTL = 21600;
header("Content-Type: application/json");
header("Cache-Control: no-store");
if (is_file($CACHE) && (time() - filemtime($CACHE)) < $TTL) { echo file_get_contents($CACHE); exit; }
$segs = array("sitemap-artists.xml", "sitemap-cities.xml", "sitemap-venues.xml");
$total = 0;
foreach ($segs as $s) {
    $x = @file_get_contents("https://thedjcalendar.com/" . $s);
    if ($x === false) { continue; }
    $total += preg_match_all("#<loc>#", $x);
}
if ($total === 0) {
    if (is_file($CACHE)) { echo file_get_contents($CACHE); exit; }
    http_response_code(502);
    echo "{\"total\":0}";
    exit;
}
$out = json_encode(array("total" => $total, "ts" => time()));
@file_put_contents($CACHE, $out, LOCK_EX);
echo $out;
';

function rd($f, &$msg) { $s = @file_get_contents($f); if ($s === false) { $msg = 'ABORT: cannot read ' . $f; return false; } return $s; }
function cnt1($s, $needle, $name, &$msg) {
    $n = substr_count($s, $needle);
    if ($n === 0) { $msg = 'ABORT: ' . $name . ' not found'; return false; }
    if ($n > 1) { $msg = 'ABORT: ' . $name . ' count ' . $n . ', want 1'; return false; }
    return true;
}

function apply_all($html, $js, $P, &$msg) {
    if (!cnt1($html, $P['N1'], 'index needle', $msg)) return false;
    $html2 = str_replace($P['N1'], $P['R1'], $html);
    if (substr_count($html2, 'data-live="djc-pages"') !== 1) { $msg = 'ABORT: html post-check'; return false; }
    foreach (array(array($P['N2'],$P['R2'],'rack run('), array($P['N3'],$P['R3'],'rack reduced'), array($P['N4'],$P['R4'],'rack step'), array($P['N5'],$P['R5'],'rack append')) as $p) {
        if (!cnt1($js, $p[0], $p[1], $msg)) return false;
    }
    $js2 = str_replace($P['N2'], $P['R2'], $js);
    $js2 = str_replace($P['N3'], $P['R3'], $js2);
    $js2 = str_replace($P['N4'], $P['R4'], $js2);
    $js2 = str_replace($P['N5'], $P['R5'], $js2);
    if (substr_count($js2, 'data-live="djc-pages"') !== 1 || substr_count($js2, 'fmtc(') < 3) { $msg = 'ABORT: rack post-check'; return false; }
    $msg = 'patch ok (html delta ' . (strlen($html2) - strlen($html)) . ', rack delta ' . (strlen($js2) - strlen($js)) . ')';
    return array($html2, $js2);
}

if ($SELFTEST) {
    $fail = 0;
    $fh = '<span class="highlight-value"><span class="bh-cnt" data-to="1000" data-grp="1">1,000</span>+ pages, live since 2015</span>';
    $fj = "/* 1. count-up */try{function run(el){el.textContent='x';}}catch(e){}\n\n/* 2. case-studies */";
    $m = ''; $r = apply_all($fh, $fj, get_defined_vars(), $m);
    // get_defined_vars trick fails scope; rebuild explicit patches
    $P = array('N1'=>$N1,'R1'=>$R1,'N2'=>$N2,'R2'=>$R2,'N3'=>$N3,'R3'=>$R3,'N4'=>$N4,'R4'=>$R4,'N5'=>$N5,'R5'=>$R5);
    $m = ''; $r = apply_all($fh, $fj, $P, $m);
    if (is_array($r) && strpos($r[0], 'data-to="1090" data-prefix="~" data-live="djc-pages"') !== false && strpos($r[1], 'bh-djc-count.php') !== false && strpos($r[1], "data-prefix')||''") !== false) {
        echo 'selftest 1 both-files patch: PASS' . $NL;
    } else { $fail++; echo 'selftest 1 both-files patch: FAIL (' . $m . ')' . $NL; }
    $m = ''; $r2 = apply_all($fh . $fh, $fj, $P, $m);
    if ($r2 === false) { echo 'selftest 2 double-needle abort: PASS' . $NL; } else { $fail++; echo 'selftest 2 double-needle abort: FAIL' . $NL; }
    $m = ''; $r3 = apply_all(str_replace('data-to="1000"', 'data-to="999"', $fh), $fj, $P, $m);
    if ($r3 === false && strpos($m, 'index needle') !== false) { echo 'selftest 3 missing-html-needle abort: PASS' . $NL; } else { $fail++; echo 'selftest 3 missing-html-needle abort: FAIL' . $NL; }
    $m = ''; $r4 = apply_all($fh, str_replace('/* 2. case-studies */', '/* 2. elsewhere */', $fj), $P, $m);
    if ($r4 === false && strpos($m, 'rack append') !== false) { echo 'selftest 4 missing-append-anchor abort: PASS' . $NL; } else { $fail++; echo 'selftest 4 missing-append-anchor abort: FAIL' . $NL; }
    echo 'selftest: build ' . BUILD . ' - ' . ($fail === 0 ? 'ALL PASS' : $fail . ' FAILURE(S)') . $NL;
    exit;
}

$HTML = __DIR__ . '/index.html';
$JS = __DIR__ . '/assets/js/rack.js';
$h = rd($HTML, $msg); if ($h === false) { echo $msg . $NL; exit; }
$j = rd($JS, $msg); if ($j === false) { echo $msg . $NL; exit; }
echo 'index.html bytes: ' . strlen($h) . $NL . 'rack.js bytes: ' . strlen($j) . $NL;
$P = array('N1'=>$N1,'R1'=>$R1,'N2'=>$N2,'R2'=>$R2,'N3'=>$N3,'R3'=>$R3,'N4'=>$N4,'R4'=>$R4,'N5'=>$N5,'R5'=>$R5);
$msg = '';
$r = apply_all($h, $j, $P, $msg);
if (!is_array($r)) { echo $msg . $NL; exit; }
echo $msg . $NL;
if (!$GO) { echo 'DRY OK. &go=go to write.' . $NL; exit; }
$bh = $HTML . '.bak-p1090b'; $bj = $JS . '.bak-p1090b';
if (!copy($HTML, $bh) || !copy($JS, $bj)) { echo 'ABORT: backup failed' . $NL; exit; }
echo 'backups: index.html.bak-p1090b, assets/js/rack.js.bak-p1090b' . $NL;
if (file_put_contents($HTML, $r[0], LOCK_EX) !== strlen($r[0])) { copy($bh, $HTML); echo 'HTML WRITE SHORT; restored' . $NL; exit; }
clearstatcache();
if (file_get_contents($HTML) !== $r[0]) { copy($bh, $HTML); echo 'HTML VERIFY FAIL; restored' . $NL; exit; }
if (file_put_contents($JS, $r[1], LOCK_EX) !== strlen($r[1])) { copy($bj, $JS); echo 'JS WRITE SHORT; restored' . $NL; exit; }
clearstatcache();
if (file_get_contents($JS) !== $r[1]) { copy($bj, $JS); echo 'JS VERIFY FAIL; restored' . $NL; exit; }
@file_put_contents(__DIR__ . '/bh-djc-count.php', $PROXY);
echo 'wrote bh-djc-count.php (6h-cached DJC page-count proxy)' . $NL;
$bumped = 0;
$dirit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
foreach ($dirit as $f) {
    if (!$f->isFile()) continue;
    $rel = substr($f->getPathname(), strlen(__DIR__) + 1);
    if (strpos($rel, 'assets/') === 0 || strpos($rel, '.bak') !== false || $rel === $HTML || $rel === 'bh-djc-count.php') continue;
    $ext = strtolower($f->getExtension());
    if ($ext !== 'html' && $ext !== 'php') continue;
    if ($f->getSize() > 3000000) continue;
    $s = @file_get_contents($f->getPathname());
    if ($s === false || strpos($s, 'rack.js?v=') === false) continue;
    $n = 0;
    $r2 = preg_replace_callback('/rack\\.js\\?v=(\\d+)/', function ($mm) use (&$n) { $n++; return 'rack.js?v=' . ((int)$mm[1] + 1); }, $s);
    if ($n > 0 && file_put_contents($f->getPathname(), $r2, LOCK_EX) === strlen($r2)) { $bumped += $n; }
}
echo 'rack.js version bumped in ' . $bumped . ' reference(s)' . $NL;
@unlink(__FILE__);
echo 'self-deleted. Live check ?cb=<rand>: tilde shows during + after count-up; count refreshes from bh-djc-count.php (6h cache).' . $NL;
