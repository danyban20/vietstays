<?php

namespace App\Support;

class LegacyMediaUrl
{
    /**
     * Rewrite legacy WordPress / vietstays.test media URLs to local /home/images paths.
     */
    public static function normalize(?string $src): ?string
    {
        if ($src === null || trim($src) === '') {
            return null;
        }

        $src = trim($src);

        if (str_starts_with($src, '/home/') || str_starts_with($src, '/storage/')) {
            return $src;
        }

        if (preg_match('#(?:https?://[^/]+)?/wp-content/uploads/(.+)$#i', $src, $matches)) {
            return '/home/images/uploads/'.ltrim($matches[1], '/');
        }

        if (preg_match('#(?:https?://[^/]+)?/wp-content/themes/visitvietnam/images/(.+)$#i', $src, $matches)) {
            return '/home/images/theme/'.ltrim($matches[1], '/');
        }

        if (str_starts_with($src, 'http') || str_starts_with($src, 'data:')) {
            return $src;
        }

        if (str_starts_with($src, '/')) {
            return $src;
        }

        return '/storage/'.ltrim($src, '/');
    }
}
