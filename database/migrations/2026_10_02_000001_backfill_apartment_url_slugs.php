<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Public apartment URLs use url_slug. Fill missing values and replace
     * numeric or broken slugs with a readable slug from the apartment name.
     * A stored slug is kept when it already matches that name, or when the
     * readable slug still contains it (shorter editorial slugs).
     */
    public function up(): void
    {
        $rows = DB::table('vv_apartments')
            ->orderByRaw("case when status = 'active' then 0 else 1 end")
            ->orderBy('ID')
            ->get(['ID', 'name', 'display_name', 'url_slug']);

        $taken = [];

        foreach ($rows as $row) {
            $current = trim((string) $row->url_slug);
            $readable = Str::slug($row->display_name ?: $row->name) ?: 'apartment';
            $keep = $current !== ''
                && ! ctype_digit($current)
                && ! isset($taken[$current])
                && ($current === $readable || str_contains($readable, $current));

            if ($keep) {
                $taken[$current] = true;

                continue;
            }

            $candidate = $readable;
            $suffix = 2;

            while (isset($taken[$candidate])) {
                $candidate = $readable.'-'.$suffix;
                $suffix++;
            }

            if ($candidate !== $current) {
                DB::table('vv_apartments')->where('ID', $row->ID)->update([
                    'url_slug' => $candidate,
                ]);
            }

            $taken[$candidate] = true;
        }
    }

    public function down(): void
    {
        // Previous slug values are not stored.
    }
};
