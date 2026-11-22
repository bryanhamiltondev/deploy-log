<?php
// djc-singleplay-v1.php - hover-preview singleton: one track at a time, everywhere.
// BUG: rolling over a ticker artist then leaving quickly still started the track
// (the iTunes fetch resolved AFTER mouseout; the token/audio guard never saw the
// leave) - and the SHP audio + the app-main.js card engine knew nothing about
// each other, so two tracks could overlap.
// FIX (JS-only, both engines):
//  A) search-hover-player.js: stopAudio() now also stops the card engine;
//     the fetch-resolve guard requires the hover to STILL be active;
//     the live audio registers as window.__djcShpAudio; exposes
//     window.djcStopAllHoverAudio().
//  B) app-main.js: playPreview() pauses window.__djcShpAudio before arming a
//     card; stopPreview() exposes window.djcStopCardEngineAudio() for SHP.
// Marker: djc-singleplay-v1. Token: djc-Sp1Pl4y!
if (!isset($_GET['t']) || !hash_equals('djc-Sp1Pl4y!', (string)$_GET['t'])) { http_response_code(404); if (is_file(__DIR__ . '/404.php')) { include __DIR__ . '/404.php'; } else { echo '404'; } exit; }
header('Content-Type: text/plain; charset=utf-8');
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$SELFTEST = (isset($_GET['selftest']) && $_GET['selftest'] === '1');
$NL = PHP_EOL;
echo 'worker: djc-singleplay-v1 2026-11-22' . $NL;
echo 'mode: ' . ($GO ? 'GO (writes enabled)' : ($SELFTEST ? 'SELFTEST (fixtures only)' : 'DRY (nothing written)')) . $NL;

$SHP = __DIR__ . '/search-hover-player.js';
$AM = __DIR__ . '/app-main.js';

$P = array();
// --- SHP patches ---
$P['s1n'] = "    function stopAudio() {\n        if (audio) {";
$P['s1r'] = <<<'J1'
    function stopAudio() {
        /* djc-singleplay-v1: also stop the card-preview engine */
        try { if (typeof window.djcStopCardEngineAudio === 'function') { window.djcStopCardEngineAudio(); } } catch (eEng) {}
        if (audio) {
J1;
$P['s2n'] = "        audio = new Audio(track.previewUrl);";
$P['s2r'] = "        audio = new Audio(track.previewUrl);\n        window.__djcShpAudio = audio; /* djc-singleplay-v1 */";
$P['s3n'] = "            try { audio.pause(); } catch (e) {}\n            audio = null;";
$P['s3r'] = "            try { audio.pause(); } catch (e) {}\n            audio = null;\n            window.__djcShpAudio = null; /* djc-singleplay-v1 */";
$P['s4n'] = "if (token !== fetchToken || audio) { return; }";
$P['s4r'] = "if (token !== fetchToken || audio || !activeItem) { return; } /* djc-singleplay-v1: hover already left - no orphan play */";
$P['s5n'] = "        if (!inPanel && !inSearch) { window.clearTimeout(hoverTimer); hidePanel(); }\n    }, true);\n})();";
$P['s5r'] = <<<'J2'
        if (!inPanel && !inSearch) { window.clearTimeout(hoverTimer); hidePanel(); }
    }, true);

    window.djcStopAllHoverAudio = function () { /* djc-singleplay-v1 */
        window.clearTimeout(hoverTimer);
        hidePanel();
    };
})();
J2;
// --- app-main patches ---
$P['a1n'] = "    window.activeAudioCard = card;\n    card._previewPending = true;";
$P['a1r'] = "    try { if (window.__djcShpAudio) { window.__djcShpAudio.pause(); } window.__djcShpAudio = null; } catch (eSh) {} /* djc-singleplay-v1 */\n    window.activeAudioCard = card;\n    card._previewPending = true;";
$P['a2n'] = "function stopPreview(card) {";
$P['a2r'] = "function stopPreview(card) {\n    window.djcStopCardEngineAudio = function () { try { if (window.activeAudioCard) { stopPreview(window.activeAudioCard); } } catch (eExp) {} }; /* djc-singleplay-v1 */";

