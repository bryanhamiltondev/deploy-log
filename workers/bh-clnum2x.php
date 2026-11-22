<?php
// bh-clnum2x.php - CL medal statics sync: bronze 44, silver 9 (live truth from bh-cl-live.php).
// The bh-clive1 runtime block recounts on every page load, but the static fallbacks
// (count-up data-to values, bronze tooltip, .cl-medals aria-label) drift invisibly.
// x-variant: punctuation-free token (bhClnum2x9) after the bh-Clnum2! gate kept
// rejecting on the host - upload/copy mangling suspected; alphanumeric removes the variable.
if (!isset($_GET['t']) || !hash_equals('bhClnum2x9', (string)$_GET['t'])) { http_response_code(404); if (is_file(__DIR__ . '/404.php')) { include __DIR__ . '/404.php'; } else { echo '404'; } exit; }
header('Content-Type: text/plain; charset=utf-8');
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: bh-clnum2 2026-11-22' . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (fixtures only)' : 'DRY (nothing written)')) . $NL;

function patchStatics($s, &$report) {
    $report = '';
    $ok = true;
    // 1. bronze tooltip
    $s2 = preg_replace('/title="43 bronze medals"/', 'title="44 bronze medals"', $s, 1, $c1);
    if ($c1 === 1) { $report .= 'tooltip:1 '; } elseif ($c1 > 1) { $report .= 'tooltip:MULTI '; $ok = false; } else { $report .= 'tooltip:0 '; }
    // 2. bronze count-up
    $old = 'cl-num-b" data-to="43">43<'; $new = 'cl-num-b" data-to="44">44<'; $c2 = substr_count($s2, $old); if ($c2 === 1) { $s2 = str_replace($old, $new, $s2); }
    if ($c2 === 1) { $report .= 'bronze:1 '; } elseif ($c2 > 1) { $report .= 'bronze:MULTI '; $ok = false; } else { $report .= 'bronze:0 '; }
    // 3. silver count-up (drifted to 9 on the live profile)
    $old = 'cl-num-s" data-to="8">8<'; $new = 'cl-num-s" data-to="9">9<'; $c3 = substr_count($s2, $old); if ($c3 === 1) { $s2 = str_replace($old, $new, $s2); }
    if ($c3 === 1) { $report .= 'silver:1 '; } elseif ($c3 > 1) { $report .= 'silver:MULTI '; $ok = false; } else { $report .= 'silver:0 '; }
    // 4. aria-label (any stale values -> live truth)
    $s2 = preg_replace('/aria-label="Coder Legion medals: [^"]*"/', 'aria-label="Coder Legion medals: 1 gold, 9 silver, 44 bronze"', $s2, 1, $c4);
    if ($c4 === 1) { $report .= 'aria:1 '; } elseif ($c4 > 1) { $report .= 'aria:MULTI '; $ok = false; } else { $report .= 'aria:0 '; }
    if ($s2 === $s) { return false; }
    return $ok ? $s2 : false;
}

if ($SELFTEST) {
    $fail = 0;
    $fix = '<span class="cl-medal" title="43 bronze medals"><span class="cl-dot cl-bronze" aria-hidden="true"></span><span class="bh-cnt cl-num-b" data-to="43">43</span></span><span class="bh-cnt cl-num-s" data-to="8">8</span><span class="cl-medals" aria-label="Coder Legion medals: 1 gold, 6 silver, 39 bronze">';
    $rep = ''; $r = patchStatics($fix, $rep);
    if (is_string($r) && strpos($r, 'title="44 bronze medals"') !== false && strpos($r, 'cl-num-b" data-to="44">44<') !== false && strpos($r, 'cl-num-s" data-to="9">9<') !== false && strpos($r, '1 gold, 9 silver, 44 bronze') !== false) { echo 'selftest 1 all four statics: PASS (' . $rep . ')' . $NL; } else { $fail++; echo 'selftest 1: FAIL (' . $rep . ')' . $NL; }
    // already-correct file must be untouched
    $good = 'data-to="44">44< title="44 bronze medals" data-to="9">9< aria-label="Coder Legion medals: 1 gold, 9 silver, 44 bronze"';
    $rep = ''; $r2 = patchStatics($good, $rep);
    if ($r2 === false) { echo 'selftest 2 already-correct untouched: PASS' . $NL; } else { $fail++; echo 'selftest 2: FAIL' . $NL; }
    echo 'selftest: ' . ($fail === 0 ? 'ALL PASS' : $fail . ' FAILURE(S)') . $NL;
    exit;
}

// recursive scan: patch every .html file that carries the medal statics
$files = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if ($f->isFile() && strtolower($f->getExtension()) === 'html') { $files[] = $f->getPathname(); }
}
sort($files);
echo 'html files scanned: ' . count($files) . $NL;
$hit = 0; $done = 0;
foreach ($files as $f) {
    if (strpos($f, '.bak') !== false) { continue; }
    $s = @file_get_contents($f);
    if ($s === false || strpos($s, 'cl-num-b') === false) { continue; }
    $hit++;
    $rep = ''; $r = patchStatics($s, $rep);
    if ($r === false) { echo basename($f) . ': nothing to change (' . $rep . 'or guards failed)' . $NL; continue; }
    if (!$GO) { echo basename($f) . ': would patch (' . $rep . ')' . $NL; continue; }
    copy($f, $f . '.bak-clnum2.bak');
    if (file_put_contents($f, $r, LOCK_EX) !== strlen($r)) { copy($f . '.bak-clnum2.bak', $f); echo basename($f) . ': WRITE SHORT; restored' . $NL; continue; }
    if (file_get_contents($f) !== $r) { copy($f . '.bak-clnum2.bak', $f); echo basename($f) . ': VERIFY FAIL; restored' . $NL; continue; }
    $done++;
    echo basename($f) . ': patched (' . $rep . ')' . $NL;
}
echo 'files with medal statics: ' . $hit . ($GO ? ', patched: ' . $done : ' (dry - no writes)') . $NL;
if (!$GO) { echo 'DRY OK. &go=go to write.' . $NL; exit; }
@unlink(__FILE__);
echo 'self-deleted. Live check: https://bryanhamilton.info/?cb=rand - strip should paint 1/9/44 before the recount lands (same numbers after).' . $NL;
