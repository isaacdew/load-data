<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workbench\Database\Factories\TestModelFactory;

class TestModel extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return new TestModelFactory();
    }
}
