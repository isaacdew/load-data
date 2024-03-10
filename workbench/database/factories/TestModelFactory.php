<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\TestModel;

class TestModelFactory extends Factory
{
    public function definition()
    {
        return [
            'column_one' => 'value',
            'column_two' => 'value',
        ];
    }

    public function modelName()
    {
        return TestModel::class;
    }
}
