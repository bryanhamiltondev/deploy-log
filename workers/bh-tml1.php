<?php
// bh-tml1 - Tomorrowland article: complete the missing discovery surfaces
// (hub card + hub blogPost JSON-LD + hub count, homepage card with 10-cap held,
// og-manifest entry, receipts row + chip, hero recount, IndexNow)
// Modes: ?t=TOKEN              -> dry (reads live files, no writes)
//        ?t=TOKEN&selftest=1   -> fixture self-test (embedded mini-fixtures)
//        ?t=TOKEN&go=go        -> GO (backup, patch, verify, rollback-on-fail, self-delete)
$TOKEN = 'bhTml1Srf9';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); if (is_file('404.php')) { include '404.php'; } else { echo '404'; } exit; }
header('Content-Type: text/plain');
$SELFTEST = isset($_GET['selftest']);
$GO = isset($_GET['go']) && $_GET['go'] === 'go';

$T = 'Tomorrowland Is Eyeing Colorado. Here\'s What\'s Confirmed (and What Isn\'t)';
$SLUG = 'tomorrowland-winter-colorado';
$AURL = 'https://bryanhamilton.info/writing/' . $SLUG . '/';

// ---------- payloads ----------
$NEWCARD = '<!-- bh-tml1 --><a class="card" href="' . $AURL . '" style="text-decoration:none;color:inherit;display:flex;flex-direction:column">'
	. '<p class="art-meta">Nov 21, 2026 &middot; 2 min read</p><h3>' . $T . '</h3>'
	. '<p style="color:#8b949e;font-size:16px;margin:6px 0 12px">The Denver Post reported it. Tomorrowland confirmed talks are underway. Colorado is pitching $500,000. What the reporting actually supports, what is still rumor, and what a winter edition would do to the North American calendar.</p>'
	. '<span class="btn"><span>Read the build</span><span class="btn-arrow" aria-hidden="true">&rarr;</span></span></a>';
$NEWMORE = '<a class="more-card" href="' . $AURL . '"><span class="mc-t">' . $T . '</span><span class="mc-d">Denver Post reported it, Tomorrowland confirmed talks are underway, Colorado is pitching $500K. What the reporting supports, what is still rumor - and why a winter edition reshapes the North American calendar.</span></a>';
$TOMLD = '{"@type":"BlogPosting","headline":"' . $T . '","datePublished":"2026-11-21","url":"' . $AURL . '"},';

// ---------- ops (needle => replacement), every needle must match EXACTLY once ----------
function ops_for($which, $NEWCARD, $NEWMORE, $TOMLD) {
	$ops = array();
	if ($which === 'index') {
		$ops[] = array('<a class="card" href="https://bryanhamilton.info/writing/php-l-passed-my-bug-as-a-feature/" style=', $NEWCARD . '<a class="card" href="https://bryanhamilton.info/writing/php-l-passed-my-bug-as-a-feature/" style=');
		$ops[] = array('Show 18 more: 14 articles + 4 cross-posts', 'Show 19 more: 15 articles + 4 cross-posts');
		$ops[] = array('+ 14 more deep-dives', '+ 15 more deep-dives');
	}
	if ($which === 'hub') {
		$ops[] = array('<a class="more-card" href="https://bryanhamilton.info/writing/php-l-passed-my-bug-as-a-feature/">', $NEWMORE . '<a class="more-card" href="https://bryanhamilton.info/writing/php-l-passed-my-bug-as-a-feature/">');
		$ops[] = array('{"@type":"BlogPosting","headline":"php -l Passed My Bug as a Feature: Lint Is Not a Rehearsal"', $TOMLD . '{"@type":"BlogPosting","headline":"php -l Passed My Bug as a Feature: Lint Is Not a Rehearsal"');
		$ops[] = array('<li>28 articles</li>', '<li>29 articles</li>');
	}
	if ($which === 'ogman') {
		$ops[] = array('"p": {', '"p": {' . "\n        " . '"tomorrowland-winter-colorado": "Tomorrowland Is Eyeing Colorado. Here\'s What\'s Confirmed (and What Isn\'t)",');
	}
	if ($which === 'rcpt') {
		$ops[] = array('>105 receipts', '>106 receipts');
	}
	if ($which === 'wd') {
		$ops[] = array('<b>105 changes shipped</b>', '<b>106 changes shipped</b>');
		$ops[] = array('<b>105 live-verified</b>', '<b>106 live-verified</b>');
	}
	return $ops;
}

