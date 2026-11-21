<?php
header('Content-Type: text/plain; charset=utf-8');
if (!isset($_GET['t']) || $_GET['t'] !== 'bh-TmW2!') { http_response_code(404); echo '404'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$NL = chr(10);
$ROOT = __DIR__;
$SLUG = 'tomorrowland-winter-colorado';
$BASE = 'https://bryanhamilton.info';
$URL = $BASE . '/writing/' . $SLUG . '/';
$OGIMG = $BASE . '/og.php?a=' . $SLUG;
$DIR = $ROOT . '/writing/' . $SLUG;
$TPL = $ROOT . '/writing/dead-at-file-30-of-62/index.html';
$TITLE = "Tomorrowland Is Eyeing Colorado. Here's What's Confirmed (and What Isn't)";
$DESC = 'Tomorrowland is in talks to bring a winter edition to a Colorado ski resort. Here is what the Denver Post reporting confirms and what is still open.';
$PUB = '2026-11-21';
$PUBRSS = 'Sat, 21 Nov 2026 15:00:00 +0000';
$KEY = 'ec61cb24045482a3a8b4621740fad3bb';

function fail($m) { echo chr(10) . 'ABORT: ' . $m . chr(10) . 'Nothing was written.' . chr(10); exit; }
function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function need($n, $want, $label) { if ($n !== $want) { fail($label . ' found ' . $n . ', need ' . $want); } }
function rd($p) { $s = @file_get_contents($p); if ($s === false) { fail('missing file: ' . $p); } return $s; }
function ctx($p, $needle, $before, $after, $label) {
  echo '== PROBE ' . $label . chr(10);
  $s = @file_get_contents($p);
  if ($s === false) { echo '  (file missing)' . chr(10) . chr(10); return; }
  echo '  count=' . substr_count($s, $needle) . chr(10);
  $pos = strpos($s, $needle);
  if ($pos !== false) { echo '  ' . json_encode(substr($s, max(0, $pos - $before), $before + strlen($needle) + $after), JSON_INVALID_UTF8_SUBSTITUTE) . chr(10); }
  echo chr(10);
}
function add_line($s, $url, $title, $desc, $label) {
  if (substr_count($s, $url) > 0) { return array($s, $label . ': already lists the URL, SKIPPED'); }
  $lines = explode(chr(10), $s);
  $idx = -1;
  foreach ($lines as $i => $ln) { if (preg_match('#/writing/[a-z0-9]#', $ln)) { $idx = $i; break; } }
  $newLine = '- [' . $title . '](' . $url . '): ' . $desc;
  if ($idx < 0) { $lines[] = $newLine; return array(implode(chr(10), $lines), $label . ': no article line to anchor on, appended at end'); }
  array_splice($lines, $idx, 0, array($newLine));
  return array(implode(chr(10), $lines), $label . ': inserted before line ' . ($idx + 1) . ': ' . substr($lines[$idx + 1], 0, 200));
}

if ($GO) { echo 'MODE: GO (writes enabled)' . $NL; } else { echo 'MODE: DRY RUN (nothing written; add &go=go to ship)' . $NL; }
if (is_dir($DIR)) { fail('writing/' . $SLUG . '/ already exists'); }

$BODY = <<<'ARTBODY'
<h1>Tomorrowland Is Eyeing Colorado. Here's What's Confirmed (and What Isn't)</h1>
<p><time datetime="2026-11-21">November 21, 2026</time></p>
<p>Tomorrowland, the Belgian festival, may be coming to the Colorado mountains. The Denver Post reported on November 19 that the festival is eyeing a ski resort, and on November 20 Tomorrowland confirmed that talks are underway. Nothing is signed, and no resort has been named. Here is what the reporting supports so far.</p>
<h2>What is confirmed</h2>
<p>The Denver Post reports that the festival wants a Colorado resort to be its exclusive location in North America, and that the state is pitching $500,000 to make it happen. Tomorrowland has confirmed talks. According to the paper's follow-up, Colorado beat out Utah, California and Canadian resorts for the bid, but the paper also notes that the real test is still ahead.</p>
<h2>What the event would look like</h2>
<p>The model is the immersive winter event Tomorrowland already holds in the French Alps. The Denver Post says Tomorrowland will likely try to replicate that event in Colorado.</p>
<h2>Why "exclusive" matters</h2>
<p>In plain terms, if a Colorado resort becomes the only North American home for the winter festival, the whole bet sits on one town and one set of mountain roads. That is my read of the reporting, not something the festival has said.</p>
<h2>What is still open</h2>
<p>No resort has been named. No dates or ticket sales have been announced. The reporting does not say how the state's $500,000 would be spent or how the festival would handle travel and lodging in a ski town. Those are the questions to watch.</p>
<p>I run The DJ Calendar, which tracks tour dates for DJs, so I'll be watching for dates first. If a resort or a date is announced, this page will get an update.</p>
<p class="sources">Sources: <a href="https://www.denverpost.com/2026/11/19/edm-resorts-music-festival-mountains/">Denver Post, November 19, 2026</a> and <a href="https://www.denverpost.com/2026/11/20/tomorrowland-colorado-edm-festival/">Denver Post, November 20, 2026</a>. More in the <a href="/writing/">writing hub</a>.</p>
ARTBODY;

$tpl = rd($TPL);
echo 'template bytes: ' . strlen($tpl) . $NL;
need(substr_count($tpl, '<article'), 1, 'article open in template');
need(substr_count($tpl, '</article>'), 1, 'article close in template');
$tpl = preg_replace('#<h1[^>]*>.*?</h1>#is', '', $tpl, 1, $h1Removed);
echo 'template h1 removed: ' . $h1Removed . $NL;
$a0 = strpos($tpl, '<article');
$a1 = strpos($tpl, '>', $a0);
$open = substr($tpl, $a0, $a1 - $a0 + 1);
$c0 = strpos($tpl, '</article>');
$pageArticle = substr($tpl, 0, $a0) . $open . $NL . $BODY . $NL . '</article>' . substr($tpl, $c0 + 10);

$page = $pageArticle;
$oldTitle = preg_match('#<title>(.*?)</title>#is', $tpl, $tm) ? $tm[1] : '(none)';
$extra = '';
$TITLE_TAG = '<title>' . esc($TITLE) . ' | Bryan Hamilton</title>';
if (preg_match('#<title>.*?</title>#is', $page) > 0) { $page = preg_replace('#<title>.*?</title>#is', $TITLE_TAG, $page, 1); } else { $extra .= $TITLE_TAG . $NL; }
$DESC_RX = '#<meta[[:space:]]+name=.description.[^>]*>#i';
$DESC_TAG = '<meta name="description" content="' . esc($DESC) . '">';
if (preg_match($DESC_RX, $page) > 0) { $page = preg_replace($DESC_RX, $DESC_TAG, $page, 1); } else { $extra .= $DESC_TAG . $NL; }
$CAN_RX = '#<link[[:space:]]+rel=.canonical.[^>]*>#i';
$CAN_TAG = '<link rel="canonical" href="' . $URL . '">';
if (preg_match($CAN_RX, $page) > 0) { $page = preg_replace($CAN_RX, $CAN_TAG, $page, 1); } else { $extra .= $CAN_TAG . $NL; }
$page = preg_replace('#<meta[[:space:]]+(property|name)=.(og|twitter):[^>]*>[[:space:]]*#i', '', $page, -1, $ogRemoved);

$ldRemoved = 0;
$page = preg_replace_callback('#<script[[:space:]]+type=.application/ld[+]json.[[:space:]]*>(.*?)</script>[[:space:]]*#is', function ($m) use (&$ldRemoved) {
  if (preg_match('#"(Article|BlogPosting|NewsArticle)"|dead-at-file#', $m[1])) { $ldRemoved++; return ''; }
  return $m[0];
}, $page);

$ld = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array(
      '@type' => 'Article',
      'headline' => $TITLE,
      'description' => $DESC,
      'datePublished' => $PUB,
      'dateModified' => $PUB,
      'author' => array('@type' => 'Person', 'name' => 'Bryan Hamilton', 'url' => $BASE . '/'),
      'publisher' => array('@type' => 'Person', 'name' => 'Bryan Hamilton', 'url' => $BASE . '/'),
      'mainEntityOfPage' => $URL,
      'image' => $OGIMG,
    ),
    array(
      '@type' => 'BreadcrumbList',
      'itemListElement' => array(
        array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $BASE . '/'),
        array('@type' => 'ListItem', 'position' => 2, 'name' => 'Writing', 'item' => $BASE . '/writing/'),
        array('@type' => 'ListItem', 'position' => 3, 'name' => $TITLE, 'item' => $URL),
      ),
    ),
  ),
);
$ldJson = str_replace('</', '<\/', json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
if ($ldJson === false) { fail('json-ld encode failed'); }

$head = $extra . implode($NL, array(
  '<meta property="og:type" content="article">',
  '<meta property="og:title" content="' . esc($TITLE) . '">',
  '<meta property="og:description" content="' . esc($DESC) . '">',
  '<meta property="og:url" content="' . $URL . '">',
  '<meta property="og:image" content="' . $OGIMG . '">',
  '<meta property="og:image:width" content="1200">',
  '<meta property="og:image:height" content="630">',
  '<meta name="twitter:card" content="summary_large_image">',
  '<meta name="twitter:title" content="' . esc($TITLE) . '">',
  '<meta name="twitter:description" content="' . esc($DESC) . '">',
  '<meta name="twitter:image" content="' . $OGIMG . '">',
  '<script type="application/ld+json">' . $ldJson . '</script>',
)) . $NL;
need(substr_count($page, '</head>'), 1, '</head> in template');
$page = str_replace('</head>', $head . '</head>', $page);
$residual = substr_count($page, 'dead-at-file');

$smPath = $ROOT . '/sitemap.xml';
$sm = rd($smPath);
$smNew = $sm;
if (substr_count($sm, $URL) > 0) { $smMsg = 'sitemap: already lists the URL, SKIPPED'; }
elseif (substr_count($sm, '</urlset>') === 1) { $smNew = str_replace('</urlset>', '  <url><loc>' . $URL . '</loc><lastmod>' . $PUB . '</lastmod></url>' . $NL . '</urlset>', $sm); $smMsg = 'sitemap: entry added'; }
else { $smMsg = 'sitemap: no single </urlset>, SKIPPED'; }

$rssPath = $ROOT . '/rss.xml';
$rss = rd($rssPath);
$rssNew = $rss;
$rp = strpos($rss, '<item>');
if (substr_count($rss, '<link>' . $URL . '</link>') > 0) { $rssMsg = 'rss: already lists the URL, SKIPPED'; }
elseif ($rp === false) { $rssMsg = 'rss: no item, SKIPPED'; }
else {
  $item = '<item>' . $NL . '<title>' . esc($TITLE) . '</title>' . $NL . '<link>' . $URL . '</link>' . $NL . '<guid isPermaLink="true">' . $URL . '</guid>' . $NL . '<pubDate>' . $PUBRSS . '</pubDate>' . $NL . '<description>' . esc($DESC) . '</description>' . $NL . '</item>' . $NL;
  $rssNew = substr($rss, 0, $rp) . $item . substr($rss, $rp);
  $rssMsg = 'rss: new item added first';
}

$llmsPath = $ROOT . '/llms.txt';
$llms = rd($llmsPath);
list($llmsNew, $llmsMsg) = add_line($llms, $URL, $TITLE, $DESC, 'llms.txt');
$aiPath = $ROOT . '/ai.txt';
$ai = rd($aiPath);
list($aiNew, $aiMsg) = add_line($ai, $URL, $TITLE, $DESC, 'ai.txt');

$omPath = $ROOT . '/og-manifest.json';
$om = rd($omPath);
$omNew = $om;
$data = json_decode($om, true);
if (substr_count($om, $SLUG) > 0) { $omMsg = 'og-manifest: already lists the slug, SKIPPED'; }
elseif (!is_array($data) || count($data) < 1) { $omMsg = 'og-manifest: not a non-empty JSON array or object, SKIPPED'; }
else {
  $isList = (array_keys($data) === range(0, count($data) - 1));
  $first = reset($data);
  if (!is_array($first) || ($isList && !array_key_exists('slug', $first))) { $omMsg = 'og-manifest: entry shape not recognized, SKIPPED'; }
  else {
    $entry = $first;
    if (array_key_exists('slug', $entry)) { $entry['slug'] = $SLUG; }
    if (array_key_exists('title', $entry)) { $entry['title'] = $TITLE; }
    if (array_key_exists('description', $entry)) { $entry['description'] = $DESC; }
    if ($isList) { $data[] = $entry; } else { $data[$SLUG] = $entry; }
    $enc = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($enc !== false) { $omNew = $enc; $omMsg = 'og-manifest: entry added'; } else { $omMsg = 'og-manifest: encode failed, SKIPPED'; }
  }
}

echo $NL . '== PLAN' . $NL;
echo 'NEW writing/' . $SLUG . '/index.html: ' . strlen($page) . ' bytes' . $NL;
echo 'old template title: ' . $oldTitle . $NL;
echo 'og+twitter tags removed: ' . $ogRemoved . '; old json-ld article blocks removed: ' . $ldRemoved . $NL;
echo 'residual dead-at-file in new page (nav-only is fine): ' . $residual . $NL;
echo $smMsg . $NL;
echo $rssMsg . $NL;
echo $llmsMsg . $NL;
echo $aiMsg . $NL;
echo $omMsg . $NL . $NL;

ctx($ROOT . '/writing/index.html', 'dead-at-file-30-of-62', 500, 200, 'writing hub (where the new card goes)');
ctx($ROOT . '/index.html', '/writing/', 300, 300, 'homepage article link');
ctx($ROOT . '/receipts/index.html', '<tr><td class=d', 0, 400, 'receipts newest row');

if (!$GO) { echo 'DRY RUN complete. Nothing written.' . $NL; exit; }

$writes = array(
  $smPath => array($smNew, $sm),
  $rssPath => array($rssNew, $rss),
  $llmsPath => array($llmsNew, $llms),
  $aiPath => array($aiNew, $ai),
  $omPath => array($omNew, $om),
);
$changed = array();
foreach ($writes as $p => $pair) { if ($pair[0] !== $pair[1]) { $changed[$p] = $pair[0]; } }
foreach ($changed as $p => $content) { if (!copy($p, $p . '.bak-tmw2')) { fail('backup failed: ' . $p); } }
if (!is_dir($DIR) && !mkdir($DIR, 0755, true)) { fail('could not create article dir'); }
if (file_put_contents($DIR . '/index.html', $page) !== strlen($page)) { fail('article write short'); }
echo 'wrote writing/' . $SLUG . '/index.html' . $NL;
foreach ($changed as $p => $content) {
  if (file_put_contents($p, $content) !== strlen($content)) { fail('write short: ' . $p . ' (backups kept as .bak-tmw2)'); }
  echo 'wrote ' . basename($p) . $NL;
}

$checks = array('article' => substr_count(rd($DIR . '/index.html'), $URL) > 0 && substr_count(rd($DIR . '/index.html'), 'og:image') > 0);
if (isset($changed[$smPath])) { $checks['sitemap'] = substr_count(rd($smPath), $URL) > 0; }
if (isset($changed[$rssPath])) { $checks['rss'] = substr_count(rd($rssPath), $URL) > 0; }
if (isset($changed[$llmsPath])) { $checks['llms'] = substr_count(rd($llmsPath), $URL) > 0; }
if (isset($changed[$aiPath])) { $checks['ai'] = substr_count(rd($aiPath), $URL) > 0; }
if (isset($changed[$omPath])) { $checks['og-manifest'] = substr_count(rd($omPath), $SLUG) > 0; }
foreach ($checks as $k => $ok) { echo 'verify ' . $k . ': ' . ($ok ? 'OK' : 'FAILED') . $NL; }

$payload = json_encode(array('host' => 'bryanhamilton.info', 'key' => $KEY, 'keyLocation' => $BASE . '/' . $KEY . '.txt', 'urlList' => array($URL)));
$ctxHttp = stream_context_create(array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/json; charset=utf-8' . chr(13) . chr(10), 'content' => $payload, 'timeout' => 15, 'ignore_errors' => true)));
$resp = @file_get_contents('https://api.indexnow.org/indexnow', false, $ctxHttp);
echo 'IndexNow: ' . (isset($http_response_header[0]) ? $http_response_header[0] : 'no response') . $NL;

@unlink(__FILE__);
echo 'worker self-deleted. Backups kept as *.bak-tmw2.' . $NL;
