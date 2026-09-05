<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_renders_the_inertia_home_component(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Home')
                    ->has('seo.title')
                    ->has('seo.description')
            );
    }

    public function test_home_page_shares_institutional_data(): void
    {
        $this->get(route('home'))
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->where('institution.shortName', config('paulinas.short_name'))
                    ->where('institution.legalName', config('paulinas.legal_name'))
                    ->where('currency.code', 'CLP')
            );
    }
}
