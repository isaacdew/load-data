<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return new class extends Factory
        {
            public function definition()
            {
                return [
                ];
            }

            public function modelName()
            {
                return TestModel::class;
            }
        };
    }
}
