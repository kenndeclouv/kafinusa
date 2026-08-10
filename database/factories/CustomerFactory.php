<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'market_id' => \App\Models\Market::inRandomOrder()->first()?->id ?? 1,
            'customer_category_id' => \App\Models\CustomerCategory::inRandomOrder()->first()?->id ?? 1,
            'name' => $this->faker->name(),
            'status' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
}
