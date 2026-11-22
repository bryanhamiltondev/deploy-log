<?php
// bh-pages1090c.php - DJC highlight card: tilde survives count-up + age-proof count.
// State this patch assumes (verified live 2026-11-22): index.html span already
// reads data-to="1090" ... >~1,090< (static v1 patch ran); rack.js untouched.
// 1) TILDE: rack.js count-up wrote plain toFixed digits (no prefix, no grouping).
//    run()/step() now honor data-prefix + data-grp: card shows ~1,090 during AND
//    after the animation. Spans without data-prefix/data-grp are byte-identical.
// 2) AGE-PROOF: appends bh-djclive1 block in rack.js fetching /bh-djc-count.php
//    (same-origin proxy written by this worker; 6h disk cache; counts DJC
//    sitemap-artists+cities+venues children; FAQ excluded by design). Rewrites
//    data-to + text when the count moves. Static ~1,090 stays the fallback.
// DRY default; &go=go writes; &selftest=1 fixtures. Backups *.bak-p1090c.
// Self-deletes after GO. Token: bh-PgC!
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
define('BUILD', 'bh-pages1090c v1 2026-11-22');
define('TOKEN', 'bh-PgC!');
if (!isset($_GET['t']) || !hash_equals(TOKEN, (string)$_GET['t'])) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: ' . BUILD . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (read-only fixtures)' : 'DRY (nothing written)')) . $NL;

// ---- Patch 1: index.html highlight span (current live state) ----
$N1 = '<span class="bh-cnt" data-to="1090" data-grp="1">~1,090</span>+ pages, live since 2015';
$R1 = '<span class="bh-cnt" data-to="1090" data-prefix="~" data-live="djc-pages" data-grp="1">~1,090</span>+ pages, live since 2015';

// ---- Patch 2: rack.js count-up prefix + grouped finals ----
$N2 = 'function run(el){';
$R2 = 'function fmtc(n,g){n=Math.round(n);return g?(n>=1000?String(Math.floor(n/1000))+","+String(1000+n%1000).slice(1):String(n)):String(n);}'
    . 'function run(el){var pre=el.getAttribute("data-prefix")||"";var g=el.getAttribute("data-grp")==="1";';
$N3 = 'if(reduced||isNaN(to)){el.textContent=to.toFixed(dec);return;}';
$R3 = 'if(reduced||isNaN(to)){el.textContent=(pre||g)?pre+fmtc(to,g?1:0):to.toFixed(dec);return;}';
$N4 = 'function step(ts){if(!t0)t0=ts;var p=Math.min(1,(ts-t0)/dur);p=1-Math.pow(1-p,3);el.textContent=(from+(to-from)*p).toFixed(dec);if(p<1)requestAnimationFrame(step);}';
$R4 = 'function step(ts){if(!t0)t0=ts;var p=Math.min(1,(ts-t0)/dur);p=1-Math.pow(1-p,3);var cur=from+(to-from)*p;el.textContent=(p>=1&&pre)?pre+fmtc(to,g?1:0):((pre||g)?pre+fmtc(cur,g?1:0):cur.toFixed(dec));if(p<1)requestAnimationFrame(step);}';

// ---- Patch 3: rack.js live-count block (nowdoc: no quoting traps) ----
$N5 = "}catch(e){}\n\n/* 2. case-studies";
$R5 = <<<'JSEOF'
}catch(e){}

/* 2b. DJC live page count (bh-pages1090c) */
try{
 var djp=document.querySelectorAll('[data-live="djc-pages"]');
 if(djp.length){
  fetch('/bh-djc-count.php',{cache:'no-store'}).then(function(r){return r.ok?r.json():null;}).then(function(d){
   if(!d||!d.total)return;
   djp.forEach(function(el){el.setAttribute('data-to',String(d.total));el.textContent='~'+Number(d.total).toLocaleString('en-US');});
  }).catch(function(){});
 }
}catch(e){}

