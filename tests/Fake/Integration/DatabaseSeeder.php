<?php


namespace BaseTree\Tests\Fake\Integration;


use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        create(User::class, 15);
    }
}