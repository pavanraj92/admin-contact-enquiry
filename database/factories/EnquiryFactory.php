<?php

namespace admin\enquiries\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use admin\enquiries\Models\Enquiry;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\admin\enquiry\Models\Enquiry>
 */
class EnquiryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Enquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'message' => $this->faker->paragraph(3),
            'admin_reply' => null,
            'status' => $this->faker->randomElement(['new', 'draft', 'replied', 'closed']),
            'is_replied' => false,
            'replied_at' => null,
            'replied_by' => null,
        ];
    }

    /**
     * Indicate that the enquiry is new.
     */
    public function asNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
            'is_replied' => false,
            'replied_at' => null,
            'replied_by' => null,
        ]);
    }

    /**
     * Indicate that the enquiry is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'is_replied' => false,
            'replied_at' => null,
            'replied_by' => null,
        ]);
    }

    /**
     * Indicate that the enquiry has been replied to.
     */
    public function replied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'replied',
            'is_replied' => true,
            'replied_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'admin_reply' => $this->faker->paragraph(2),
        ]);
    }

    /**
     * Indicate that the enquiry is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'is_replied' => true,
            'replied_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'admin_reply' => $this->faker->paragraph(2),
        ]);
    }
}