function bal($s) { return array(substr_count($s, '{') - substr_count($s, '}'), substr_count($s, '(') - substr_count($s, ')')); }

function applyFile($src, $pairs, &$msg) {
    foreach ($pairs as $p) {
        $n = substr_count($src, $p[0]);
        if ($n !== 1) { $msg = 'ABORT: needle not unique/found (' . $n . 'x): ' . substr($p[0], 0, 50); return false; }
    }
    foreach ($pairs as $p) { $src = str_replace($p[0], $p[1], $src); }
    if (strpos($src, 'djc-singleplay-v1') === false) { $msg = 'ABORT: marker missing post-patch'; return false; }
    return $src;
}

if ($SELFTEST) {
    $fail = 0;
    $fshp = "    var audio = null;\n    function stopAudio() {\n        if (audio) {\n            try { audio.pause(); } catch (e) {}\n            audio = null;\n        }\n    }\n    function playTrack(track) {\n        stopAudio();\n        audio = new Audio(track.previewUrl);\n    }\n        if (token !== fetchToken || audio) { return; }\n        if (!inPanel && !inSearch) { window.clearTimeout(hoverTimer); hidePanel(); }\n    }, true);\n})();";
    $fam = "function stopPreview(card) {\n    if (typeof window.djcStopCardPreview === 'function') {\n        window.djcStopCardPreview(card);\n        return;\n    }\n}\n    window.activeAudioCard = card;\n    card._previewPending = true;";
    $m = ''; $r1 = applyFile($fshp, array(array($P['s1n'],$P['s1r']),array($P['s2n'],$P['s2r']),array($P['s3n'],$P['s3r']),array($P['s4n'],$P['s4r']),array($P['s5n'],$P['s5r'])), $m);
    $m2 = ''; $r2 = applyFile($fam, array(array($P['a1n'],$P['a1r']),array($P['a2n'],$P['a2r'])), $m2);
    if (is_string($r1) && substr_count($r1, 'djc-singleplay-v1') >= 4 && strpos($r1, '!activeItem') !== false && strpos($r1, 'djcStopCardEngineAudio') !== false) { echo 'selftest 1 shp patch: PASS' . $NL; } else { $fail++; echo 'selftest 1 shp patch: FAIL (' . $m . ')' . $NL; }
    if (is_string($r2) && substr_count($r2, 'djc-singleplay-v1') === 2 && strpos($r2, '__djcShpAudio') !== false && strpos($r2, 'djcStopCardEngineAudio') !== false) { echo 'selftest 2 app-main patch: PASS' . $NL; } else { $fail++; echo 'selftest 2 app-main patch: FAIL (' . $m2 . ')' . $NL; }
    // balance deltas on patched fixtures
    $b1s = bal($fshp); $b1d = bal($r1);
    $b2s = bal($fam); $b2d = bal($r2);
    if ($b1s === $b1d && $b2s === $b2d) { echo 'selftest 3 balance delta zero: PASS' . $NL; } else { $fail++; echo 'selftest 3 balance delta zero: FAIL' . $NL; }
    // doubled needle abort
    $m = ''; $r3 = applyFile($fshp . $fshp, array(array($P['s1n'],$P['s1r']),array($P['s2n'],$P['s2r']),array($P['s3n'],$P['s3r']),array($P['s4n'],$P['s4r']),array($P['s5n'],$P['s5r'])), $m);
    if ($r3 === false && strpos($m, 'not unique') !== false) { echo 'selftest 4 doubled-needle abort: PASS' . $NL; } else { $fail++; echo 'selftest 4 doubled-needle abort: FAIL' . $NL; }
    echo 'selftest: ' . ($fail === 0 ? 'ALL PASS' : $fail . ' FAILURE(S)') . $NL;
    exit;
}

