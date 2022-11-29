<?php

namespace Database\Factories;
use App\Models\Paper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Paper>
 */
class PaperFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Paper::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->text(),
            'keywords' => collect(['kafka', 'python', 'php', 'javascript', 'elasticsearch'])
                ->random(2)
                ->values()
                ->all(),
        ];
    }
}
