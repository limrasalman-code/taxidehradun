<?php
/**
 * Anaya Cab Service — ROUTE SYNC
 * Rate Manager (fare + km) -> hand-written route pages.
 *
 * Template-driven route pages already read rates.json / admin km directly.
 * A few hand-written pages carry authored fares and distances in their text.
 * For those, when the admin changes a fare or a distance, this filter rewrites
 * the authored numbers in the rendered page (title, description, schema, body,
 * FAQs). While admin values equal the authored values the filter does nothing
 * and the page is served untouched.
 *
 * Table: slug => [authored sedan fare (0 = do not sync fares), authored km]
 *
 * Only ROOT-level route pages are synced. Blog posts can share a slug with a
 * route page (e.g. /blog/dehradun-to-jim-corbett-taxi), and their fares are
 * already authored from the admin form, so they must never be rewritten.
 * (31 Oct 2026 fix: the Corbett blog showed 5000 -> 7150, x5000/3500.)
 */
if (!function_exists('anaya_route_sync_start')) {

    function anaya_sync_pages(): array
    {
        return array(
            'dehradun-to-mussoorie-taxi'   => array(1800, 35),
            'dehradun-to-rishikesh-taxi'   => array(1150, 45),
            'dehradun-to-agra-taxi'        => array(9000, 430),
            'dehradun-to-auli-taxi'        => array(8500, 300),
            /* dehradun-to-chandigarh-taxi removed 30 Oct 2026: the page now uses {{rate:}} / {{km:}} tokens everywhere */
            'dehradun-to-dhanaulti-taxi'   => array(2200, 60),
            /* dehradun-to-jim-corbett-taxi removed 31 Oct 2026: the page now uses {{rate:}} / {{km:}} tokens everywhere (stale 3500 row scaled every Rs figure x1.43) */
            'dehradun-to-joshimath-taxi'   => array(6500, 255),
            'dehradun-to-kainchi-dham-taxi' => array(5800, 309),
            /* 3rd value true = change only the sedan fare itself (other tiers stay as authored). km-only rows: fares not synced. */
            'dehradun-to-nainital-taxi'    => array(0, 280),
            'dehradun-to-chakrata-taxi'    => array(2800, 90),
            'dehradun-to-kasol-taxi'       => array(8500, 410),
            'dehradun-to-valley-of-flowers-taxi' => array(7500, 300),
            'dehradun-to-delhi-taxi'       => array(0, 250),
            'dehradun-to-badrinath-taxi'   => array(0, 335),
        );
    }

    function anaya_sync_fares(string $html, int $a, int $r, bool $exactOnly = false): string
    {
        if ($a <= 0 || $r <= 0 || $a === $r) {
            return $html;
        }

        $lo = $a * 0.5;
        $hi = $a * 4;

        $conv = function (string $raw) use ($a, $r, $lo, $hi, $exactOnly): string {
            $n = (int)str_replace(',', '', $raw);

            if ($n === $a) {
                $new = $r;
            } elseif (!$exactOnly && $n >= $lo && $n <= $hi) {
                $new = (int)(round($n * $r / $a / 50) * 50);
            } else {
                return $raw;
            }

            return (strpos($raw, ',') !== false) ? number_format($new) : (string)$new;
        };

        $out = preg_replace_callback(
            '/(₹|Rs\.?\s?|INR\s?)(\d{1,3}(?:,\d{3})+|\d{4,6})(?!\d)/u',
            function (array $m) use ($conv): string {
                return $m[1] . $conv($m[2]);
            },
            $html
        );
        if ($out !== null) {
            $html = $out;
        }

        $out = preg_replace_callback(
            '/(\d{1,3}(?:,\d{3})+|\d{4,6})(\s*rupees)/iu',
            function (array $m) use ($conv): string {
                return $conv($m[1]) . $m[2];
            },
            $html
        );
        if ($out !== null) {
            $html = $out;
        }

        $out = preg_replace_callback(
            '/("(?:price|lowPrice|highPrice)"\s*:\s*"?)(\d{4,6})(?!\d)/',
            function (array $m) use ($conv): string {
                return $m[1] . $conv($m[2]);
            },
            $html
        );

        return $out !== null ? $out : $html;
    }

    function anaya_sync_km(string $html, int $k0, int $k): string
    {
        if ($k0 <= 0 || $k <= 0 || $k0 === $k) {
            return $html;
        }

        $out = preg_replace(
            '/(?<![\d.,])' . $k0 . '((?:\s|&nbsp;)*km\b)/u',
            $k . '$1',
            $html
        );

        return $out !== null ? $out : $html;
    }

    function anaya_route_sync_start(): void
    {
        static $started = false;

        if ($started) {
            return;
        }

        $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($script));
        if ($dir !== '/' && $dir !== '.' && $dir !== '') {
            return; /* not a root route page (e.g. /blog/<slug>.php) */
        }

        $slug = basename($script, '.php');
        $pages = anaya_sync_pages();

        if (!isset($pages[$slug])) {
            return;
        }

        if (!function_exists('anaya_rate')) {
            $f = __DIR__ . '/rates.php';
            if (!is_file($f)) {
                return;
            }
            require_once $f;
        }

        $t = str_replace('-', '_', substr($slug, 12, -5));
        $a0 = (int)$pages[$slug][0];
        $k0 = (int)$pages[$slug][1];
        $exact = !empty($pages[$slug][2]);
        $r  = (int)anaya_rate('r_' . $t);
        $k  = (int)anaya_rate('k_' . $t);

        $fareOn = ($a0 > 0 && $r > 0 && $r !== $a0);
        $kmOn   = ($k0 > 0 && $k > 0 && $k !== $k0);

        if (!$fareOn && !$kmOn) {
            return;
        }

        $started = true;

        ob_start(function ($buf) use ($fareOn, $kmOn, $a0, $r, $k0, $k, $exact) {
            try {
                if ($fareOn) {
                    $buf = anaya_sync_fares($buf, $a0, $r, $exact);
                }
                if ($kmOn) {
                    $buf = anaya_sync_km($buf, $k0, $k);
                }
            } catch (\Throwable $e) {
                /* never break the page */
            }

            return $buf;
        });
    }
}