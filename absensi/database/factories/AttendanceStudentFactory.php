<?php

namespace Database\Factories;

use App\Models\AttendanceStudent;
use App\Models\AttendanceClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceStudent>
 */
class AttendanceStudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttendanceStudent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate realistic Indonesian names
        $firstNames = ['Budi', 'Ani', 'Siti', 'Ahmad', 'Rina', 'Dewi', 'Eko', 'Sri', 'Agus', 'Lestari'];
        $lastNames = ['Santoso', 'Wijaya', 'Kusuma', 'Pratama', 'Putri', 'Saputra', 'Maharani', 'Nugraha', 'Wati', 'Prasetyo'];
        
        $firstName = fake()->randomElement($firstNames);
        $lastName = fake()->randomElement($lastNames);
        
        return [
            'nis' => fake()->unique()->numerify('24###'),
            'nama' => "{$firstName} {$lastName}",
            'kelas_id' => AttendanceClass::factory(),
            'no_hp_ortu' => '628' . fake()->numerify('#########'),
            'qr_code_path' => null,
            'foto_profil' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the student is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the student has a QR code.
     */
    public function withQRCode(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'qr_code_path' => 'attendance/qrcodes/' . $attributes['nis'] . '.svg',
            ];
        });
    }

    /**
     * Indicate that the student has a profile photo.
     */
    public function withPhoto(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'foto_profil' => 'attendance/profiles/' . $attributes['nis'] . '.jpg',
            ];
        });
    }
}
