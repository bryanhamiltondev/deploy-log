<?php
// bh-djtrk2.php - homepage DJ Calendar blurb: "99 DJs tracked".
// v2: fixes selftest 4 (fixture contained the FULL needle + junk, so patching
// was correct; the test expectation was wrong). Test 4 is now a truncated
// needle; test 5 asserts full-needle-plus-junk patches exactly once.
// DRY by default; &go=go writes; &selftest=1 runs fixture self-tests (read-only).
// Backup: index.html.bak-djtrk2. Self-deletes after GO. Token: bh-DjTrk2!
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
define('BUILD', 'bh-djtrk2 v2 2026-11-21');
define('TOKEN', 'bh-DjTrk2!');
if (!isset($_GET['t']) || !hash_equals(TOKEN, (string)$_GET['t'])) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: ' . BUILD . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (read-only fixtures)' : 'DRY (nothing written)')) . $NL;

$NEEDLE = 'tour-date engine tracking <span class="bh-cnt" data-to="99">99</span> DJs across';
$REPL   = 'tour-date engine, <span class="bh-cnt" data-to="99">99</span> DJs tracked across';

function djtrk_apply($s, $needle, $repl, &$msg) {
    $n = substr_count($s, $needle);
    if ($n === 0) { $msg = 'ABORT: needle not found'; return false; }
    if ($n > 1) { $msg = 'ABORT: needle count ' . $n . ', want 1'; return false; }
    $out = str_replace($needle, $repl, $s);
    if (substr_count($out, 'DJs tracked across') !== 1) { $msg = 'ABORT: post-check failed'; return false; }
    $msg = 'patch ok (delta ' . (strlen($out) - strlen($s)) . ' bytes)';
    return $out;
}

if ($SELFTEST) {
    $fail = 0;
    $fx = '<p>a live tour-date engine tracking <span class="bh-cnt" data-to="99">99</span> DJs across Bandsintown + Ticketmaster.</p>';
    $m = ''; $r = djtrk_apply($fx, $NEEDLE, $REPL, $m);
    if ($r !== false && strpos($r, $REPL) !== false && strpos($r, 'tracking <span') === false) {
        echo 'selftest 1 patch-path: PASS' . $NL;
    } else { $fail++; echo 'selftest 1 patch-path: FAIL (' . $m . ')' . $NL; }
    $m = ''; $r2 = djtrk_apply($fx . $fx, $NEEDLE, $REPL, $m);
    if ($r2 === false) { echo 'selftest 2 double-needle abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 2 double-needle abort: FAIL' . $NL; }
    $m = ''; $r3 = djtrk_apply('<p>no needle here</p>', $NEEDLE, $REPL, $m);
    if ($r3 === false) { echo 'selftest 3 missing-needle abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 3 missing-needle abort: FAIL' . $NL; }
    $m = ''; $r4 = djtrk_apply('tour-date engine tracking <span class="bh-cnt" data-to="99">99</span> DJs', $NEEDLE, $REPL, $m);
    if ($r4 === false) { echo 'selftest 4 truncated-needle abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 4 truncated-needle abort: FAIL' . $NL; }
    $m = ''; $r5 = djtrk_apply($NEEDLE . ' twice? no', $NEEDLE, $REPL, $m);
    if ($r5 !== false && substr_count($r5, 'DJs tracked across') === 1) { echo 'selftest 5 full-needle-plus-junk patches once: PASS' . $NL; }
    else { $fail++; echo 'selftest 5 full-needle-plus-junk patches once: FAIL (' . $m . ')' . $NL; }
    echo 'selftest: build ' . BUILD . ' - ' . ($fail === 0 ? 'ALL PASS' : $fail . ' FAILURE(S)') . $NL;
    exit;
}

$FILE = __DIR__ . '/index.html';
$src = @file_get_contents($FILE);
if ($src === false) { echo 'ABORT: cannot read index.html' . $NL; exit; }
echo 'index.html bytes: ' . strlen($src) . $NL;
$msg = '';
$out = djtrk_apply($src, $NEEDLE, $REPL, $msg);
if ($out === false) { echo $msg . $NL; exit; }
echo $msg . $NL;
echo 'old: ' . $NEEDLE . $NL;
echo 'new: ' . $REPL . $NL;
if (!$GO) { echo 'DRY OK. &go=go to write.' . $NL; exit; }
$bak = $FILE . '.bak-djtrk2';
if (!copy($FILE, $bak)) { echo 'ABORT: backup failed' . $NL; exit; }
echo 'backup: index.html.bak-djtrk2' . $NL;
if (file_put_contents($FILE, $out, LOCK_EX) !== strlen($out)) { copy($bak, $FILE); echo 'WRITE SHORT; restored' . $NL; exit; }
clearstatcache();
if (file_get_contents($FILE) !== $out) { copy($bak, $FILE); echo 'VERIFY FAILED; restored' . $NL; exit; }
echo 'wrote index.html, verified byte-exact' . $NL;
@unlink(__FILE__);
echo 'self-deleted. Re-request homepage with ?cb=<rand> and confirm: engine, 99 DJs tracked across.' . $NL;