// structural move: cut the 10th visible card (dead-at-file) and re-insert it in the expander
function move_card($src, $hrefNeedle, $beforeNeedle) {
	if (substr_count($src, $hrefNeedle) !== 1) { return array(null, 'dead-at-file anchor count != 1'); }
	if (substr_count($src, $beforeNeedle) !== 1) { return array(null, 'expander anchor count != 1'); }
	$p0 = strpos($src, $hrefNeedle);
	$pe = strpos($src, '</a>', $p0);
	if ($pe === false) { return array(null, 'card end not found'); }
	$pe += 4;
	$card = substr($src, $p0, $pe - $p0);
	$out = substr($src, 0, $p0) . substr($src, $pe);
	$p2 = strpos($out, $beforeNeedle);
	$out = substr($out, 0, $p2) . $card . substr($out, $p2);
	return array($out, 'moved: ' . strlen($card) . ' B card');
}

function apply_ops($src, $ops) {
	foreach ($ops as $op) {
		if (substr_count($src, $op[0]) !== 1) { return array(null, 'NEEDLE != 1: ' . substr($op[0], 0, 60)); }
	}
	foreach ($ops as $op) { $src = str_replace($op[0], $op[1], $src); }
	foreach ($ops as $op) {
		if (strpos($src, $op[1]) === false) { return array(null, 'VERIFY TAG BAD'); }
	}
	return array($src, 'ok');
}

function receipts_row($r) {
	$d = date('M j');
	$new = '<tr><td class="d">' . $d . '</td><td><span class="tag bh">BH</span></td><td><span class="item">Tomorrowland article surfaces</span><span class="note">The Nov 21 publish shipped the page but missed its discovery surfaces: hub card, blogPost JSON-LD entry, article count 28 to 29, homepage card with the 10-visible cap held, og-manifest entry, receipts row, hero recount. All closed in one pass.</span></td><td class="v" title="verified on the live site">&#10003; verified</td></tr>';
	$a = '<tr><td class="d">';
	$p = strpos($r, $a);
	if ($p === false) { return array(null, 'receipts anchor not found'); }
	return array(substr($r, 0, $p) . $new . substr($r, $p), 'row ok');
}

// ---------- SELFTEST on embedded mini-fixtures ----------
if ($SELFTEST) {
	$S = 'style="S"';
	$fx = array();
	$fx['index'] = '<section id="articles"><div class="cards articles"><!-- bh-art10 -->'
		. '<a class="card" href="https://bryanhamilton.info/writing/php-l-passed-my-bug-as-a-feature/" ' . $S . '>P1</a>'
		. '<a class="card" href="/writing/dead-at-file-30-of-62/" ' . $S . '>D1</a>'
		. '</div><details class="more-projects"><summary><span class="summary-heading">Show 18 more: 14 articles + 4 cross-posts</span><span class="summary-meta">Deploy workers &middot; + 14 more deep-dives</span></summary><div class="cards articles" style="margin-top:14px">'
		. '<a class="card" href="/writing/most-traveled-musicians-djs/" ' . $S . '>M1</a>'
		. '<a class="card" href="/writing/lazy-cron-scheduled-jobs-zero-crontab/" ' . $S . '>L1</a>'
		. '</div></details></section>';
	$fx['hub'] = 'x{"@type":"BlogPosting","headline":"php -l Passed My Bug as a Feature: Lint Is Not a Rehearsal","datePublished":"2026-11-18"}y'
		. '<a class="more-card" href="https://bryanhamilton.info/writing/php-l-passed-my-bug-as-a-feature/"><span class="mc-t">P</span></a><ul class="facts"><li>28 articles</li></ul>';
	$fx['ogman'] = '{"d":"x","p": {' . "\n        " . '"php-l-passed-my-bug-as-a-feature": "php -l"}}';
	$fx['rcpt'] = '<span class="count">105 receipts &middot; Oct-Nov 2026 &middot; all live-verified</span><table><tr><td class="d">Nov 19</td><td>OLDROW</td></tr></table>';
	$fx['wd'] = '<span class="wd-out">status: <b>105 changes shipped</b> / <b>105 live-verified</b></span>';

	$fail = 0;
	// index: ops + move
	list($o, $m) = apply_ops($fx['index'], ops_for('index', $NEWCARD, $NEWMORE, $TOMLD));
	if ($o !== null) { list($o, $m2) = move_card($o, '<a class="card" href="/writing/dead-at-file-30-of-62/" ' . $S, '<a class="card" href="/writing/lazy-cron-scheduled-jobs-zero-crontab/" ' . $S); }
	$checks = array(
		'o1 index ops+move' => ($o !== null),
		'o2 marker x1' => (substr_count($o, 'bh-tml1') === 1),
		'o3 tomorrowland card x1' => (substr_count($o, 'writing/tomorrowland-winter-colorado/') === 1),
		'o4 dead-at-file once, in expander' => (substr_count($o, 'dead-at-file-30-of-62') === 1 && strpos($o, 'dead-at-file-30-of-62') > strpos($o, 'more-projects')),
		'o5 summary updated' => (strpos($o, 'Show 19 more: 15 articles + 4 cross-posts') !== false && strpos($o, '+ 15 more deep-dives') !== false && strpos($o, 'Show 18 more') === false),
		'o6 visible intact' => (strpos($o, '>P1</a>') !== false && strpos($o, '>L1</a>') !== false),
	);
	list($h, $m) = apply_ops($fx['hub'], ops_for('hub', $NEWCARD, $NEWMORE, $TOMLD));
	$checks['h1 hub ops'] = ($h !== null);
	$checks['h2 more-card before php-l'] = ($h !== null && strpos($h, 'tomorrowland-winter-colorado') < strpos($h, '"more-card" href="https://bryanhamilton.info/writing/php-l'));
	$checks['h3 jsonld before php-l entry'] = ($h !== null && strpos($h, '"datePublished":"2026-11-21"') < strpos($h, 'Lint Is Not a Rehearsal'));
	$checks['h4 29 articles'] = ($h !== null && strpos($h, '<li>29 articles</li>') !== false);
	list($g, $m) = apply_ops($fx['ogman'], ops_for('ogman', $NEWCARD, $NEWMORE, $TOMLD));
	$checks['g1 og ops'] = ($g !== null);
	$checks['g2 og json valid + key'] = ($g !== null && json_decode($g) !== null && strpos($g, 'tomorrowland-winter-colorado') !== false);
	list($r, $m) = receipts_row($fx['rcpt']);
	list($r, $m2) = ($r !== null) ? apply_ops($r, ops_for('rcpt', $NEWCARD, $NEWMORE, $TOMLD)) : array(null, '');
	$checks['r1 receipts row + chip'] = ($r !== null && strpos($r, '>106 receipts') !== false && substr_count($r, '<tr><td class="d">') === 2);
	$checks['r2 row before old row'] = ($r !== null && strpos($r, 'Tomorrowland article surfaces') < strpos($r, 'OLDROW'));
	list($w, $m) = apply_ops($fx['wd'], ops_for('wd', $NEWCARD, $NEWMORE, $TOMLD));
	$checks['w1 hero 106/106'] = ($w !== null && strpos($w, '<b>106 changes shipped</b> / <b>106 live-verified</b>') !== false);
	foreach ($checks as $k => $v) { echo (($v) ? 'PASS ' : 'FAIL ') . $k . "\n"; if (!$v) { $fail++; } }
	echo $fail === 0 ? "SELFTEST: ALL PASS\n" : "SELFTEST: $fail FAIL\n";
	exit;
}

