## 2026-11-20 - Stew Leonard's hire banner (bryanhamilton.info)
- Change: "Fresh out of the kitchen: Bryan is the new Hoedown chef at Stew Leonard's" fixed banner at the top of the homepage, non-dismissible, cow logo, links to stewleonards.com. Body padding-top 35px so the site shifts down beneath it. Went relative, then sticky, then fixed before the final v4 landed.
- Risk: a fixed banner without matching body padding covers the header. The nav and corner card needed their own offsets once the banner was fixed.
- Verify: cache-busted live: banner marker bh-chef4-fix x1, logo 200, body padding 35px, banner copy and stewleonards.com link present.
- Tag: none (baks pending sweep)

## 2026-11-20 - bhnavfix1 (bryanhamilton.info)
- Change: stickybar (photo dropdown + Tech/Retail/Book options) moved from top 0 to top 35px so it sits beneath the Stew Leonard's banner when scrolling.
- Risk: raising z-index would only hide the nav behind the banner; the offset puts it fully below.
- Verify: cache-busted live: stickybar rule top 35px.
- Tag: none (baks pending sweep)

## 2026-11-20 - bhcorner1 (bryanhamilton.info)
- Change: QR .corner card top moved to 70px so it clears the banner and nav. The print rule (top 0) is untouched.
- Risk: the screen rule was unique; the print stylesheet has its own top 0 that must not change.
- Verify: cache-busted live: .corner top 70px, print rule top 0 unchanged.
- Tag: none (baks pending sweep)

## 2026-11-20 - bhjobs1-3 (bryanhamilton.info)
- Change: Stew Leonard's row added to the career timeline ("2026 - Now", Hoedown chef), on the same line as the other seven jobs. The grid went from 7 to 8 columns.
- Risk: an 8th item in a 7-column grid wraps onto its own line, so the column count has to change with the row.
- Verify: cache-busted live: timeline row 8 present, 8-column grid rule live, Stew Leonard's row and 2026 - Now label present.
- Tag: none (baks pending sweep)

## 2026-11-19 - bhexpfix1 (bryanhamilton.info)
- Change: rack expander fixed - the "Show 14 more projects" toggle was dead not because of the button but because bhfx1's drag-reorder restore re-appended EVERY .rack-card into the main grid on load (the saved-order restore loop ran seq.forEach(c => grid.appendChild(c)) over all 21 cards), so anyone who had ever dragged a card had all 21 pulled out of the details expander, leaving it empty. One needle: the restore loop now guards on the card already being in the main grid, so only grid cards re-order and the 14 rolled-up cards stay in the details. rack.js v48->v49 bumped across 65 HTML pages (1 ref each). Node sim on a 21-card DOM with a polluted saved order: OLD 21-in-grid / 0 rolled up, NEW 7 / 14
- Risk: polluted localStorage orders self-heal - the 21-entry saved order is harmlessly ignored for non-grid cards and the next drag persists 7. Backup kept only on rack.js; the 65 HTML bumps are exact counted 1-char swaps with in-memory rollback and per-file verify
- Verify: cache-busted live: served rack.js 61,597 B with marker x1 + guard x1 + old line 0; homepage v=49 x1 / v=48 x0 with 7 grid / 14 details cards; receipts 105 rows, chip 105, self-log row present; hero 105/105; /writing/ v=49; worker 404
- Tag: none (bak self-cleaned)

## 2026-11-19 - bhclsil6 (bryanhamilton.info)
- Change: Coder Legion medal sync - silver 5->6, bronze 34->35 (live truth read from coderlegion.com/user/bryanhamiltondev/badges hmb-* data-all attributes; Bryan called the silver first, the probe then showed bronze had ALSO moved). Homepage statics: aria-label, .cl-num-s data-to, .cl-num-b data-to, bronze tooltip title; resume '35 bronze badges'. Receipts chip fixed 97 -> 104 - it had DRIFTED: the last four self-logs (bhhstat1/bhwdsn1/bhrb1/bhtrfix1) added rows but never recomputed the chip, so the ledger claimed 97 with 103 rows. Chip AND web-developer hero both recomputed from the real row count at runtime; self-log row inserted BEFORE the first <tr><td class="d"> anchor
- Risk: fixture rehearsal caught a silent killer - PHP functions do not inherit outer scope, so patch() saw an undefined $go and would have skipped the index/resume WRITES on the host while still printing OK and self-deleting. global $go; fixed. Also: IndexNow code was read via curl_getinfo BEFORE curl_exec - always prints 0; ping actually fires. And bh-cl-live.php (60-min cache) served stale silver 5 while the CL profile said 6 - no purge param exists; it self-heals on the next cache expiry, medal dots ride it live
- Verify: cache-busted live: aria '1 gold, 6 silver, 35 bronze' x1, both data-to statics updated, 0 stragglers (no data-to=5 silver / 34 bronze / 34-bronze-tooltip), resume 35, chip 104 + 104 rows + self-log row present, hero 104/104, worker 404
- Tag: none (backups self-cleaned)

RESTORE NOTE (also Nov 19): the first attempt at this entry passed ONLY the entry text to the contents API, which REPLACES the whole file - main briefly held a 1,534-byte LOG.md. Full history recovered from commit 426c7f7 and re-committed here byte-identical. RULE: create_or_update_file REPLACES - always pass the complete file, entry + everything below it.

## 2026-11-19 - bhrb1 (bryanhamilton.info)
- Change: PSI fixes on the homepage - three render-blocking stylesheets (testimonials-reveal 1,936 B + contact-form 6,980 B + rack 4,864 B = 13,851 B) inlined into ONE <style id="bh-rb-inline"> before </head> (zero blocking CSS requests left), an LCP image preload (bryan-hamilton.jpg, fetchpriority=high) before the Anton font preload so the avatar starts downloading at head-parse instead of post-CSS, and the three 44px testimonial avatars GD-converted to 88x88 q78 webp (9.9/9.5/8.8 KB jpeg -> 986/1,258/1,500 B webp, srcs updated, originals kept for other pages). Receipts self-log + hero recount 101 -> 102/102 in the same pass
- Risk: three rehearsals on real served bytes caught three bugs before the server saw any: the three <link> tags are NOT adjacent (a concatenated str_replace matched 0 and auto-restored - per-tag strips instead); a pre-existing '.webp"' elsewhere on the page made a substr_count===3 verify guard permanently false (explicit per-file strpos checks instead); and grep -c vs substr_count confusion is why the first receipts recount probe misread
- Verify: server run matched rehearsal byte-for-byte (349,561 -> 363,349 B, webp 3/3, receipts 102, hero 102/102). Cache-busted live: 0 stylesheet /assets/css/ links, bh-rb-inline x1, preload line present, 3 webp srcs, matt-avatar.webp 200 image/webp (986 B), worker 404. PSI API is keyless-quota-blocked (0/day) - chased every flag against live source manually. Forced reflow DEFERRED until the report's offender rows are available (suspects: count-up/scroll inline scripts). QR PNG (19.8 KB, crispness) and the main 520px avatar (used at 36/104/40px) intentionally left as-is
- Tag: none (backup self-cleaned)

