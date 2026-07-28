<?php

namespace Database\Factories;

use App\Models\AttendanceClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceClass>
 */
class AttendanceClassFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttendanceClass::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tingkat = fake()->randomElement(['10', '11', '12']);
        $jurusan = fake()->randomElement(['RPL', 'AKL', 'MPLB', 'TKJ', 'BDP']);
        
        return [
            'nama_kelas' => "{$tingkat} {$jurusan} " . fake()->numberBetween(1, 3),
            'tingkat' => $tingkat,
            'jurusan' => $jurusan,
            'wali_kelas_id' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the class is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