// ---------- DRY / GO on real files ----------
$files = array(
	'index' => __DIR__ . '/index.html',
	'hub'   => __DIR__ . '/writing/index.html',
	'ogman' => __DIR__ . '/og-manifest.json',
	'rcpt'  => __DIR__ . '/receipts/index.html',
	'wd'    => __DIR__ . '/web-developer/index.html',
);
$src = array();
foreach ($files as $k => $f) {
	if (!is_file($f)) { echo "READ FAIL: $k ($f)\n"; exit; }
	$src[$k] = file_get_contents($f);
}
$deadNeedle = '<a class="card" href="/writing/dead-at-file-30-of-62/" style=';
$lazyNeedle = '<a class="card" href="/writing/lazy-cron-scheduled-jobs-zero-crontab/" style=';
// needle count pre-flight on real bytes
$pre = array();
$pre[] = array('index php-l card', $src['index'], '<a class="card" href="https://bryanhamilton.info/writing/php-l-passed-my-bug-as-a-feature/" style=');
$pre[] = array('index dead-at-file card', $src['index'], $deadNeedle);
$pre[] = array('index lazy-cron card', $src['index'], $lazyNeedle);
$pre[] = array('index summary', $src['index'], 'Show 18 more: 14 articles + 4 cross-posts');
$pre[] = array('index deep-dives', $src['index'], '+ 14 more deep-dives');
$pre[] = array('hub more-card', $src['hub'], '<a class="more-card" href="https://bryanhamilton.info/writing/php-l-passed-my-bug-as-a-feature/">');
$pre[] = array('hub jsonld', $src['hub'], '{"@type":"BlogPosting","headline":"php -l Passed My Bug as a Feature: Lint Is Not a Rehearsal"');
$pre[] = array('hub count', $src['hub'], '<li>28 articles</li>');
$pre[] = array('og p-block', $src['ogman'], '"p": {');
$pre[] = array('og already-patched?', $src['ogman'], 'tomorrowland-winter-colorado');
$pre[] = array('rcpt chip', $src['rcpt'], '>105 receipts');
$pre[] = array('rcpt anchor', $src['rcpt'], '<tr><td class="d">');
$pre[] = array('wd shipped', $src['wd'], '<b>105 changes shipped</b>');
$pre[] = array('wd verified', $src['wd'], '<b>105 live-verified</b>');
$bad = 0;
foreach ($pre as $p) {
	$c = substr_count($p[1], $p[2]);
	echo (($p[0] === 'og already-patched?') ? "info" : "needle") . " | {$p[0]} | count=$c\n";
	if ($p[0] === 'og already-patched?') { if ($c !== 0) { echo "ALREADY PATCHED (og-manifest has the slug) - ABORT\n"; exit; } continue; }
	if ($p[0] === 'rcpt anchor') { if ($c < 1) { $bad++; } continue; } // >=1 is fine
	if ($c !== 1) { $bad++; }
}
if ($bad > 0) { echo "DRY: $bad needle(s) != 1 - ABORT (no writes)\n"; exit; }