## 2026-11-19 - bhtrfix1 (bryanhamilton.info)
- Change: receipts table repair. bhwdsn1's self-log inserted "after the FIRST <tr>" - but that <tr> opened a real row, so the insert consumed its opener: one Nov 18 row had rendered merged since ~08:00 (signature: <tr><tr> double-open at the table head + exactly one </tr><td class="d"> orphan). bhtrfix1 dropped the stray opener, restored the orphan's opener (net 0 bytes), then inserted its own row with the CORRECTED pattern - before the first <tr><td class="d"> anchor, never after a bare <tr>. Hero recounted 102 -> 103/103. The receipts self-log snippet in the worker skill is updated to the corrected pattern so no future self-log can repeat this
- Risk: the "insert after the first <tr>" habit was safe only while the table's first <tr> was a header/empty row - once the ledger's first row became a data row, every insert silently ate its opener. Self-log code that rewrites its own ledger must anchor on row CONTENT (<tr><td class="d">), never structure (<tr>)
- Verify: cache-busted live: 0 <tr><tr>, 0 orphans, 103 openers / 103 closes / 103 cells, repair row present, hero 103/103 live, worker 404
- Tag: none (backup self-cleaned)

## 2026-11-19 - bhwdsn1 (bryanhamilton.info)
- Change: stray literal \n (2 bytes of backslash-n TEXT, not a newline) sat between the <!-- wdtreetrend/wdtrend0-wd --> comment marker and </body> on /web-developer/ - wdtrend0 residue, the classic literal-backslash-n injection bug. One needle: "-->\n</body>" -> "-->" + real newline + "</body>" (31,728 -> 31,727). Same pass: receipts self-log row (newest-first insert) + hero recount in the same worker, so the hero we just synced to 100/100 did not instantly drift: now 101/101
- Risk: the self-log note itself contained "</body>" - which would have injected raw markup into the receipts HTML. Caught in rehearsal: notes must describe markup in prose, never quote it
- Verify: cache-busted live: zero literal backslash-n in the served page, tail is comment -> newline -> </body>, served 31,727 B, hero "101 changes shipped / 101 live-verified", receipts carries the BHWDSN1 row, worker 404
- Tag: none (backup self-cleaned)

