<?php
// bh-pages1090.php - highlight card: "1,000+ pages" -> "~1,090+ pages".
// Count-up span: data-to 1000->1090, label "1,000"->"~1,090" (tilde inside
// count-up text = honest rounding; + suffix and "live since 2015" unchanged).
// DRY by default; &go=go writes; &selftest=1 fixture tests (read-only).
// Backup: index.html.bak-p1090. Self-deletes after GO. Token: bh-Pg1090!
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
define('BUILD', 'bh-pages1090 v1 2026-11-22');
define('TOKEN', 'bh-Pg1090!');
if (!isset($_GET['t']) || !hash_equals(TOKEN, (string)$_GET['t'])) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: ' . BUILD . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (read-only fixtures)' : 'DRY (nothing written)')) . $NL;

$NEEDLE = '<span class="bh-cnt" data-to="1000" data-grp="1">1,000</span>+ pages, live since 2015';
$REPL   = '<span class="bh-cnt" data-to="1090" data-grp="1">~1,090</span>+ pages, live since 2015';

function pg_apply($s, $needle, $repl, &$msg) {
    $n = substr_count($s, $needle);
    if ($n === 0) { $msg = 'ABORT: needle not found'; return false; }
    if ($n > 1) { $msg = 'ABORT: needle count ' . $n . ', want 1'; return false; }
    $out = str_replace($needle, $repl, $s);
    if (substr_count($out, 'data-to="1090"') !== 1 || strpos($out, '~1,090</span>+ pages') === false) { $msg = 'ABORT: post-check failed'; return false; }
    $msg = 'patch ok (delta ' . (strlen($out) - strlen($s)) . ' bytes)';
    return $out;
}

if ($SELFTEST) {
    $fail = 0;
    $fx = '<span class="highlight-value"><span class="bh-cnt" data-to="1000" data-grp="1">1,000</span>+ pages, live since 2015</span>';
    $m = ''; $r = pg_apply($fx, $NEEDLE, $REPL, $m);
    if ($r !== false && strpos($r, 'data-to="1090"') !== false && strpos($r, 'data-to="1000"') === false && strpos($r, '~1,090</span>+ pages, live since 2015') !== false) {
        echo 'selftest 1 patch-path: PASS' . $NL;
    } else { $fail++; echo 'selftest 1 patch-path: FAIL (' . $m . ')' . $NL; }
    $m = ''; $r2 = pg_apply($fx . $fx, $NEEDLE, $REPL, $m);
    if ($r2 === false) { echo 'selftest 2 double-needle abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 2 double-needle abort: FAIL' . $NL; }
    $m = ''; $r3 = pg_apply('<span class="highlight-value"><span class="bh-cnt" data-to="1090" data-grp="1">~1,090</span>+ pages, live since 2015</span>', $NEEDLE, $REPL, $m);
    if ($r3 === false) { echo 'selftest 3 already-patched abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 3 already-patched abort: FAIL' . $NL; }
    $m = ''; $r4 = pg_apply('<span class="bh-cnt" data-to="1000" data-grp="1">1,000</span>+ pages, live', $NEEDLE, $REPL, $m);
    if ($r4 === false) { echo 'selftest 4 truncated-needle abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 4 truncated-needle abort: FAIL' . $NL; }
    echo 'selftest: build ' . BUILD . ' - ' . ($fail === 0 ? 'ALL PASS' : $fail . ' FAILURE(S)') . $NL;
    exit;
}

$FILE = __DIR__ . '/index.html';
$src = @file_get_contents($FILE);
if ($src === false) { echo 'ABORT: cannot read index.html' . $NL; exit; }
echo 'index.html bytes: ' . strlen($src) . $NL;
$msg = '';
$out = pg_apply($src, $NEEDLE, $REPL, $msg);
if ($out === false) { echo $msg . $NL; exit; }
echo $msg . $NL;
if (!$GO) { echo 'DRY OK. &go=go to write.' . $NL; exit; }
$bak = $FILE . '.bak-p1090';
if (!copy($FILE, $bak)) { echo 'ABORT: backup failed' . $NL; exit; }
echo 'backup: index.html.bak-p1090' . $NL;
if (file_put_contents($FILE, $out, LOCK_EX) !== strlen($out)) { copy($bak, $FILE); echo 'WRITE SHORT; restored' . $NL; exit; }
clearstatcache();
if (file_get_contents($FILE) !== $out) { copy($bak, $FILE); echo 'VERIFY FAILED; restored' . $NL; exit; }
echo 'wrote index.html, verified byte-exact' . $NL;
@unlink(__FILE__);
echo 'self-deleted. Live check ?cb=<rand>: ~1,090+ pages, count-up to 1090.' . $NL;
