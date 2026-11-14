# Deploy Log

One entry per production deploy. Tokens stripped; sites are public.

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