## 2026-11-19 - bhhstat1 (bryanhamilton.info)
- Change: web-developer hero stat synced to the real receipts ledger - the hero sat at 94/94 while /receipts/ moved to 100 rows (the wrt28 + receipts-reorder self-log rows landed after bhhero1's snapshot). bhhstat1 reads the LIVE row count at run time (count of class="v" verified cells = 100) and writes that, never a remembered number: hero now "100 changes shipped / 100 live-verified". Dry run read 100 and printed the plan without writing; GO wrote web-developer/index.html (31,726 -> 31,728 B), backup cleaned, self-deleted
- Risk: the same drift can recur - the fix pattern is "count at run time", but nothing re-syncs the hero automatically when receipts grows again. If it drifts again, the right move is making the hero read the count server-side (like the chip recompute), not another sync worker
- Verify: cache-busted live: hero "100 changes shipped / 100 live-verified" present, /receipts/ at exactly 100 rows (100 tr / 100 class="v"), served file 31,728 B matching the GO report, worker 404
- Tag: none (backup self-cleaned)

## 2026-11-19 - djccard1 (thedjcalendar.com)
- Change: branded share cards for ALL artist pages + newsletter, and the sitelinks-searchbox eligible. og.php did not exist on the DJC host - djccard1 created it (5,662 B): 1200x630 GD PNG, RANK #N badge chip, artist name shrink-to-fit, "N UPCOMING SHOWS" line from djs.json tour_dates, photo cover-cropped right with cyan seam, Anton embedded as og-font.ttf (170,812 B), 24h disk cache images/og-cache/, ?a=newsletter renders THE RADAR card. artist.php: $artistImageUrl swapped from the hotlinked 500px Wikimedia thumb (utm_source=en.wikipedia.org riding along; FB crawler often can't fetch Wikimedia = broken shares) to og.php?a=<slug> + og:image:type jpeg->png - all 99 artist pages upgraded in one needle. newsletter.php: all 3 og-default.jpg refs -> og.php?a=newsletter. index.php: WebSite JSON-LD gained potentialAction SearchAction -> /search/?s={search_term_string}
- Risk: two rehearsal catches on real dumped source: a double-quoted $replA would have interpolated $artistImageUrl/$artistSlug to empty (single-quoted fix), and the font write sat before the GO gate (dry run wrote og-font.ttf - moved behind &go). GD string fallback (imagestring) ships in case the TTF ever goes missing
- Verify: cache-busted live: og.php?a=davidguetta 200 image/png (392,622 B, valid 1200x630), ?a=newsletter 200 (21,231 B), artist og:image -> og.php?a=davidguetta, newsletter og:image -> og.php?a=newsletter, homepage SearchAction + valid JSON-LD, worker 404 (self-deleted). BONUS: session-expired source scan 0 hits across index/artist/header - the Nov 11 first-visit bug officially closed. IndexNow skipped (no DJC key on file)
- Tag: none (backups self-cleaned on success)

## 2026-11-18 - bhrcrev1 + bhtlrev1 (bryanhamilton.info)
- Change: /receipts/ and /timeline/ both reversed to newest-first per Bryan. bhrcrev1: all 97 receipt rows stable-sorted newest-first (same-day rows keep original order) with its own self-log row prepended -> 98. bhtlrev1: timeline month sections reversed (November 2026 first) and all 73 nodes sorted newest-first within each month (Nov 16 -> Oct 19), self-log row prepended to /receipts/ -> 99. The receipts self-log skill rule is updated: rows now INSERT after the first <tr>, never append - future deploys land on top
- Risk: PHP preg_match_all with PREG_OFFSET_CAPTURE puts the byte offset in $tok[1], not the captured group - reading it as the month/node type sorts months by address. Caught in php -r rehearsal, not by lint. And the receipts self-log must run AFTER the &go gate or a dry run writes a phantom row (first fixture run proved it)
- Verify: cache-busted live: /receipts/ top items Timeline newest-first then Receipts newest-first (99 rows), /timeline/ November 2026 first, nodes Nov 16 -> Oct 19, 73 nodes, both workers 404 (self-deleted)
- Tag: none (backups self-cleaned on success)

## 2026-11-18 - bh-wrt28 (bryanhamilton.info)
- Change: article #28 published - "php -l Passed My Bug as a Feature" (/writing/php-l-passed-my-bug-as-a-feature/, 39,361 B from the house article template, og.php?a=slug 200 image/png). Full 9-surface publish: hub card above Three Bugs + "27 articles"->28 + BlogPosting entry FIRST in the hub JSON-LD blogPost array; homepage card inserted at the TOP of the visible list with the 10th visible card (most-traveled-musicians-djs) MOVED into the expander so bhartcap1's 10-visible cap holds and the summary honestly reads "Show 18 more: 14 articles + 4 cross-posts"; llms.txt +1 entry above the Three Bugs line; ai.txt byte-copy resync (14,136 B identical); sitemap +1 (122 B); rss new first item; og-manifest +1; receipts row + chip recompute -> 97; IndexNow pinged article + /writing/
- Risk: a publish that inserts a card at the top of a capped list silently makes 11 visible - the worker moves the displaced card in the same pass and rewrites the expander count, so cap and counts move together. And the chip recompute is dynamic (substr_count('<tr>')+1), which is why the fixture (stale 95-row receipts) printed 96 while the live run printed 97 - both correct on their own base
- Verify: cache-busted live, all 9 surfaces PASS: page 200 (prose x1, LONG headline, zero three-bugs residue), home card x1 + summary + moved card present, hub 28 + card + blogPost-first, llms/ai byte-identical with the entry, sitemap/rss/og-manifest patched (og-manifest still valid JSON), receipts chip 97, og.php 200 image/png, IndexNow 200, worker self-deleted
- Fixture rehearsal caught MY bug of the day: strpos argument reversal (strpos('</a>',$p0) instead of strpos($home,'</a>',$p0)) - the cut-needle searched the WRONG HAYSTACK and would have failed silently on the host
- Tag: `.bak-wrt28.bak` x7 (pending sweep)

## 2026-11-18 - bh-repo20 + bh-repo20b (bryanhamilton.info)
- Change: repo #20 stale-asset-audit (github.com/bryanhamiltondev/stale-asset-audit) shipped with its on-site page at /projects/stale-asset-audit/ - the ?v= drift detector extracted from bhfx1b's 84-page incident (one PHP file, groups local script/stylesheet refs per version key, flags bare-vs-keyed mixed refs, --json mode, CI exit 0/1/2, zero dependencies). Worker synced repo counts 22->23 across ALL homepage surfaces (meta x2, FAQ JSON-LD + visible dd + text x7, numberOfItems 23, ListItem position 22), llms/ai.txt +1 entry (ai.txt byte-copy resync), sitemap +1, receipts row + chip 96, IndexNow
- Risk: the tool's FIRST test run reported "no drift" everywhere - the version key was read after the query-string strip, so every ref parsed versionless. A detector that detects nothing fails green; rehearsal caught it before the repo did. Second abort came ON the host: the worker assumed home.html but the BH homepage is static index.html (the rack2 lesson, already in the notes) - grep the deploy notes for a file's real name before writing any worker path
- Verify: bh-repo20b cleaned the 2 template leftovers the page swap missed (In-brief sentence + FAQ summary still said preview-media-session); cache-busted live: page 200 (7,204 B, 0 preview-media-session refs, SoftwareSourceCode JSON-LD), index.html 348,548 B with 8x "23 open-source" / 0x "22", llms/ai byte-identical 13,787 B, sitemap +1, receipts chip 96, og.php?a=stale-asset-audit 200 image/png, IndexNow 200 both runs, both workers self-deleted
- Tag: `.bak-repo20.bak` x4 + `.bak-repo20b.bak` (pending sweep)

## 2026-11-18 - bhfx1b (bryanhamilton.info)
- Change: catch-up rack.js version bump - bhfx1's sweep found only the homepage at v47 because earlier bump passes were page-local, so bhfx1b normalized every page still ref'ing rack.js?v=<old> or a bare versionless ref to v=48. 84 pages bumped: /writing/ + hub pages at v45, case-study pages at v33, resume html at v33/v38/v45, plus legacy worker .php files that also embed rack.js refs (dead files, bumped harmlessly - also a map of remaining sweep candidates)
- Risk: a stale ?v= key is a browser cache key - pages loading the CURRENT rack.js file under old keys served old cached JS to returning visitors indefinitely
- Verify: cache-busted live spot-checks all rack.js?v=48 (writing, case-studies, resume, playground, arcade, changelog, now, three-bugs article); backups .bak-fx1b.bak alongside each; worker self-deleted
- Fixture: dry+go on a tree covering v33/v45/v48/bare/no-ref; rehearsal caught a missing '.' concat php -l passed as a dynamic function call

## 2026-11-18 - bhfx1 (bryanhamilton.info)
- Change: rack power features, four in one worker - (1) drag-to-reorder rack cards: grip dots injected into every .rack-meta (pointer:fine only; touch keeps static order), HTML5 dnd with grid-level dragover insertion, order persisted to localStorage keyed by the S/N serials rackpulse1 stamps (stable identity even after reorder), idx plates renumbered on drop, cables rebuilt via the resize path. (2) Event cable pulses: light travels the patch-cable chain on a real signal - interval is 7s when commits.php shows a deploy <24h old, 25s otherwise; reduced-motion kills it; window.__bhPulse exposed for Cmd-K. (3) Health LEDs: new bh-health.php endpoint does server-side file_exists checks for every same-origin link on each card (no client HEADs - bhbayled1 lesson), green glow + tooltip = live, orange = missing, fetch failure = LED untouched. (4) Cmd-K actions: copy email / print resume / send a pulse / reset rack order, labeled "action" not "easter egg" via a render-line patch keyed on __act. rack.js v47->v48 bump pass
- Risk: two rehearsal bugs on real served fixtures - the appended JS block was defined but never concatenated (marker-absence check caught it), and the health endpoint omitted missing paths instead of reporting false, which would have silenced the amber LED forever. Also surfaced PRE-EXISTING version drift: the bump found only the homepage at v47 - /writing/ still refs v45, /case-studies/ v33, /resume/ v38 (earlier bump passes were page-local, so those pages load current rack.js but browsers may hold stale copies under old ?v= keys)
- Verify: cache-busted live: rack.js 61,447 B, marker x1, node --check clean, actions present, homepage refs v=48, bh-health.php returns true/false correctly with traversal paths filtered, worker self-deleted. Fixture: dry-run then go on real fetched rack.js + homepage

## 2026-11-18 - djaegate1 (thedjcalendar.com)
- Change: auto_enrich.php gated - anonymous GETs were live-triggering Wikipedia/Spotify fetches + djs.json rewrites (the endpoint's own comment says run from browser, but nothing enforced it, and the Spotify client secret rode along in a publicly reachable file). A guard before set_time_limit(300) allows PHP_SAPI === 'cli' (cron-safe) or $_GET['t'] === '<gate token>' (Bryan's manual browser runs), else a bare 403. Runner worker djaegate1 was a read-only probe (djcae0) first - needle count==1 confirmed before any write
- Risk: a gate on an endpoint with unknown cron callers silently breaks enrichment - the CLI escape hatch covers cron; any HTTP cron URL now needs the token appended. And a 403 indistinguishable from Cloudflare's challenge page would poison future debugging - the gate emits a bare 4-byte 403, no challenge markup
- Verify: cache-busted live: tokened run enriched 21 artists (Wikipedia 21, Spotify 0 cached, 78 skipped), anonymous GET = bare 403 (no CF challenge), worker self-deleted (404), djs.json serving fresh
- Tag: `.bak-aegate.bak` (self-cleaned)

## 2026-11-18 - bhfonts1 (bryanhamilton.info)
- Change: Google Fonts self-hosted - PSI's render-blocking 520ms was the homepage loading Google Fonts THREE ways (x2 preconnects + a preload-as-style + an async print/onload stylesheet + a plain render-blocking stylesheet that defeated the async one), with the LCP font (Anton) behind a third-party origin driving the LCP discovery / dependency-tree / layout-shift flags too. All googleapis/gstatic <link> lines removed from 5 pages (index.html x5, /timeline/ x2, now.html, /writing/ and the three-bugs article x1 each) and replaced before </head> with a local @font-face block (Anton 400 + JetBrains Mono 500/700 from one variable woff2, font-display:swap) plus an Anton preload. Fonts vendored at /fonts/anton-400.woff2 (12,004 B) + /fonts/jbm-var.woff2 (31,340 B - Google's own css2 serves the same variable URL for both weights). Resume bryan-hamilton-tech.html untouched - it loads Carlito, not Anton/JBM
- Risk: a @font-face pointing at a not-yet-uploaded file would silently degrade ALL typography - the worker hard-aborts unless both woff2 files exist on the host. And a strlen-shrink verify guard was WRONG: on single-link pages the injected @font-face block outweighs the one removed remote line, so the guard falsely failed sane patches - caught in sandbox rehearsal on fresh served fixtures; verify-marker + google-ref-absence are the real guards
- Verify: cache-busted live: zero fonts.googleapis/gstatic refs + marker x1 + 3 @font-face + Anton preload on all 5 pages, both woff2 files 200 font/woff2, resume still Carlito with zero markers, worker self-deleted (404)
- Tag: `.bak-fonts1.bak` (self-cleaned)

