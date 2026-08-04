<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Website>
 */
class WebsiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $websiteName = 'Website ' . fake()->unique()->company();
        $domain = fake()->unique()->domainName();

        return [
            'name' => $websiteName,
            'opd_name' => fake()->company(),
            'website_name' => $websiteName,
            'domain' => $domain,
            'subdomain' => null,
            'ip_server' => fake()->ipv4(),
            'hosting' => fake()->randomElement(['Diskominfo Data Center', 'Shared Hosting', 'VPS Cloud']),
            'cms' => fake()->randomElement(['WordPress', 'Joomla', 'CodeIgniter', 'Laravel']),
            'cms_version' => fake()->numerify('#.#.#'),
            'server_location' => fake()->randomElement(['Kubu Raya', 'Pontianak', 'Jakarta']),
            'admin_name' => fake()->name(),
            'admin_email' => fake()->safeEmail(),
            'admin_phone' => fake()->numerify('08##########'),
            'status' => 'safe',
            'notes' => null,
        ];
    }

    public function needsReview(): static
    {
        return $this->state(fn () => ['status' => 'needs_review']);
    }

    public function flagged(): static
    {
        return $this->state(fn () => ['status' => 'flagged']);
    }
}
