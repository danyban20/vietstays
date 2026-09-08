<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Support\LegacySqlParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BuildingSeeder extends Seeder
{
    /**
     * Import buildings from WordPress neighbourhood posts.
     */
    public function run(): void
    {
        $parser = LegacySqlParser::default();
        $posts = $parser->parseGyhPosts('neighbourhood');
        $postMeta = $parser->parseGyhPostmeta();

        // building_pricing_factors has a FK on buildings.id, which blocks a plain
        // TRUNCATE even with cascadeOnDelete() (TRUNCATE ignores ON DELETE rules).
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Building::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($posts as $postId => $post) {
            if ($post['post_status'] !== 'publish') {
                continue;
            }

            $meta = $postMeta[$postId] ?? [];

            $facilities = $this->normalizeIdList($parser->unserializeMeta($meta['facilities'] ?? null));
            $securityFeatures = $this->normalizeIdList($parser->unserializeMeta($meta['security_features'] ?? null));
            $buildingGallery = $this->normalizeGallery($parser->unserializeMeta($meta['building_gallery'] ?? null));

            $districtWpId = (int) $post['post_parent'];

            Building::query()->updateOrCreate(
                ['id' => $postId],
                [
                    'name' => $post['post_title'],
                    'slug' => $post['post_name'] ?: 'building-'.$postId,
                    'district_wp_id' => $districtWpId,
                    'district_id' => $districtWpId > 0 ? $districtWpId : null,
                    'facilities' => $facilities,
                    'security_features' => $securityFeatures,
                    'main_image' => $this->nullableString($meta['main_image'] ?? null),
                    'header_bg_image' => $this->nullableString($meta['header_section_bg_image'] ?? $meta['districts_header_section_bg_image'] ?? null),
                    'header_text' => $this->nullableString($meta['header_text'] ?? $meta['header_section_text'] ?? null),
                    'building_gallery' => $buildingGallery,
                    'status' => $post['post_status'] ?: 'publish',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            );
        }

        $this->command?->info('Imported '.count($posts).' buildings from legacy WordPress data.');
    }

    protected function normalizeIdList(mixed $value): ?array
    {
        if (! is_array($value) || $value === []) {
            return null;
        }

        return array_values(array_map(
            static fn ($id) => is_numeric($id) ? (int) $id : $id,
            $value,
        ));
    }

    protected function normalizeGallery(mixed $value): ?array
    {
        if (! is_array($value) || $value === []) {
            return null;
        }

        return array_values(array_map(static function ($item) {
            if (! is_array($item)) {
                return $item;
            }

            return [
                'id' => isset($item['id']) ? (int) $item['id'] : null,
                'label' => $item['label'] ?? null,
            ];
        }, $value));
    }

    protected function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