## 2026-11-18 - bhprint2 (bryanhamilton.info)
- Change: duplicate print button removed - bhprint1's JS-created floating DOWNLOAD PDF button (#bh-printbtn) sat alongside the original #print-resume in the toolbar nav, so the resume shipped two print affordances. Cut the injected IIFE, its @media screen styles, and the #bh-printbtn/.bh-printbtn refs in the print-rule selector; the original button, the @media print block and the ProfilePage JSON-LD are untouched. Receipts row + chip recompute
- Risk: a cleanup worker removing a prior worker's addition must keep the prior worker's other surfaces - the verify pins bh-printbtn gone AND bh-print1-css AND ProfilePage still present, restoring the backup if any check fails
- Verify: cache-busted live: zero bh-printbtn residue in the served resume, #print-resume present, receipts chip 95, worker self-deleted
- Tag: `.bak-print2.bak` (self-cleaned)

## 2026-11-18 - bhsweep1 + djcsweep1 + bhhero1 (bryanhamilton.info / thedjcalendar.com)
- Change: the cleanup day. bhsweep1 swept the BH host - spent one-shot workers (pulse, bhegg, bhord, bhdev, bhwrt23/25, bhart10, bhcnt/boot/pages/ogman) + stale baks, with .bak-bhwrt24.bak x7 preserved pending Bryan's eyeball; chip recompute added before ship (the worker predated the chip-recompute habit and would have drifted rows/chip). djcsweep1 swept the DJC host - dead djchover1-8, djcscore1, djcegg1, the two unidentified djcsig files, and the pending .bak-fav*/shpback/medias/slugfix pile. bhhero1 fixed the web-developer hero stat ("N changes shipped / N live-verified") that sat frozen at 45 while receipts hit 92 - instead of hardcoding another drift-prone number, it adds its own receipts row FIRST, then counts the real rows and writes that, so hero and chip can never disagree
- Risk: a stat synced by hand drifts every time its source moves - sync from the source count at run time, not from a remembered value. And a worker appending a ledger row without recomputing the chip lies by one row forever
- Verify: cache-busted live: hero 94 shipped / 94 live-verified, receipts chip 94 with Host cleanup + Hero stat sync rows both present, homepage LED zero bhs-djc residue + static green, both sweep workers self-deleted. PSI side-note: crawlable-links audit chased via static+JS analysis (151 anchors clean; the one href-less match is a regex literal inside a script) - API quota is a hard 0/day keyless, offender list needs a real PSI run
- Tag: `.bak-hero1.bak` (self-cleaned); sweeps delete only, no baks

## 2026-11-17 - bhbayled1 (bryanhamilton.info)
- Change: LED Commit Bay trimmed to one LED - the DJC status dot removed entirely per Bryan, and the BH dot made statically green. The old BH status did a HEAD request to /style.css and LiteSpeed can reject HEADs while serving the page fine, so the LED sat amber on a healthy site. New logic: if the page is rendering, the site is up - the dot is green, no fetch
- Risk: a status indicator that fetches to prove the page it is running on is up can only ever say "down" wrongly, never "up" wrongly - self-referential checks belong static
- Verify: cache-busted live homepage: zero bhs-djc residue, BH-only LED row with static green class, receipts chip 92 + row, IndexNow 200, worker self-deleted. Remaining top10.php reference is the hero chip's data fallback (bhhchip2), not the LED
- Tag: `.bak-bayled1.bak` (self-cleaned)

## 2026-11-17 - bhpms1 (bryanhamilton.info)
- Change: repo #19 preview-media-session got its on-site page at /projects/preview-media-session/ - the Nov 17 repo shipped with a social preview but zero on-site presence while the "22 open-source repos" claims already counted it (20 project pages + deploy-log + this one = 22, so the count promised a page that 404'd). House template clone from the og-card page: SoftwareSourceCode JSON-LD, og.php?a=preview-media-session card, canonical, FAQ block, llms/ai.txt +1 entry (ai.txt resynced as byte-copy), sitemap URL, IndexNow, receipts row + chip recompute
- Risk: the count claims were RIGHT and the page was wrong - an audit that only cross-checks claim-vs-claim passes this; the missing surface hid behind a count that already included it
- Verify: live page 200 (7,125 B) with styling + JSON-LD, llms/ai byte-identical at 13,585 B with the entry, sitemap +1, og.php 200 image/png, receipts chip 91, IndexNow 200, worker self-deleted
- Tag: `.bak-pms1.bak` x3 (self-cleaned)

