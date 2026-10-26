# taxidehradun.com — Full SEO Audit (26 Oct 2026)

**Business:** Anaya Cab Service — local taxi service (service-area business), Dehradun
**Scope:** 215 URLs from the XML sitemaps were crawled, plus a live mobile render using Playwright (Pixel 5).
**SEO Health Score: 81 / 100 (Good)**

| Category | Weight | Score |
|---|---|---|
| Technical SEO | 22% | 85 |
| Content Quality | 23% | 72 |
| On-Page SEO | 20% | 84 |
| Schema | 10% | 88 |
| Performance (CWV, lab) | 10% | 78 |
| AI Search Readiness | 10% | 90 |
| Images | 5% | 92 |

## What already works
- All 215 sitemap URLs return 200. Each has a self-referencing canonical, exactly one H1, and none is noindexed.
- HTTPS is in place with HSTS preload, CSP upgrade-insecure-requests, X-Frame-Options and nosniff.
- robots.txt is clean and blocks only the admin and utility paths. A sitemap index points to 3 child sitemaps, and every URL has a lastmod.
- The JSON-LD graph covers LocalBusiness+TaxiService (with geo, opening hours and areaServed), Organization, WebSite, BreadcrumbList on every page, FAQPage (210), Service (151), Trip (92) and BlogPosting (51). All of it is valid JSON.
- llms.txt and llms-full.txt are both live, and NAP is consistent (phone +918077611583).
- Images are WebP, 21 of 25 on the homepage are lazy-loaded, and static assets are cached for 1 year.
- Titles and meta descriptions have no duplicates across the site.

## Findings

### High
1. **The redirect chain for http://taxidehradun.com takes 2 hops**: http://taxidehradun.com → https://taxidehradun.com → https://www.taxidehradun.com. Every variant should 301 straight to `https://www.` in one hop (this is a Hostinger/.htaccess rule).
2. **Route pages have high layout shift.** /dehradun-to-mussoorie-taxi measured CLS 0.196 in the lab, and Google's "good" limit is <0.1. The banner image is declared 1240×300 but is styled at a different aspect ratio, and late-loading CSS/fonts make it worse. Add `aspect-ratio` to the CSS, reserve space for the booking widget, and use `font-display` with size-adjusted fallbacks.
3. **The homepage LCP element is a CSS background image** (`.td-hero-bg-layer` → /assets/images/hero-bg.webp, LCP 2.06 s in the lab). The browser only finds it after the CSS loads. Change it to an `<img fetchpriority="high">`, or add `<link rel="preload" as="image" href="/assets/images/hero-bg.webp" fetchpriority="high">`.
4. **Location pages are templated content.** The /taxi-service-in-* pages (20 of them) share about 43–44% of their 5-word shingles with each other. Add unique local proof to each one: landmarks, real pickup points, fares and times from that locality, a local photo, and locality-specific FAQs. Otherwise Google may treat them as doorway pages.
5. **Blog posts compete for the same keywords:**
   - Kedarkantha: `/blog/kedarkantha-trek-2026-27-dehradun-taxi-package-guide` and `/blog/kedarkantha-trek-2026-27-winter-season-dehradun-taxi`
   - Char Dham: 6 posts (planning-guide, taxi-complete-guide, taxi-itinerary, 2026-dates-fares, 2027-dates, helicopter-vs-taxi) plus /char-dham-yatra-taxi
   - Mussoorie: distance-taxi, taxi-fare-2026, best-way-to-reach, travel-guide-by-cab, plus the money page
   - Delhi: dehradun-to-delhi-taxi-time and travel-cost-taxi-fare-guide

   Merge or 301 the overlapping posts, and point each cluster at its money page with exact-match internal links.

### Medium
6. **7 pages are thin (fewer than 500 words of main content):** /ai-trip-planner (360), /reviews (362), /gallery (368), /blog/mussoorie-travel-guide-by-cab (462), /dehradun-to-agra-taxi (499), /blog/taxi-vs-bus-dehradun-to-delhi-comparison (502), /dehradun-to-chakrata-taxi (517). Expand them, or noindex the utility pages (trip planner, gallery).
7. **The /reviews page is thin and there is no review markup.** llms.txt says "4.9★, 22 reviews" but also "500+ happy customers". Keep these claims consistent everywhere. Embed real Google reviews with dates and names, and ask customers for more reviews (22 is low for this market). Do not add self-serving AggregateRating to LocalBusiness, because Google ignores it.
8. **27 titles are longer than 60 characters** and get truncated in the SERP. Examples: the haridwar-/rishikesh-/mussoorie-to-* and jolly-grant-airport-to-* pages, /taxi-service-in-dehradun (62) and /dehradun-to-nainital-taxi (62). Shorten them by dropping the " | Anaya Cab" suffix on those pages.
9. **Weak internal linking.** At least 15 pages have only 1 internal inlink (from the sitemap/hub only), for example /haridwar-to-chandigarh-taxi, /dehradun-to-naukuchiatal-taxi, /dehradun-to-pauri-taxi, /jolly-grant-airport-to-delhi-taxi, /delhi-to-lansdowne-taxi and several blog posts. Add "nearby routes" and "related routes" blocks, and link from blog posts to the routes they mention.
10. **Homepage H2/H3 headings are slider copy** ("Family Trips Made Comfortable.", "Book your cab" ×3). The H1 "Your Ride, Our Priority." contains no keyword. Make the H1 "Taxi Service in Dehradun — 24×7 Cab Booking", and change the slide headings to `<p>` or `<div>`.
11. **The H3s on service cards contain stray line breaks** ("Local\n\n\nTaxi"). Clean up the markup.
12. **The Google News SwG `publisher.js` script loads on every page.** It is meant for news subscription and adds a third-party request for no SEO benefit. Remove it.
13. **Font Awesome loads the full CSS (about 100 KB) from cdnjs** for a handful of icons. Use an inline SVG sprite instead.

### Low
14. 1 image on the homepage has no alt text, and 2 on /book.
15. The meta description on /ai-trip-planner is 162 characters, slightly too long.
16. Sitemaps: there are no image sitemap entries. Consider adding `<image:image>` entries for fleet and route images.
17. **Footer links to steelly.in and ultrawebx.com (with UTM parameters) appear sitewide.** Add `rel="nofollow"` or `rel="sponsored"`, or keep them on the homepage only.
18. **The BlogPosting markup could carry more E-E-A-T signals.** Make sure it has a named `author` (Person with a bio page), plus `dateModified` and an `image`. Add an author or about-the-driver/team section to show real experience.
19. **Pages with "2026" in the URL** (for example /blog/char-dham-yatra-2026-dates-fares) will go stale. Keep evergreen URLs and update the year inside the content, or 301 old years to the new ones.

## Local SEO (off-site, not measurable from the crawl)
- Optimise the GBP: set the primary category "Taxi service", add the services and fares, post weekly, add 20+ fleet photos, and reply to every review.
- Get citations on Justdial, Sulekha, IndiaMart, Bing Places and Apple Maps with the identical NAP.
- The address "Sewla Kalan Majra" should be written the same way everywhere.

## Limits of this audit
The PageSpeed/CrUX API quota was exhausted (HTTP 429), so the CWV figures are lab-only from Playwright and there is no field data. No Search Console, GA4 or backlink data was available.
