# Deploy Log

One entry per verified production deploy. Format spec in README.
Every entry: worker, date, target, change, verification method.

---

## bh-clnum5 - medal statics 2/10/50 -> 2/10/51 - 2026-11-24

- **Target:** homepage medal strip (aria-label, medal tooltip, bronze count-up)
- **Change:** Coder Legion medal counts ticked bronze 50->51. Patched all three
  surfaces. The count-up had drifted (data-to="50" but inner text "49" from an
  earlier partial patch) - so data-to AND stale text patched as ONE needle:
  `data-to="50">49<` -> `data-to="51">51<`. Verified OLD value gone (count 0),
  not just new value present - "new exists" passes on a half-patched page.
- **Verify:** cache-busted live fetch: aria/tooltip/count-up all 51, zero
  '50 bronze' residue. Receipts row +1. 366,823 B.
- **Lesson:** a count-up's data-to and its inner fallback text are one unit;
  and a receipts self-log row is NOT proof of a deploy - always re-fetch the
  patched surface and grep the bytes. Codified as rules 19-20 in
  guarded-deploy-workers docs/checklist.md.

## bh-cltypo1 - silver medal tooltip typo - 2026-11-24

- **Target:** homepage silver medal tooltip
- **Change:** "Avid Reader, 1,3 Club, Good Post" -> "Avid Reader, 1,000 Club,
  Good Post" (dropped zero in 1,000 from an earlier patch).
- **Verify:** cache-busted live fetch: 1,000 Club present, 1,3 Club count 0.
  Receipts row +1. 366,825 B.
- **Lesson:** verify typed numbers with a character-level needle, not an
  eyeball - the typo had survived two medal patches because it was adjacent
  to text everyone read as correct.

## bh-reflog1 - external-referrer logging stack - 2026-11-24

- **Target:** sitewide (rack.js v53->v54, 67 files version-bumped)
- **Change:** new self-contained beacon block in rack.js (marker bh-reflog1):
  on external-referrer page loads, fires an image beacon to new reflog.php,
  which logs `time|refhost|path|fullreferrer` to data/vpref-YYYYMMDD.log
  (bot-filtered UA, internal nav ignored - no view inflation, no px.php
  touch: that file has a wipe-to-0-bytes history). Read endpoint prints a
  per-host tally.
- **Verify:** live: rack.js v54 marker count 1, homepage refs v54, tally
  endpoint returns selftest row, worker self-deleted 404.
- **Lesson:** the analytics stack deliberately dropped referrers (GA-204
  article), so referral verdicts were vibes. A 60-line beacon closed the gap
  without touching the fragile px.php.