## 2026-11-17 - bhclb34 (bryanhamilton.info)
- Change: Coder Legion bronze hit 34 - static chip surfaces synced: homepage aria-label, bronze medal tooltip title, bronze count-up static (data-to), plus a stale silver count-up static (data-to 3 vs the aria-label's 5), and the resume's "33 bronze badges". The live endpoint bh-cl-live.php already scraped 34 and the count-ups self-correct on load, but crawlers and no-JS visitors see statics. Receipts row + chip recompute
- Risk: the live-fetch block updates count-ups and the aria-label but NOT the medal title tooltip - statics drift invisibly whenever a surface never passes through the fetch updater
- Verify: live homepage aria "1 gold, 5 silver, 34 bronze", cl-num-b data-to 34, resume "34 bronze badges", receipts chip recomputed, IndexNow 200, worker self-deleted
- Tag: `.bak-clb34.bak` x3 (self-cleaned)

## 2026-11-17 - bhsaga1 (bryanhamilton.info)
- Change: full-site SAGA audit (61 sitemap pages) found 4 pages missing from llms.txt/ai.txt (case-studies, hiring, now.html, changelog.html) plus 73-byte ai.txt drift from bhuses1's old-format line; bhsaga1 inserted the 4 entries, resynced ai.txt to a byte-copy of llms.txt, and cleaned /receipts/ - removed 2 of 3 adjacent "Deploy strip v2" duplicate rows and corrected the chip 73 -> 89 (rows - 2 dups + 1 new row, computed from the actual row count, not hardcoded)
- Risk: the re-audit's one remaining flag is a false positive - the jsonld-schema-audit page's own demo JS contains the literal ld+json script string, which a regex audit catches; the real schema parses. Do not "fix"
- Verify: live llms.txt carries all 4 entries, ai.txt byte-identical to llms.txt (13,373 B), /receipts/ at 89 rows with chip 89 and a single "Deploy strip v2", IndexNow 200, worker self-deleted; re-audit 60/61 clean
- Tag: `.bak-bhsaga1.bak` x3 (self-cleaned)

# Deploy Log

One entry per production deploy. Tokens stripped; sites are public.

## 2026-11-17 - bhuses1 (bryanhamilton.info)
- Change: /uses/ shipped - the stack-and-infrastructure page (AEO play: "what does this dev actually run on"). Full SAGA from day one: canonical, OG card via og.php?a=uses (200 image/png), WebPage + Breadcrumb + FAQPage JSON-LD, llms.txt "## Stack" section, ai.txt mirror, sitemap entry, IndexNow ping, receipts row
- Risk: a new page that ships without its llms/ai.txt mirror splits the site's own corpus - both written in the same pass, never "later"
- Verify: cache-busted /uses/ live with styling + JSON-LD; og.php renders the PNG; worker self-deleted
- Tag: `.bak-bhuses1.bak`

## 2026-11-17 - bhproj9 (bryanhamilton.info)
- Change: 9 per-repo project pages generated at /projects/<slug>/ for the GitHub-hosted repos that had no on-site case study (og-card, lazy-cron, arcade, badge-proxy, browser-direct-feed, led-commit-wall, guarded-deploy-workers, guarded-deploy-worker, one-file-analytics). Names + descriptions parsed from llms.txt with an 8-entry sanity floor; llms/ai.txt GitHub links flipped to the on-site pages; sitemap + IndexNow. Template embedded via nowdoc - single-file worker rule held
- Risk: the repo ledger was llms.txt, not an API - parsed with a minimum-entry guard so a partial fetch aborts instead of generating 9 stub pages. Slug case caught in review (an /projects/Arcade/ path would have 404'd next to the lowercase convention)
- Verify: all 9 URLs 200 with SoftwareSourceCode JSON-LD; llms/ai.txt point on-site; worker self-deleted
- Tag: `.bak-bhproj9.bak`

## 2026-11-17 - bhprint1 (bryanhamilton.info)
- Change: one-click PDF resume - print stylesheet (bh-print1-css) hides the rack chrome and a JS-created fixed DOWNLOAD PDF button (#bh-printbtn) calls window.print(). The resume now prints clean straight from the browser
- Risk: v1 failed on the host with the two-file pattern (PHP + separate payload file) - v1's payload never landed and the worker correctly refused. v2 embedded the payload via nowdoc
- Verify: served resume carries both markers; print preview clean; worker self-deleted
- Tag: `.bak-bhprint1.bak`

## 2026-11-17 - bhbayops1 (bryanhamilton.info)
- Change: LED Bay ops block - two status LEDs (BH = this host, DJC = fed browser-direct via /top10.php because Cloudflare blocks server-to-server) and a 30-day deploys-per-day sparkline parsed from this very LOG.md on raw.githubusercontent. Marker root #bh-bayops-root, style bh-bayops-css
- Risk: v1 was also a two-file casualty; v2 embedded. DJC's LED rides the browser-direct fallback chain - a server-side fetch would have been DOA per the Nov 14 Cloudflare block
- Verify: served homepage carries the marker root, LEDs and spark render; worker self-deleted
- Tag: `.bak-bhbayops1.bak`

## 2026-11-17 - bhstyle1 (bryanhamilton.info)
- Change: the 10 new pages from this batch (/uses/ + 9 project pages) rendered unstyled - /style.css targets rack-specific classes bare markup never gets. bhstyle1 embedded the bh-page1-css studio-rack stylesheet into all 10 directly
- Risk: the batch's rule violation (two-file workers) hit THREE workers before it was caught - sweep every worker in a batch for a rule the turn it breaks, not just the one that failed. Style target list re-checked for slug case before shipping
- Verify: "to patch: 10, already styled: 0 / OK - 10 files styled"; bh-page1-css confirmed in served raw bytes of /uses/ and /projects/og-card/
- Tag: `.bak-bhstyle1.bak` x10

## 2026-11-17 - bhdepfix3 (bryanhamilton.info)
- Change: the deploys strip finished - entries are 1 column / 2 rows (title line, then full description beneath) with the legacy flex-row overridden via !important, li padding 25px, and the strip now shows only the last 3 deploys with a SHOW ALL N DEPLOYS ghost button underneath that expands the full log. v3 swapped the v2 payload in place, marker-anchored on the block bhdepfix2 itself injected
- Risk: patching my own injected block meant trusting an earlier worker's output - the swap is needle-counted and auto-restores on verify failure. Also: a self-closing comment marker inside the PHP docblock terminated it early (php -l caught it)
- Verify: live page shows 2-row entries at 25px padding, last 3 + expand button, SHOW ALL renders all 33 entries
- Tag: `.bak-bhdepfix3.bak`

## 2026-11-17 - bhartcap1 (bryanhamilton.info)
- Change: homepage article list capped back to 10 visible - the cap had drifted to 11 (Scheduled Jobs With Zero Crontab was the 11th). The card was structurally cut (href-anchored <a>..</a> walk - formatting-proof, no raw-source fetch needed behind the LiteSpeed challenge) and re-inserted as the first card in the articles expander; summary updated to "Show 17 more: 13 articles + 4 cross-posts" and "+ 14 more deep-dives"
- Risk: needle patches against browser-DOM-derived markup - avoided entirely by cutting on structure (slug -> strrpos '<a class="card"' -> strpos '</a>'), which survives any whitespace/attribute difference between disk and rendered DOM
- Verify: live page shows exactly 10 visible cards, Scheduled Jobs intact as first hidden card, counts honest; homepage now surfaces all 27 /writing/ articles (10 + 13 local + 4 dev.to cross-posts)
- Tag: `.bak-bhartcap1.bak`

## 2026-11-17 - bhdepfix2 (bryanhamilton.info)
- Change: the "Recent deploys" strip under the LED Bay restyled - each entry now renders title-first ("date · workers · site" in cyan mono) with the full Change description underneath in subdued gray, instead of one crushed line. v1 failed on the host: a two-file worker (PHP + separate .block payload) silently injected 0 bytes when the payload file never landed - the verify-marker refused success and the byte-identical write did no harm. v2 embedded the payload in the PHP via nowdoc; renders via an additive script before </body> that re-parses LOG.md from raw.githubusercontent, plus a MutationObserver that overwrites the old renderer's mashed output the moment it lands
- Risk: shipping a worker that reads a second file from the host treats the upload step as reliable - it is not. Single-file workers only; payloads embedded, never referenced
- Verify: cache-busted live homepage shows title + full desc for every entry, old mashed format gone; worker self-deleted
- Tag: `.bak-bhdepfix2.bak`

## 2026-11-17 - bhres1 + bhwrt24 (bryanhamilton.info)
- Change: the resume push. bhres1 refreshed the stale tech resume bryan-hamilton-tech.html (frozen at Oct 12): bronze 28->33, +3 repo blocks (preview-media-session, og-card, deploy-log), a "Proof of Work" section linking the receipts ledger + stats.php + four engineering write-ups, ProfilePage JSON-LD, sitemap lastmod, IndexNow, receipts row. bhwrt24 published article #27 "Three Bugs in a Trenchcoat" - the favorites sync saga (capture-phase click hijack, missing CSRF meta + silent .catch 403, two sources of truth, add-only tombstone-aware merge) - via the full 9-surface publish: on-server template clone, hub card + count 26->27 + BlogPosting entry, homepage card before the CURRENT top card, llms.txt + ai.txt mirror, sitemap, rss, og-manifest, IndexNow
- Risk: the article rehearsal caught the prose splice consuming the <article class="prose"> opening tag itself - the exact "lost prose wrapper" bug from the Nov 14 saga. Fix: splice INSIDE the wrapper (tagEnd = strpos('>')+1). Also: a missing worker and a wrong token render the IDENTICAL custom 404, so "doesn't work" needed a state probe before a diagnosis - the first run simply never landed on the host
- Verify: resume carries 33 bronze + proof section + JSON-LD live; article 200 with h1 x1 / prose x1 / zero template residue, og.php renders the card PNG, hub at 27 articles, home card absolute href, llms at all 27, sitemap/rss/manifest patched, receipts row live
- Tag: `.bak-bhwrt24.bak` x7 (kept pending eyeball); bhres1 self-cleaned

## 2026-11-17 - bhmedias1 + bhart1 + bheqv5 + bhclfix1 (bryanhamilton.info)
- Change: EQ dock player day - bhmedias1 wired Media Session metadata into bh-eq.js (title/artist/album + 600x600 art on play, cleared on stop); bhart1 added the 40px album-art thumbnail to the dock (200x200 source, clears on stop, reduced-motion safe); bhclfix1 purged the CL proxy cache (60-min stale scrape was serving bronze 32) and synced the static chip; bheqv5 bumped the loader refs the first two workers missed
- Risk: the version-bump passes swept the HTML files - but the bh-eq.js ref actually lives inside rack.js, so patched content shipped behind a stale ?v=3 ref and cached browsers would have kept the old player forever. Rule: find where an asset is REFERENCED on the live site, not where you assume it is. bheqv5 fixed it (bh-eq.js v3->v5 in rack.js, rack.js v46->v47 in HTML)
- Verify: served bh-eq.js carries bh-medias-v1 + bh-eqart-set; served rack.js refs v5; bh-cl-live.php returns gold 1 / silver 5 / bronze 33; homepage aria-label matches. All four workers self-deleted
- Tag: `.bak-eqv5.bak` (self-cleaned); baks from medias1/art1/clfix1 self-cleaned

## 2026-11-17 - djcmedias1 + bhclprobe1 (thedjcalendar.com / bryanhamilton.info)
- Change: Media Session metadata for the search hover player - title, artist and 600x600 artwork land in navigator.mediaSession.metadata on play, cleared on stop, so Apple's media popover shows the track instead of the page title; shp v8->v9 across 11 root templates. Read-only probe on BH dumped bh-cl-live.php to diagnose the CL bronze chip showing 32 while the profile says 33
- Risk: needles cut from the browser-rendered page never matched disk - the browser decodes &middot; entities, so the raw bytes differ. All needles re-cut from raw served bytes fetched in-page through the JS challenge. The CL ghost was the proxy's own 60-min cache serving a pre-badge scrape: the scraper was right all along
- Verify: served shp v9 carries the marker; the exact served patch block executed in a real browser against an iTunes-shaped track returned title/artist/art; endpoint cache purged for a fresh scrape
- Tag: `.bak-medias1.bak` (self-cleaned); probe self-deleted

## 2026-11-16 - favorites + hover-player saga (thedjcalendar.com)
- Change: favorites finally sync end-to-end - three stacked bugs: a legacy capture-handler hijacking heart clicks, a missing CSRF meta silently 403-ing the account save, and SAVE never reverse-syncing; app-main.js v40. New djcMergeFavs() pulls starredDJs + server favorites into the legacy FAVORITES section (add-only, tombstone-aware) at load + 1.2s retry
- Risk: the silent .catch plus a local-merge fallback masked the dead POST for weeks - hearts lit and the UI echoed success while the server never heard anything. Verify server state, never the UI echo
- Verify: hearts <-> /account sync both directions, tombstones survive reload; Bryan-confirmed

## 2026-11-16 - djchover1-8 + djctickhover1 + djcshpback1 (thedjcalendar.com)
- Change: hover player shipped - hovering a search result or NEXT UP ticker name plays that artist's top iTunes preview in a right-side panel: 250ms hover-intent, no restart on child hovers, dismiss via click-outside, ESC and Backspace (key+keyCode 8); genre.php duplicate template chunk handled; /search-hover-player.js standalone, tagged after app-main.js
- Risk: eight workers to get it right - dead worker files djchover1-8.php left on the host pending cleanup; the stickySearchRow gate initially bypassed ticker items until .djc-hero-item selectors joined the shp match set
- Verify: playback, all dismissal paths and dock hover production-checked; shp glob bumped every pass

## 2026-11-16 - djcslugfix2+3 + djcintro1 + follow + predcards (thedjcalendar.com)
- Change: 59 mangled city slugs corrected with canonical matching + 301 redirects (a city 404 now just means no djs.json shows); 647 artist intros filled; follow alerts shipped (/follow-btn.js via header.php, append-only into the existing alert_subscribers pipeline); prediction share cards with og:image on /tour-predictions/ (RANK badge default ON, conf pill removed, fonts 24/34/17/15)
- Risk: slug "fixes" that re-derive slugs from names mangle non-ASCII - match canonical slugs from the existing data instead
- Verify: city pages resolve, intros render, follow button feeds the pipeline, cards "look perfect" per Bryan

## 2026-11-16 - timeline + receipts expansion (bryanhamilton.info)
- Change: /timeline/ shipped (73 nodes, full SAGA, og.php?a=timeline); /receipts/ chip count 45->73; card tilt kept, scramble text effect removed per Bryan; mobile burger drawer staged (bh-mobnav1: touch hover hints, QR hidden, boot-leak fix)
- Risk: project/page counts live in page text, JSON-LD, llms.txt and ai.txt - a count change that syncs only one surface lies to the crawlers
- Verify: timeline renders 73 nodes, receipts at 73 rows, SAGA endpoints live

## 2026-11-15 - bhclfix4+5 + bhmed1 + bhart10 + wrt22 (bryanhamilton.info)
- Change: CL feed rewritten - coderlegion.php scrapes the profile + per-post API with an X-API-Key (firehose pagination throttles the server IP; never revisit); rack.js v44 floors 14 articles/29 reactions; medal dots recomputed gold 1 / silver 3 / bronze 28 riding the .bh-cnt count-up animation, so a stale value animates confidently to the wrong thing
- Verify: CL feed live at 14/29, chip correct, article live

## 2026-11-14 - djcsbui0 + djcsbui1 (thedjcalendar.com)
- Change: prediction-scoreboard UI upgrade. A read-only probe dumped the 13,199-byte source first (never patch what you haven't seen); then a 5-needle worker added: FAQPage JSON-LD (3 questions - grading cadence, hit definition, misses are public), a countdown chip ("FIRST GRADES LAND FEB 1, 2027 - IN N DAYS", PHP-computed, self-retires the moment gradedN > 0), a "The road ahead" month-arc visualization (pending predictions grouped by month, count + avg confidence + opacity-scaled bars, ksort'd Jan 2027 -> Apr 2028), and band-colored confidence pills (vhigh/high/med/low) on all 61 pending rows
- Risk: rehearsal on a sqlite stand-in (apt php-sqlite3) caught three of MY bugs before the server saw any of them: the patch-marker verify failed because no patch introduced the marker; `$g['conf']` inside a double-quoted PHP string interpolated fatally; and a literal backslash-n inside the injected CSS string killed the `.arc` flex rule silently (computed display stayed block). All fixed pre-handoff; final sim rendered the flex arc, chip (ceil for calendar days), and 12 seeded pills with zero PHP warnings
- Verify: server run matched sim byte-for-byte (16,587); cache-busted live page: 1 FAQPage LD block / 3 entities parses, chip IN 79 DAYS, arc CSS + section, 61 pills; worker + probe self-deleted (404). DJC 403s the whole .bak extension pattern server-wide - a 403 there is a security rule, not a leak
- Tag: `.bak-djcsbui1.bak` (self-cleaned)

## 2026-11-14 - bhrack18 (bryanhamilton.info)
- Change: repo #18 browser-direct-feed published (fc6b96b) + rack card #17 inside the expander ("Show 9 more projects" -> "Show 10 more projects"); counts 17->18 across 15 needles (copy, FAQ x3, "published..." lines x3, meta list, units readout, boot line, JSON-LD numberOfItems + ListItem 17, llms/ai.txt +1 entry each). Rack columns now even at 18
- Risk: the JSON-LD ItemList runs 17 repo entries under numberOfItems 18 BY DESIGN - the rack's the-dj-calendar site card is a product, not SoftwareSourceCode, so it is excluded. Do not "fix" this mismatch
- Verify: 20/20 cache-busted checks PASS (card idx 17, zero 17-count stragglers, JSON-LD parses, position 17 = browser-direct-feed, llms/ai synced); worker self-deleted, baks cleaned
- Tag: `.bak-bhrack18.bak` (self-cleaned)

## 2026-11-14 - bhhboot1 + bhhupd1 (bryanhamilton.info)
- Change: boot terminal line "mounting 15 projects" -> 17; hero count-up line "updating now" -> "updated now". Both one-needle index.html patches
- Risk: count strings hide in many spots (boot lines array, hero line, JSON-LD, FAQ x3, llms/ai.txt) - each found on live served source, never from browser DOM
- Verify: cache-busted served HTML carries both strings; workers self-deleted
- Tag: `.bak-bhhboot1.bak` / `.bak-bhhupd1.bak` (self-cleaned)

## 2026-11-14 - djctop10w + djctop10v2 (thedjcalendar.com)
- Change: new `/top10.php` - slim CORS JSON (top-10, ~2KB vs 1.3MB djs.json), `Access-Control-Allow-Origin: *`, 30-min cache. v2 fixed an off-by-one: `djs` must stay a 1-based OBJECT (items[0] is the label) - v1's `array_values()` re-indexed to 0-based and every marquee hover pointed one artist off
- Risk: replacing a feed endpoint without byte-replicating the original's JSON key shape - PHP json_encode turns [1..10]-keyed arrays into objects and 0-based into lists, and consumers index accordingly
- Verify: live endpoint 200 with CORS header; alignment check djs[i].name in items[i] for all 1..10
- Tag: none (new file + in-place rewrite, lint-gated)

## 2026-11-14 - bhtkfall1 + bhhchip2 (bryanhamilton.info)
- Change: THE CLOUDFLARE BLOCK. CF on thedjcalendar.com 403-challenges ALL server-side fetches from the BH host: streams, php-curl, AND shell curl all get the block page. Server-to-server BH->DJC is dead. Fix: browser-direct - rack.js v42 falls back to thedjcalendar.com/top10.php (CORS) when ticker.php serves empty djs; hero chip inline script fetches the same endpoint when bh-live-data.php returns zeros
- Risk: shipping a fix that still depends on server-side fetch would have been dead on arrival - a read-only 3-transport probe worker on the real host settled it first
- Verify: rack.js v42 live-verified, top10.php fallback chain checked end-to-end, probe self-deleted
- Tag: `.bak-bhtkfall1.bak` / `.bak-bhhchip2.bak` (self-cleaned)

## 2026-11-14 - bheggclose1 (bryanhamilton.info)
- Change: golden-egg modal close (X and backdrop) now removes the #bhegg-chip and sets a localStorage guard so it never re-appears; rack.js v40->41
- Risk: two close paths with slightly different code - prefix-free needle patch applied to both, verified count==2
- Verify: served rack.js carries the guard in both handlers; version bump live
- Tag: `.bak-bheggclose1.bak` x2 (self-cleaned)

## 2026-11-14 - bhrkonair (bryanhamilton.info)
- Change: ON AIR lamp (rk-onair span + red dot) removed from the chat header - the green avatar dot already says online. Span-only removal; rack.js already null-guards the lamp, commit fetch stays alive (serial popovers use it)
- Risk: yanking the span could null-ref the pulse block - checked the guard before shipping
- Verify: served header HTML zero rk-onair residue
- Tag: `.bak-bhrkonair.bak` (self-cleaned)

## 2026-11-14 - bhrack17 (bryanhamilton.info)
- Change: rack card #16 badge-proxy (inside the expander, "Show 9 more projects"), repo counts 15->17 across 11 needles (copy, FAQ x3, meta list, JSON-LD numberOfItems + ListItem 16 + dateModified), scoreboard copy "grades land Dec 1" -> "Feb 1, 2027", llms/ai.txt +3 entries (arcade, one-file-analytics, badge-proxy)
- Risk: v1 worker FATAL 500 on the server - the emitter doubled a `);` and it shipped without a real lint (sandbox had no PHP). v2: PHP CLI installed in sandbox (`sudo apt-get install -y php-cli`), php -l + full dress rehearsal before handoff. Standing rule now: EVERY worker gets php -l
- Verify: 14/14 cache-busted checks PASS; worker self-deleted, baks cleaned
- Tag: `.bak-bhrack17.bak` (self-cleaned)

## 2026-11-14 - bhclive1 (bryanhamilton.info)
- Change: Coder Legion medal dots went LIVE - new same-origin endpoint `bh-cl-live.php` (server-side fetch of the CL badges page, tier counts sit in `id="hmb-Gold/Silver/Bronze" data-all="N"`, 60-min cache, stale-serve fallback) plus an injected fetch block that updates the `.cl-num-*` count-ups and the `.cl-medals` aria-label on load. Medals auto-sync now; no more hand patches when a badge lands
- Risk: fragile profile scraping - avoided by keying the parser on the machine-readable `data-all` attributes, never on layout
- Verify: endpoint returns `{"gold":1,"silver":3,"bronze":27}` live; served HTML carries the injected block
- Tag: `.bak-bhclive1.bak`

## 2026-11-14 - bhwplate2 + bhwplate3 (bryanhamilton.info)
- Change: writing-article plates tightened (margin `0 0 18px` -> `-8px 0 8px`, scoped to each article's own style block) - then removed entirely hours later: style block + plate div cut from all 21 articles, zero residue, headline intact. Full ship-tweak-delete cycle inside one day
- Risk: multi-file sweeps - every file guarded individually (exact-once style marker, plate div adjacent to style close, residue abort, h1 check) with per-file skip reporting so one odd file cannot block or corrupt the rest
- Verify: served articles grep zero `bh-wrtplate`, headlines render; CSS recoverable from baks if the plate ever returns
- Tag: `.bak-bhwplate2.bak` / `.bak-bhwplate3.bak` x21 each

## 2026-11-14 - bhpulseoff1 (bryanhamilton.info)
- Change: Site Pulse VU meter removed from the rack - the 2,519-byte self-contained IIFE cut between exact comment markers, nothing else touched; `pulse.php` now has zero callers
- Risk: global first-match replace was UNSAFE here (the file holds other `right` declarations) - the cut is marker-anchored (start marker + next module's comment, each count==1) with brace-delta reconciliation before write and residue checks after
- Verify: served rack.js 45,802 bytes with zero bhpulse1/bh-vu/pulse.php residue; rack.js v40 in both HTML refs
- Tag: `.bak-bhpulseoff1.bak` x3

## 2026-11-14 - bheggchip3 (bryanhamilton.info)
- Change: golden-egg chip moved - `right:18px` -> `100px` in the chip's injected CSS inside rack.js, version bumped v38->v39 in both HTML files
- Risk: rack.js lives at `assets/js/`, not the web root (a root-path dry run DRY-FAILed and a recursive locator probe found the real path); the replace is window-scoped to 500 chars after a `bhegg-chip` reference so unrelated `right:18px` declarations survive
- Verify: served rack.js carries `right:100px;bottom:56px`, `right:18px` remaining=0, v39 refs live
- Tag: `.bak-bheggchip3.bak` x3

## 2026-11-11 - bhglow1 (bryanhamilton.info)
- Change: `/stats.php` glow-up - neon pulse on the cockpit title, bars grow from 0 with a cyan sheen sweep, PARSED LINES + TODAY count up from zero, cards and rows lift on hover
- Risk: appended block must not touch the log-parsing PHP - shipped as one needle inject (`</body>`, count 1), pure CSS/JS, `prefers-reduced-motion` renders it all static
- Verify: Playwright on live page - count-ups landed exactly (37,729 / 275), zero JS errors; 8,650 -> 12,393 bytes
- Tag: `.bak-bhglow1.bak`

## 2026-11-11 - bhdeploylog1 (bryanhamilton.info)
- Change: LED Bay gained a "Recent deploys" strip that reads LOG.md straight from raw.githubusercontent - the site now shows what this log shows, auto-updated per commit
- Risk: injected script reaching into the rack IIFE fails silently - block shipped fully self-contained (own fetch, own render, own cleanup); parser dry-ran against the real LOG.md before shipping
- Verify: cache-busted served HTML shows #bh-deploys + #bh-deploy-list; 307,106 -> 310,085 bytes
- Tag: `.bak-deploylog1.bak`

## 2026-11-11 - bhstats1 (bryanhamilton.info)
- Change: `/stats.php` cockpit dashboard - parses gzipped cPanel access logs, 10-min JSON cache, no third-party analytics
- Risk: `filesize()` on `.gz` returns the COMPRESSED size - slicing the decompressed buffer by it silently chops the freshest log lines (13-line loss, zero warnings)
- Verify: mock render of the page caught an orphaned `fclose` fatal that `php -l` passed clean - lint is not a render test
- Tag: `.bak-bhstats1.bak`

## 2026-11-11 - djcsmap1 (thedjcalendar.com)
- Change: `/site-map/` gained a "Data & tools" branch (miles, trending, directory, tour predictions, scoreboard, widget, newsletter)
- Risk: editing the wrong file - the tree lives in `_inc/html_sitemap.php`, NOT seo.php
- Verify: served sitemap HTML + IndexNow ping 200
- Tag: `.bak-djcsmap1.bak`

## 2026-11-11 - djcsexy1-5 (thedjcalendar.com)
- Change: hero NEXT UP marquee (14 soonest shows), rank chip, TOP-100 count-up, card-hover spacing; strip pinned to hero-section bottom
- Risk: patching versioned assets without a version-bump pass - HTML kept referencing old css/js; LiteSpeed also served stale HTML ~90s post-write
- Verify: served HTML shows css v26 / js v31, cache-busted
- Tag: `.bak-djcsexy*.bak`

## 2026-11-11 - bhhsexy1-3 (bryanhamilton.info)
- Change: hero live chip counts up via new same-origin proxy `bh-live-data.php` (10-min cache; source JSON has no CORS headers), flame badge links to Coder Legion post, footer order swapped
- Risk: fetching cross-origin JSON client-side dies silently - server-side proxy was the fix, not a CORS flag we don't control
- Verify: served HTML 306,953 bytes (was 302,704), chip centered
- Tag: `.bak-bhhsexy*.bak`

## 2026-11-11 - bhwrt17 (bryanhamilton.info)
- Change: tour-alerts article backfilled to /writing/ with full SAGA (canonical, JSON-LD, llms.txt entry, sitemap, IndexNow)
- Risk: raw-file needle validation + HTML entity handling - needles rehearsed against served source, not local copies
- Verify: article live, hub card + homepage card + llms 18->19 + ai.txt mirror all confirmed
- Tag: `.bak-bhwrt17.bak`

## 2026-11-11 - bhpwrhold1 + bhclbadge1 (bryanhamilton.info)
- Change: rack power button holds black ~3s before re-init (500->3500ms); Coder Legion badge chip corrected to "15 bronze badges"
- Risk: boot animation on every visit becomes a toll - gated once per session via sessionStorage
- Verify: rack.js v34 served; chip text confirmed
- Tag: `.bak-bhpwrhold1.bak`

## 2026-11-10 - bh-eqtrunc1 + bh-eqcolor1 + bh-eqhint1 (bryanhamilton.info)
- Change: EQ dock readability - title/artist ellipsis truncation (34/22 chars), bars recolored to cyan glow, one-shot ~6s hint gated by sessionStorage
- Risk: injected hint reaching into the main IIFE fails silently - block made fully self-contained (own querySelectors, own cleanup)
- Verify: rack.js v29 served; hint shows once then never again
- Tag: `.bak-eq*.bak`

## 2026-11-10 - bhwrt16 (bryanhamilton.info)
- Change: article #19 published to /writing/ - hub card, homepage card, JSON-LD, llms.txt (18->19), ai.txt mirror, sitemap, OG manifest
- Risk: every article MUST ship an OG image (og.php?a=slug, 1200x630) - Facebook previews die without it
- Verify: IndexNow 200; og endpoint hit live for the slug
- Tag: `.bak-bhwrt16.bak`

## 2026-11-09 - portfolio rack: ticker drawer + top-10 (bryanhamilton.info)
- Change: ticker drawer (photo/date/link from JSON, hover grace-close) and a top-10 musicians strip built from JSON-LD slugs
- Risk: slugs are NOT name-derived - name-derived slugs mangled non-ASCII; decode `\uXXXX` + uppercase instead
- Verify: 10/10 cards render image + date; drawer opens/closes
- Tag: `.bak-bhtop10b.bak` etc.

## 2026-11-08 - djcliv1 + djcexq1 (thedjcalendar.com)
- Change: footer gained Tour Miles + Trending links; llms.txt core + extra questions updated
- Risk: llms.txt is a DYNAMIC rewrite to llms.php - patching the file on disk does nothing
- Verify: served footer + fetched llms.txt
- Tag: `.bak-djcliv1.bak`
