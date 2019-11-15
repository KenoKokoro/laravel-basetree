<?php


namespace BaseTree\Tests\Fake\Integration;


use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public static $isLaravel = true;

    public function run(): void
    {
        if (static::$isLaravel === true) {
            $class = User::class;
        }

        create($class, 15);
    }
}