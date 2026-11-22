<?php
// bh-clnum1.php - sync homepage CL medal STATIC fallback to live 1/8/43.
// Runtime recount (bh-clive1 + bh-cl-live.php) already returns 1/8/43; this
// fixes the no-JS/crawler fallback: silver 6->8, bronze 39->43, bronze tooltip.
// DRY by default; &go=go writes; &selftest=1 fixture tests (read-only).
// Backup: index.html.bak-clnum1. Self-deletes after GO. Token: bh-Clnum1!
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
define('BUILD', 'bh-clnum1 v1 2026-11-22');
define('TOKEN', 'bh-Clnum1!');
if (!isset($_GET['t']) || !hash_equals(TOKEN, (string)$_GET['t'])) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: ' . BUILD . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (read-only fixtures)' : 'DRY (nothing written)')) . $NL;

$PATCH = array(
    'silver count' => array('cl-num-s" data-to="6">6<', 'cl-num-s" data-to="8">8<'),
    'bronze count' => array('cl-num-b" data-to="39">39<', 'cl-num-b" data-to="43">43<'),
    'bronze tooltip' => array('title="39 bronze medals"', 'title="43 bronze medals"'),
);

function clnum_apply($s, $patch, &$msg) {
    $out = $s;
    $done = 0;
    foreach ($patch as $name => $p) {
        $n = substr_count($out, $p[0]);
        if ($n === 0) { $msg = 'ABORT: ' . $name . ' needle not found'; return false; }
        if ($n > 1) { $msg = 'ABORT: ' . $name . ' needle count ' . $n . ', want 1'; return false; }
        $out = str_replace($p[0], $p[1], $out);
        $done++;
    }
    if (substr_count($out, 'data-to="8"') !== 1 || substr_count($out, 'data-to="43"') !== 1 || strpos($out, '43 bronze medals') === false) { $msg = 'ABORT: post-check failed'; return false; }
    $msg = 'patch ok: ' . $done . ' of ' . count($patch) . ' edits (delta ' . (strlen($out) - strlen($s)) . ' bytes)';
    return $out;
}

if ($SELFTEST) {
    $fail = 0;
    $fx = '<span class="cl-medal" title="Devoted Reader (gold)"><span class="cl-dot cl-gold" aria-hidden="true"></span><span class="bh-cnt cl-num-g" data-to="1">1</span></span><span class="cl-medal" title="Avid Reader, 1,3 Club, Good Post (silver)"><span class="cl-dot cl-silver" aria-hidden="true"></span><span class="bh-cnt cl-num-s" data-to="6">6</span></span><span class="cl-medal" title="39 bronze medals"><span class="cl-dot cl-bronze" aria-hidden="true"></span><span class="bh-cnt cl-num-b" data-to="39">39</span></span>';
    $m = ''; $r = clnum_apply($fx, $PATCH, $m);
    if ($r !== false && strpos($r, 'data-to="8">8<') !== false && strpos($r, 'data-to="43">43<') !== false && strpos($r, '43 bronze medals') !== false && strpos($r, 'data-to="6"') === false && strpos($r, '39 bronze') === false) {
        echo 'selftest 1 all-three patch: PASS' . $NL;
    } else { $fail++; echo 'selftest 1 all-three patch: FAIL (' . $m . ')' . $NL; }
    $m = ''; $r2 = clnum_apply(str_replace('title="39 bronze medals"', '', $fx), $PATCH, $m);
    if ($r2 === false && strpos($m, 'tooltip') !== false) { echo 'selftest 2 missing-tooltip abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 2 missing-tooltip abort: FAIL' . $NL; }
    $m = ''; $r3 = clnum_apply($fx . '<span title="39 bronze medals">', $PATCH, $m);
    if ($r3 === false && strpos($m, 'count 2') !== false) { echo 'selftest 3 double-tooltip abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 3 double-tooltip abort: FAIL' . $NL; }
    $m = ''; $r4 = clnum_apply(str_replace('data-to="39">39<', 'data-to="38">38<', $fx), $PATCH, $m);
    if ($r4 === false && strpos($m, 'bronze count') !== false) { echo 'selftest 4 wrong-bronze-value abort: PASS' . $NL; }
    else { $fail++; echo 'selftest 4 wrong-bronze-value abort: FAIL' . $NL; }
    echo 'selftest: build ' . BUILD . ' - ' . ($fail === 0 ? 'ALL PASS' : $fail . ' FAILURE(S)') . $NL;
    exit;
}

$FILE = __DIR__ . '/index.html';
$src = @file_get_contents($FILE);
if ($src === false) { echo 'ABORT: cannot read index.html' . $NL; exit; }
echo 'index.html bytes: ' . strlen($src) . $NL;
$msg = '';
$out = clnum_apply($src, $PATCH, $msg);
if ($out === false) { echo $msg . $NL; exit; }
echo $msg . $NL;
if (!$GO) { echo 'DRY OK. &go=go to write.' . $NL; exit; }
$bak = $FILE . '.bak-clnum1';
if (!copy($FILE, $bak)) { echo 'ABORT: backup failed' . $NL; exit; }
echo 'backup: index.html.bak-clnum1' . $NL;
if (file_put_contents($FILE, $out, LOCK_EX) !== strlen($out)) { copy($bak, $FILE); echo 'WRITE SHORT; restored' . $NL; exit; }
clearstatcache();
if (file_get_contents($FILE) !== $out) { copy($bak, $FILE); echo 'VERIFY FAILED; restored' . $NL; exit; }
echo 'wrote index.html, verified byte-exact' . $NL;
@unlink(__FILE__);
echo 'self-deleted. Live check with ?cb=<rand>; static fallback now 1/8/43.' . $NL;
