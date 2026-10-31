# 31 Oct 2026 — Corbett blog live fares ≠ admin fares

## Symptom
`/blog/dehradun-to-jim-corbett-taxi` (post #24) showed Sedan Rs 7,150 / Ertiga 8,850 /
Crysta 10,700, and every safari figure that came right after "Rs" was scaled the same way.
Admin and Rate Manager both said 5,000 / 6,200 / 7,500. The generated file
`blog/dehradun-to-jim-corbett-taxi.php` on the server was correct.

## Root cause
`includes/header.php` calls `anaya_route_sync_start()` (`includes/route-sync.php`) on
**every** page. That filter picks its page by `basename($_SERVER['SCRIPT_NAME'], '.php')`,
so the blog post `/blog/dehradun-to-jim-corbett-taxi.php` matched the route-page row
`'dehradun-to-jim-corbett-taxi' => array(3500, 240)`.

Rate Manager `r_jim_corbett` = 5000 ≠ authored 3500, so an output buffer rescaled every
`Rs/₹/INR` amount between 1,750 and 14,000 by 5000/3500 (×1.4286), rounded to 50:
5000→7150, 6200→8850, 7500→10700, 7000→10000, 9500→13550. Amounts outside that range
(1,500) and the second number of a range ("–9,300", no "Rs" in front) were left alone.
That explains the "first figure only" pattern.

The same row also broke the root route page `/dehradun-to-jim-corbett-taxi`: title
"Cabs ₹7,150", and safari "₹6,500–₹9,500" shown as "₹9,300–₹13,550". That page already
gets its fares from `{{rate:}}` / `{{km:}}` tokens, so the row was stale.

## Fix (`includes/route-sync.php`, deployed 31 Oct 2026)
1. The filter only runs for root-level scripts (`dirname(SCRIPT_NAME) === '/'`), so
   blog posts never get rewritten, even when their slug matches a route page.
2. Removed the stale `dehradun-to-jim-corbett-taxi` row (the page is fully tokenised,
   same as the Chandigarh row removed on 30 Oct).

Server backup of the previous version: `includes/route-sync.php.bak-20261031`.

Not changed: Rate Manager, rates.json, blog post #24, blog-render.php.