$shp = @file_get_contents($SHP);
if ($shp === false) { echo 'ABORT: cannot read search-hover-player.js' . $NL; exit; }
$am = @file_get_contents($AM);
if ($am === false) { echo 'ABORT: cannot read app-main.js' . $NL; exit; }
echo 'search-hover-player.js bytes: ' . strlen($shp) . $NL . 'app-main.js bytes: ' . strlen($am) . $NL;
if (strpos($shp, 'djc-singleplay-v1') !== false || strpos($am, 'djc-singleplay-v1') !== false) { echo 'ABORT: already patched' . $NL; exit; }
$m = ''; $nshp = applyFile($shp, array(array($P['s1n'],$P['s1r']),array($P['s2n'],$P['s2r']),array($P['s3n'],$P['s3r']),array($P['s4n'],$P['s4r']),array($P['s5n'],$P['s5r'])), $m);
if (!is_string($nshp)) { echo $m . $NL; exit; }
$m = ''; $nam = applyFile($am, array(array($P['a1n'],$P['a1r']),array($P['a2n'],$P['a2r'])), $m);
if (!is_string($nam)) { echo $m . $NL; exit; }
$bs1 = bal($shp); $bd1 = bal($nshp); $bs2 = bal($am); $bd2 = bal($nam);
if ($bs1 !== $bd1) { echo 'ABORT: shp balance delta not zero (' . implode(',', $bd1) . ' vs ' . implode(',', $bs1) . ')' . $NL; exit; }
if ($bs2 !== $bd2) { echo 'ABORT: app-main balance delta not zero (' . implode(',', $bd2) . ' vs ' . implode(',', $bs2) . ')' . $NL; exit; }
echo 'patch ok: shp delta ' . (strlen($nshp) - strlen($shp)) . 'B, app-main delta ' . (strlen($nam) - strlen($am)) . 'B, balance delta zero' . $NL;
if (!$GO) { echo 'DRY OK. &go=go to write.' . $NL; exit; }
copy($SHP, $SHP . '.bak-singleplay.bak');
copy($AM, $AM . '.bak-singleplay.bak');
echo 'backups kept: search-hover-player.js.bak-singleplay.bak, app-main.js.bak-singleplay.bak (remove after you confirm the fix)' . $NL;
if (file_put_contents($SHP, $nshp, LOCK_EX) !== strlen($nshp)) { copy($SHP . '.bak-singleplay.bak', $SHP); echo 'SHP WRITE SHORT; restored' . $NL; exit; }
if (file_get_contents($SHP) !== $nshp) { copy($SHP . '.bak-singleplay.bak', $SHP); echo 'SHP VERIFY FAIL; restored' . $NL; exit; }
if (file_put_contents($AM, $nam, LOCK_EX) !== strlen($nam)) { copy($AM . '.bak-singleplay.bak', $AM); echo 'AM WRITE SHORT; restored' . $NL; exit; }
if (file_get_contents($AM) !== $nam) { copy($AM . '.bak-singleplay.bak', $AM); echo 'AM VERIFY FAIL; restored' . $NL; exit; }
$bumped = 0;
$self = realpath(__FILE__);
foreach (glob(__DIR__ . '/*.php') as $f) {
    if (realpath($f) === $self) { continue; }
    $s = @file_get_contents($f);
    if ($s === false) { continue; }
    $n = 0;
    $r2 = preg_replace_callback('/app-main\.js\?v=(\d+)/', function ($mm) use (&$n) { $n++; return 'app-main.js?v=' . ((int)$mm[1] + 1); }, $s);
    $r2 = preg_replace_callback('/search-hover-player\.js\?v=(\d+)/', function ($mm) use (&$n) { $n++; return 'search-hover-player.js?v=' . ((int)$mm[1] + 1); }, $r2);
    if ($n > 0 && file_put_contents($f, $r2, LOCK_EX) === strlen($r2)) { $bumped += $n; }
}
echo 'asset versions bumped in ' . $bumped . ' reference(s)' . $NL;
@unlink(__FILE__);
echo 'self-deleted. Live checks (cache-bust): homepage ?cb=rand; hover a ticker artist, leave fast - no orphan track; hover two artists back to back - only one plays.' . $NL;
