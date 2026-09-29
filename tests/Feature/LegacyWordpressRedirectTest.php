<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegacyWordpressRedirectTest extends TestCase
{
    public function test_wp_apartments_booking_search_redirects_to_vue_apartments(): void
    {
        $response = $this->get('/wp/apartments?city_id=449&vv_action=booking_search&rooms=2&adults=2&children=0&datefilter=');

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertTrue(
            str_ends_with((string) parse_url($location, PHP_URL_PATH), '/apartments'),
            $location,
        );

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('449', $query['city']);
        $this->assertSame('2', $query['rooms']);
        $this->assertSame('2', $query['adults']);
        $this->assertSame('0', $query['children']);
        $this->assertArrayNotHasKey('city_id', $query);
        $this->assertArrayNotHasKey('vv_action', $query);
        $this->assertArrayNotHasKey('datefilter', $query);
    }

    public function test_wp_apartments_datefilter_becomes_from_and_to(): void
    {
        $response = $this->get('/wp/apartments?city_id=449&datefilter='.rawurlencode('03/01/2026 - 03/05/2026'));

        $response->assertRedirect();

        parse_str((string) parse_url((string) $response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('2026-03-01', $query['from']);
        $this->assertSame('2026-03-05', $query['to']);
        $this->assertArrayNotHasKey('datefilter', $query);
    }
}
