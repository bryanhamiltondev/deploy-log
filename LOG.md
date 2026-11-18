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
- Change: CL feed rewritten - coderlegion.php scrapes the profile + per-post API with an X-API-Key (firehose pagination throttles the server IP; never revisit); rack.js v44 floors 14 articles/29 reactions; medal dots recomputed gold 1 / silver 3 / bronze 28 riding the .bh-cnt count-ups; homepage articles restructured top-10 + collapsed 16 (12 local + 4 dev.to); article #22 published to /writing/
- Risk: medal counts must be re-checked against the live CL profile before every patch - the numbers ride the count-up animation, so a stale value animates confidently to the wrong thing
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
