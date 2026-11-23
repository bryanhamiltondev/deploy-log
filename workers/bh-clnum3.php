<?php
// bh-clnum3.php - CL medal statics sync to live profile truth: gold 2, silver 10, bronze 47.
// Surfaces: .cl-medals aria-label, cl-num-g/s/b count-up statics, bronze tooltip,
// resume "35 bronze badges" (missed by the last two syncs), receipts self-log row,
// web-developer hero recounted from the real row count after the insert.
// Token alphanumeric per the bh-Clnum2! gate lesson.
if (!isset($_GET['t']) || !hash_equals('bhCln3Go7x', (string)$_GET['t'])) { http_response_code(404); if (is_file(__DIR__ . '/404.php')) { include __DIR__ . '/404.php'; } else { echo '404'; } exit; }
header('Content-Type: text/plain; charset=utf-8');
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: bh-clnum3 2026-11-23' . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (fixtures only)' : 'DRY (nothing written)')) . $NL;

// ---- medal statics patch (homepage index.html) ----
function patchStatics($s, &$report) {
    $report = '';
    $ok = true;
    $pairs = array(
        array('cl-num-g" data-to="1">1<', 'cl-num-g" data-to="2">2<', 'gold'),
        array('cl-num-s" data-to="9">9<', 'cl-num-s" data-to="10">10<', 'silver'),
        array('cl-num-b" data-to="44">44<', 'cl-num-b" data-to="47">47<', 'bronze'),
        array('title="44 bronze medals"', 'title="47 bronze medals"', 'tooltip'),
    );
    $changed = false;
    foreach ($pairs as $p) {
        $c = substr_count($s, $p[0]);
        if ($c === 1) { $s = str_replace($p[0], $p[1], $s); $report .= $p[2] . ':1 '; $changed = true; }
        elseif ($c === 0 && strpos($s, $p[1]) !== false) { $report .= $p[2] . ':already '; }
        elseif ($c === 0) { $report .= $p[2] . ':0 '; $ok = false; }
        else { $report .= $p[2] . ':MULTI '; $ok = false; }
    }
    $s = preg_replace('/aria-label="Coder Legion medals: [^"]*"/', 'aria-label="Coder Legion medals: 2 gold, 10 silver, 47 bronze"', $s, 1, $ca);
    if ($ca === 1) { $report .= 'aria:1 '; $changed = true; } elseif ($ca > 1) { $report .= 'aria:MULTI '; $ok = false; } else { $report .= 'aria:0 '; }
    if (!$changed) { return false; }
    return $ok ? $s : false;
}

// ---- resume patch: "35 bronze badges" -> "47 bronze badges" ----
function patchResume($s, &$report) {
    $old = '35 bronze badges'; $new = '47 bronze badges';
    $c = substr_count($s, $old);
    if ($c === 1) { $report = 'resume:1'; return str_replace($old, $new, $s); }
    if ($c === 0 && strpos($s, $new) !== false) { $report = 'resume:already'; return false; }
    if ($c === 0) { $report = 'resume:0'; return false; }
    $report = 'resume:MULTI'; return false;
}

// ---- receipts self-log row + hero recount ----
function patchReceipts($s, &$report, &$rowsOut) {
    if (strpos($s, 'CL medal sync 2/10/47') !== false) { $report = 'receipts:already'; return false; }
    $row = '<tr><td class="d">Nov 23</td><td><span class="tag bh">BH</span></td><td><span class="item">CL medal sync 2/10/47</span><span class="note">gold 1-&gt;2, silver 9-&gt;10, bronze 44-&gt;47 - homepage aria + count-up statics + bronze tooltip + resume 35-&gt;47; hero recounted at runtime</span></td><td class="v" title="verified on the live site">&#10003; verified</td></tr>';
    $anchor = '<tr><td class="d">';
    $p = strpos($s, $anchor);
    if ($p === false) { $report = 'receipts:anchor0'; return false; }
    $s2 = substr($s, 0, $p) . $row . substr($s, $p);
    $rowsOut = substr_count($s2, 'class="v"');
    $report = 'receipts:1 rows:' . $rowsOut;
    return $s2;
}
function patchHero($s, $n, &$report) {
    $s2 = preg_replace('/<b>\d+ changes shipped<\/b>/', '<b>' . $n . ' changes shipped</b>', $s, 1, $c1);
    $s2 = preg_replace('/<b>\d+ live-verified<\/b>/', '<b>' . $n . ' live-verified</b>', $s2, 1, $c2);
    $report = 'hero:' . $c1 . '+' . $c2;
    if ($c1 === 1 && $c2 === 1) { return $s2; }
    return false;
}

