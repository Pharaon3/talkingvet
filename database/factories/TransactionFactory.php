<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'review_id' => $this->faker->word(),
            'submit_time' => Carbon::now(),
            'real_user_name' => $this->faker->userName(),
            'audio_length' => $this->faker->randomFloat(),
            'word_count' => $this->faker->randomNumber(),
            'external_id' => $this->faker->word(),
            'audio_quality' => $this->faker->randomNumber(),
        ];
    }
}
