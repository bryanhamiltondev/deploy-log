# Deploy Log

One entry per production deploy. Tokens stripped; sites are public.

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