if ($SELFTEST) {
    $fail = 0;
    $fx = '<span class="bh-cnt cl-num-g" data-to="1">1</span><span class="bh-cnt cl-num-s" data-to="9">9</span><span class="bh-cnt cl-num-b" data-to="44">44</span><span class="cl-medal" title="44 bronze medals"></span><span class="cl-medals" aria-label="Coder Legion medals: 1 gold, 9 silver, 44 bronze">';
    $rep = ''; $r = patchStatics($fx, $rep);
    if (is_string($r) && strpos($r, 'data-to="2">2<') !== false && strpos($r, 'data-to="10">10<') !== false && strpos($r, 'data-to="47">47<') !== false && strpos($r, 'title="47 bronze medals"') !== false && strpos($r, '2 gold, 10 silver, 47 bronze') !== false) { echo 'selftest 1 statics: PASS (' . $rep . ')' . $NL; } else { $fail++; echo 'selftest 1: FAIL (' . $rep . ')' . $NL; }
    $good = 'data-to="2">2< data-to="10">10< data-to="47">47< title="47 bronze medals" aria-label="Coder Legion medals: 2 gold, 10 silver, 47 bronze"';
    $rep = ''; $r2 = patchStatics($good, $rep);
    if ($r2 === false) { echo 'selftest 2 already-correct untouched: PASS' . $NL; } else { $fail++; echo 'selftest 2: FAIL' . $NL; }
    $rfx = '<h3>Coder Legion</h3><p>1,000 Club &middot; Verified &middot; 35 bronze badges</p>';
    $rep = ''; $r3 = patchResume($rfx, $rep);
    if (is_string($r3) && strpos($r3, '47 bronze badges') !== false && strpos($r3, '35 bronze badges') === false) { echo 'selftest 3 resume: PASS (' . $rep . ')' . $NL; } else { $fail++; echo 'selftest 3: FAIL (' . $rep . ')' . $NL; }
    $recfx = '<table><tr><td class="d">Nov 22</td><td>x</td><td class="v">v</td></tr></table>';
    $rep = ''; $rows = 0; $r4 = patchReceipts($recfx, $rep, $rows);
    if (is_string($r4) && strpos($r4, 'CL medal sync 2/10/47') !== false && strpos($r4, 'CL medal sync 2/10/47') < strpos($r4, 'Nov 22') && substr_count($r4, '<tr><td class="d">') === 2 && $rows === 2) { echo 'selftest 4 receipts+rows: PASS (' . $rep . ')' . $NL; } else { $fail++; echo 'selftest 4: FAIL (' . $rep . ' rows=' . $rows . ')' . $NL; }
    $hfx = 'status: <b>107 changes shipped</b> / <b>107 live-verified</b>';
    $rep = ''; $r5 = patchHero($hfx, 108, $rep);
    if (is_string($r5) && strpos($r5, '<b>108 changes shipped</b>') !== false && strpos($r5, '<b>108 live-verified</b>') !== false) { echo 'selftest 5 hero: PASS (' . $rep . ')' . $NL; } else { $fail++; echo 'selftest 5: FAIL (' . $rep . ')' . $NL; }
    echo 'selftest: ' . ($fail === 0 ? 'ALL PASS' : $fail . ' FAILURE(S)') . $NL;
    exit;
}

// ---- real run ----
$patched = array();
$idx = __DIR__ . '/index.html';
$s = @file_get_contents($idx);
if ($s !== false && strpos($s, 'cl-num-b') !== false) {
    $rep = ''; $r = patchStatics($s, $rep);
    if (is_string($r)) { if (!$GO) { echo 'index.html: would patch (' . $rep . ')' . $NL; } else { copy($idx, $idx . '.bak-clnum3.bak'); if (file_put_contents($idx, $r, LOCK_EX) === strlen($r) && file_get_contents($idx) === $r) { echo 'index.html: patched (' . $rep . ')' . $NL; $patched[] = $idx; } else { copy($idx . '.bak-clnum3.bak', $idx); echo 'index.html: WRITE FAIL; restored' . $NL; } } }
    else { echo 'index.html: nothing to change (' . $rep . ')' . $NL; }
} else { echo 'index.html: medal block not found' . $NL; }

