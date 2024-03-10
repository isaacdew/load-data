<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Isaacdew\LoadData\LoadData;
use Mockery;
use Tests\TestCase;
use Workbench\App\Models\TestModel;

use function Orchestra\Testbench\workbench_path;

/**
 * @covers LoadData
 */
class LoadDataTest extends TestCase
{
    public function test_it_loads_file()
    {
        $file = workbench_path('storage/app/test.csv');

        $result = LoadData::from($file)
            ->to(TestModel::class)
            ->fieldsTerminatedBy(',')
            ->fieldsEnclosedBy('"', true)
            ->useFileHeaderForColumns()
            ->load();

        $this->assertTrue($result);

        $this->assertDatabaseHas('test_models', [
            'column_one' => 'value',
            'column_two' => 'value',
        ]);
    }

    public function test_it_loads_file_with_sets()
    {
        $file = workbench_path('storage/app/test.csv');

        $result = LoadData::from($file)
            ->to(TestModel::class)
            ->fieldsTerminatedBy(',')
            ->fieldsEnclosedBy('"', true)
            ->useFileHeaderForColumns()
            ->setColumn('column_one', "'a new string'")
            ->load();

        $this->assertTrue($result);

        $this->assertDatabaseHas('test_models', [
            'column_one' => 'a new string',
            'column_two' => 'value',
        ]);
    }

    public function test_it_truncates_before_load()
    {
        // Create test model records
        TestModel::factory(15)
            ->create();

        // Create load data instance with truncate before load
        $file = workbench_path('storage/app/test.csv');

        // Mock DB so that we can ignore the statement call and not actually load data
        $dbMock = Mockery::mock(DB::getFacadeRoot())
            ->makePartial()
            ->shouldReceive('statement')
            ->once()
            ->getMock();

        DB::swap($dbMock);

        LoadData::from($file)
            ->to(TestModel::class)
            ->fieldsTerminatedBy(',')
            ->fieldsEnclosedBy('"', true)
            // Call the truncate before load method
            ->truncateBeforeLoad()
            ->columns([
                'column_one',
                'column_two',
            ])
            ->load();

        // Assert that the table has no records
        $this->assertDatabaseEmpty('test_models');
    }

    public function test_it_ignores_columns_when_using_only_columns()
    {
        $file = workbench_path('storage/app/test.csv');

        $result = LoadData::from($file)
            ->to(TestModel::class)
            ->fieldsTerminatedBy(',')
            ->fieldsEnclosedBy('"', true)
            ->useFileHeaderForColumns()
            ->onlyLoadColumns([
                'column_two',
            ])
            ->load();

        $this->assertTrue($result);

        $this->assertDatabaseHas('test_models', [
            'column_one' => null,
            'column_two' => 'value',
        ]);
    }
}