/* 2. case-studies
JSEOF;

// ---- New file: bh-djc-count.php (nowdoc) ----
$PROXY = <<<'PHPEOF'
<?php
// bh-djc-count.php - live thedjcalendar.com page count for the BH homepage card.
// Counts sitemap-artists + sitemap-cities + sitemap-venues children.
// FAQ long tail deliberately excluded. 6h disk cache, stale fallback.
$CACHE = __DIR__ . '/.djc-count-cache.json';
$TTL = 21600;
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (is_file($CACHE) && (time() - filemtime($CACHE)) < $TTL) { echo file_get_contents($CACHE); exit; }
$total = 0;
foreach (array('sitemap-artists.xml', 'sitemap-cities.xml', 'sitemap-venues.xml') as $s) {
    $x = @file_get_contents('https://thedjcalendar.com/' . $s);
    if ($x === false) { continue; }
    $total += preg_match_all('#<loc>#', $x);
}
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

function apply_all($html, $js, $P, &$msg) {
    if (!cnt1($html, $P['N1'], 'index needle', $msg)) return false;
    $html2 = str_replace($P['N1'], $P['R1'], $html);
    if (substr_count($html2, 'data-prefix="~"') !== 1 || substr_count($html2, 'data-live="djc-pages"') !== 1) { $msg = 'ABORT: html post-check'; return false; }
    foreach (array(array($P['N2'],$P['R2'],'rack run('), array($P['N3'],$P['R3'],'rack reduced'), array($P['N4'],$P['R4'],'rack step'), array($P['N5'],$P['R5'],'rack append')) as $p) {
        if (!cnt1($js, $p[0], $p[2], $msg)) return false;
    }
    $js2 = str_replace($P['N2'], $P['R2'], $js);
    $js2 = str_replace($P['N3'], $P['R3'], $js2);
    $js2 = str_replace($P['N4'], $P['R4'], $js2);
    $js2 = str_replace($P['N5'], $P['R5'], $js2);
    if (substr_count($js2, 'fmtc(') < 3 || substr_count($js2, 'data-live="djc-pages"') !== 1 || substr_count($js2, 'bh-djc-count.php') !== 1) { $msg = 'ABORT: rack post-check'; return false; }
    $msg = 'patch ok (html delta ' . (strlen($html2) - strlen($html)) . ', rack delta ' . (strlen($js2) - strlen($js)) . ')';
    return array($html2, $js2);
}

if ($SELFTEST) {
    $fail = 0;
    $fh = '<span class="highlight-value"><span class="bh-cnt" data-to="1090" data-grp="1">~1,090</span>+ pages, live since 2015</span>';
    $fj = "/* 1. count-up */try{\n function run(el){var from=parseFloat(el.getAttribute(\x27data-from\x27)||\x270\x27),to=parseFloat(el.getAttribute(\x27data-to\x27)),dec=parseInt(el.getAttribute(\x27data-dec\x27)||\x270\x27,10);\n var t0=null,dur=1400;\n if(reduced||isNaN(to)){el.textContent=to.toFixed(dec);return;}\n function step(ts){if(!t0)t0=ts;var p=Math.min(1,(ts-t0)/dur);p=1-Math.pow(1-p,3);el.textContent=(from+(to-from)*p).toFixed(dec);if(p<1)requestAnimationFrame(step);}\n }\n}catch(e){}\n\n/* 2. case-studies */";
    $P = array('N1'=>$N1,'R1'=>$R1,'N2'=>$N2,'R2'=>$R2,'N3'=>$N3,'R3'=>$R3,'N4'=>$N4,'R4'=>$R4,'N5'=>$N5,'R5'=>$R5);
    $m = ''; $r = apply_all($fh, $fj, $P, $m);
    if (is_array($r)
        && strpos($r[0], 'data-to="1090" data-prefix="~" data-live="djc-pages"') !== false
        && strpos($r[1], "getAttribute(\"data-prefix\")") !== false
        && strpos($r[1], "pre+fmtc(to,g?1:0)") !== false
        && strpos($r[1], '/bh-djc-count.php') !== false
        && strpos($r[1], "2b. DJC live page count") !== false) {
        echo 'selftest 1 both-files patch: PASS' . $NL;
    } else { $fail++; echo 'selftest 1 both-files patch: FAIL (' . $m . ')' . $NL; }
    $m = ''; $r2 = apply_all($fh . $fh, $fj, $P, $m);
    if ($r2 === false && strpos($m, 'count 2') !== false) { echo 'selftest 2 double-needle abort: PASS' . $NL; } else { $fail++; echo 'selftest 2 double-needle abort: FAIL (' . $m . ')' . $NL; }
    $m = ''; $r3 = apply_all(str_replace('data-to="1090"', 'data-to="999"', $fh), $fj, $P, $m);
    if ($r3 === false && strpos($m, 'index needle') !== false) { echo 'selftest 3 missing-html-needle abort: PASS' . $NL; } else { $fail++; echo 'selftest 3 missing-html-needle abort: FAIL' . $NL; }
    $m = ''; $r4 = apply_all($fh, str_replace('function step(ts){', 'function stepx(ts){', $fj), $P, $m);
    if ($r4 === false && strpos($m, 'rack step') !== false) { echo 'selftest 4 missing-rack-needle abort: PASS' . $NL; } else { $fail++; echo 'selftest 4 missing-rack-needle abort: FAIL' . $NL; }
    // fmtc math sanity mirrored in PHP: 1090 -> "1,090"; 999 -> "999"; 1005 -> "1,005"
    $fmtcok = (fn($n) => $n >= 1000 ? intdiv($n, 1000) . ',' . substr(strval(1000 + $n % 1000), 1) : strval($n));
    if ($fmtcok(1090) === '1,090' && $fmtcok(999) === '999' && $fmtcok(1005) === '1,005') { echo 'selftest 5 fmtc math mirror: PASS' . $NL; } else { $fail++; echo 'selftest 5 fmtc math mirror: FAIL' . $NL; }
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
$bh = $HTML . '.bak-p1090c'; $bj = $JS . '.bak-p1090c';
if (!copy($HTML, $bh) || !copy($JS, $bj)) { echo 'ABORT: backup failed' . $NL; exit; }
echo 'backups: index.html.bak-p1090c, assets/js/rack.js.bak-p1090c' . $NL;
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
    if (strpos($rel, 'assets/') === 0 || strpos($rel, '.bak') !== false || $rel === 'bh-djc-count.php') continue;
    $ext = strtolower($f->getExtension());
    if ($ext !== 'html' && $ext !== 'php') continue;
    if ($f->getSize() > 3000000) continue;
    $s = @file_get_contents($f->getPathname());
    if ($s === false || strpos($s, 'rack.js?v=') === false) continue;
    $n = 0;
    $r2 = preg_replace_callback('/rack\.js\?v=(\d+)/', function ($mm) use (&$n) { $n++; return 'rack.js?v=' . ((int)$mm[1] + 1); }, $s);
    if ($n > 0 && file_put_contents($f->getPathname(), $r2, LOCK_EX) === strlen($r2)) { $bumped += $n; }
}
echo 'rack.js version bumped in ' . $bumped . ' reference(s)' . $NL;
@unlink(__FILE__);
echo 'self-deleted. Live check ?cb=<rand>: tilde shows during + after count-up; count refreshes from bh-djc-count.php (6h cache).' . $NL;