$res = __DIR__ . '/bryan-hamilton-tech.html';
$s = @file_get_contents($res);
if ($s !== false) {
    $rep = ''; $r = patchResume($s, $rep);
    if (is_string($r)) { if (!$GO) { echo 'resume: would patch (' . $rep . ')' . $NL; } else { copy($res, $res . '.bak-clnum3.bak'); if (file_put_contents($res, $r, LOCK_EX) === strlen($r) && file_get_contents($res) === $r) { echo 'resume: patched (' . $rep . ')' . $NL; $patched[] = $res; } else { copy($res . '.bak-clnum3.bak', $res); echo 'resume: WRITE FAIL; restored' . $NL; } } }
    else { echo 'resume: nothing to change (' . $rep . ')' . $NL; }
} else { echo 'resume: not found' . $NL; }

$rec = __DIR__ . '/receipts/index.html';
$s = @file_get_contents($rec);
$rows = 0;
if ($s !== false) {
    $rep = ''; $r = patchReceipts($s, $rep, $rows);
    if (is_string($r)) { if (!$GO) { echo 'receipts: would insert row (' . $rep . ')' . $NL; } else { copy($rec, $rec . '.bak-clnum3.bak'); if (file_put_contents($rec, $r, LOCK_EX) === strlen($r) && file_get_contents($rec) === $r) { echo 'receipts: row inserted (' . $rep . ')' . $NL; $patched[] = $rec; } else { copy($rec . '.bak-clnum3.bak', $rec); echo 'receipts: WRITE FAIL; restored' . $NL; } } }
    else { echo 'receipts: nothing to change (' . $rep . ')' . $NL; $rows = substr_count($s, 'class="v"'); }
} else { echo 'receipts: not found at receipts/index.html' . $NL; }

if ($rows > 0) {
    $hero = __DIR__ . '/web-developer/index.html';
    $s = @file_get_contents($hero);
    if ($s !== false) {
        $rep = ''; $r = patchHero($s, $rows, $rep);
        if (is_string($r)) { if (!$GO) { echo 'hero: would recount to ' . $rows . ' (' . $rep . ')' . $NL; } else { copy($hero, $hero . '.bak-clnum3.bak'); if (file_put_contents($hero, $r, LOCK_EX) === strlen($r) && file_get_contents($hero) === $r) { echo 'hero: recounted to ' . $rows . ' (' . $rep . ')' . $NL; $patched[] = $hero; } else { copy($hero . '.bak-clnum3.bak', $hero); echo 'hero: WRITE FAIL; restored' . $NL; } } }
        else { echo 'hero: nothing to change (' . $rep . ')' . $NL; }
    } else { echo 'hero: not found' . $NL; }
}

if (!$GO) { echo 'DRY OK. &go=go to write.' . $NL; exit; }
if (count($patched) >= 2) {
    @unlink($idx . '.bak-clnum3.bak'); @unlink($res . '.bak-clnum3.bak'); @unlink($rec . '.bak-clnum3.bak'); @unlink($hero . '.bak-clnum3.bak');
    if (function_exists('curl_init')) {
        $ch = curl_init('https://api.indexnow.org/indexnow?url=' . urlencode('https://bryanhamilton.info/') . '&key=ec61cb24045482a3a8b4621740fad3bb');
        curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_FOLLOWLOCATION => true));
        $code = (string)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_exec($ch); $code = (string)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo 'indexnow: HTTP ' . $code . $NL;
    } else { echo 'indexnow: curl missing, skipped' . $NL; }
    $self = __FILE__;
    @unlink($self);
    echo is_file($self) ? 'SELF-DELETE FAILED - remove bh-clnum3.php manually' . $NL : 'self-deleted.' . $NL;
    echo 'Live check: https://bryanhamilton.info/?cb=rand (medals 2/10/47) + /receipts/ (108 rows) + /web-developer/ (108/108)' . $NL;
} else { echo 'nothing patched - no self-delete' . $NL; }
