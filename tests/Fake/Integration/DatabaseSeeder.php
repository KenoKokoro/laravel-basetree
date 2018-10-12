<?php


namespace BaseTree\Tests\Fake\Integration;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public static $isLaravel = false;

    public static $isLumen = false;

    public function run()
    {
        $class = '';

        if (static::$isLaravel === true) {
            $class = \Laravel\User::class;
        }

        if (static::$isLumen === true) {
            $class = \App\User::class;
        }

        create($class, 15);
    }
}