// build all new contents BEFORE any write
$plans = array();
list($n, $m) = apply_ops($src['index'], ops_for('index', $NEWCARD, $NEWMORE, $TOMLD)); if ($n === null) { echo "INDEX $m - ABORT\n"; exit; }
list($n, $m) = move_card($n, $deadNeedle, $lazyNeedle); if ($n === null) { echo "INDEX MOVE $m - ABORT\n"; exit; }
$plans['index'] = $n;
list($n, $m) = apply_ops($src['hub'], ops_for('hub', $NEWCARD, $NEWMORE, $TOMLD)); if ($n === null) { echo "HUB $m - ABORT\n"; exit; }
$plans['hub'] = $n;
list($n, $m) = apply_ops($src['ogman'], ops_for('ogman', $NEWCARD, $NEWMORE, $TOMLD)); if ($n === null) { echo "OGMAN $m - ABORT\n"; exit; }
if (json_decode($n) === null) { echo "OGMAN JSON INVALID - ABORT\n"; exit; }
$plans['ogman'] = $n;
list($n, $m) = apply_ops($src['rcpt'], ops_for('rcpt', $NEWCARD, $NEWMORE, $TOMLD)); if ($n === null) { echo "RCPT $m - ABORT\n"; exit; }
list($n, $m) = receipts_row($n); if ($n === null) { echo "RCPT ROW $m - ABORT\n"; exit; }
$plans['rcpt'] = $n;
list($n, $m) = apply_ops($src['wd'], ops_for('wd', $NEWCARD, $NEWMORE, $TOMLD)); if ($n === null) { echo "WD $m - ABORT\n"; exit; }
$plans['wd'] = $n;

echo "DRY/GO plan:\n";
foreach ($plans as $k => $n) { echo sprintf("%-6s %d -> %d B (%+d)\n", $k, strlen($src[$k]), strlen($n), strlen($n) - strlen($src[$k])); }

if (!$GO) { echo "DRY OK - no writes. Add &go=go to apply.\n"; exit; }

// GO: backup, write, verify written bytes, rollback on any failure
$done = array(); $fail = 0;
foreach ($files as $k => $f) {
	$bak = $f . '.bak-tml1.bak';
	if (!copy($f, $bak)) { echo "BACKUP FAIL: $k\n"; $fail++; break; }
	if (!file_put_contents($f, $plans[$k])) { echo "WRITE FAIL: $k\n"; copy($bak, $f); $fail++; break; }
	$written = file_get_contents($f);
	if ($written !== $plans[$k]) { echo "VERIFY FAIL (bytes): $k - restoring\n"; copy($bak, $f); $fail++; break; }
	$done[$k] = $bak;
}
if ($fail > 0) {
	foreach ($done as $k => $bak) { copy($bak, $files[$k]); }
	echo "ROLLED BACK all patched files\n"; exit;
}
foreach ($done as $k => $bak) { unlink($bak); }
echo "GO OK: " . implode(', ', array_keys($done)) . " patched + verified\n";

// IndexNow
$urls = array($AURL, 'https://bryanhamilton.info/writing/', 'https://bryanhamilton.info/');
$payload = json_encode(array('host' => 'bryanhamilton.info', 'key' => 'ec61cb24045482a3a8b4621740fad3bb', 'keyLocation' => 'https://bryanhamilton.info/ec61cb24045482a3a8b4621740fad3bb.txt', 'urlList' => $urls));
if (!function_exists('curl_init')) { echo "IndexNow: curl extension missing - ping skipped\n"; unlink(__FILE__); echo "worker self-deleted\n"; exit; }
$ch = curl_init('https://api.indexnow.org/indexnow');
curl_setopt_array($ch, array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => array('Content-Type: application/json;charset=utf-8'), CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 15));
$rs = curl_exec($ch);
$code = ($rs !== false) ? curl_getinfo($ch, CURLINFO_HTTP_CODE) : 0;
echo "IndexNow: HTTP $code\n";

unlink(__FILE__);
echo "worker self-deleted\n";
