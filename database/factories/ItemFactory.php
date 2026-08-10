<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_category_id' => \App\Models\ItemCategory::inRandomOrder()->first()?->id ?? 1,
            'code' => strtoupper($this->faker->unique()->bothify('ITM-#####')),
            'name' => $this->faker->words(2, true),
            'weight' => $this->faker->randomFloat(2, 0.5, 50.0),
        ];
    }
}
