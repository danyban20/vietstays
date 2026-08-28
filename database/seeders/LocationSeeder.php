<?php

namespace Database\Seeders;

use App\Support\LegacySqlParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Import cities and districts from WordPress custom post types (gyh_posts).
     *
     * WordPress uses post IDs as the canonical location identifiers referenced by
     * apartments (vv_apartments.district) and neighbourhoods (buildings).
     */
    public function run(): void
    {
        $parser = LegacySqlParser::default();
        $postMeta = $parser->parseGyhPostmeta();

        DB::table('vv_cities')->truncate();
        DB::table('vv_districts')->truncate();

        $cityCount = $this->seedCities($parser);
        $districtCount = $this->seedDistricts($parser, $postMeta);

        $this->command?->info("Imported {$cityCount} cities and {$districtCount} districts from WordPress.");
    }

    protected function seedCities(LegacySqlParser $parser): int
    {
        $count = 0;

        foreach ($parser->parseGyhPosts('city') as $postId => $post) {
            if ($post['post_status'] !== 'publish') {
                continue;
            }

            DB::table('vv_cities')->insert([
                'city_id' => $postId,
                'name' => $post['post_title'],
                'code' => $post['post_name'],
                'dateadded' => $this->toTimestamp($post['post_date']),
                'datemodified' => $this->toTimestamp($post['post_modified']),
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, string>>  $postMeta
     */
    protected function seedDistricts(LegacySqlParser $parser, array $postMeta): int
    {
        $count = 0;

        foreach ($parser->parseGyhPosts('district') as $postId => $post) {
            if ($post['post_status'] !== 'publish') {
                continue;
            }

            $cityId = (int) ($postMeta[$postId]['city'] ?? 0);

            DB::table('vv_districts')->insert([
                'district_id' => $postId,
                'district_num' => $post['post_name'],
                'name' => $post['post_title'],
                'city_id' => $cityId,
                'dateadded' => $this->toTimestamp($post['post_date']),
                'datemodified' => $this->toTimestamp($post['post_modified']),
            ]);

            $count++;
        }

        return $count;
    }

    protected function toTimestamp(?string $datetime): int
    {
        if ($datetime === null || $datetime === '' || str_starts_with($datetime, '0000-00-00')) {
            return Carbon::now()->timestamp;
        }

        return Carbon::parse($datetime)->timestamp;
    }
}
