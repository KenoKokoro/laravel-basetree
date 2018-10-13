<?php


namespace BaseTree\Tests\Fake\Integration;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public static $isLaravel = true;

    public function run(): void
    {
        $class = \App\User::class;

        if (static::$isLaravel === true) {
            $class = \Laravel\User::class;
        }

        create($class, 15);
    }
}