<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of User
 *
 * @extends Factory<TModel>
 */
class UserFactory extends \Orchestra\Testbench\Factories\UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = User::class;
}
