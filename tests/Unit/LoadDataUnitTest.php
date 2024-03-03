<?php

namespace Tests\Unit;

use InvalidArgumentException;
use Isaacdew\LoadData\LoadData;
use Tests\TestCase;
use Workbench\App\Models\TestModel;

use function Orchestra\Testbench\workbench_path;

class LoadDataUnitTest extends TestCase
{
    public function test_builds_sql()
    {
        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table",
            LoadData::from('a-file.csv')->to('table')->toSql()
        );

        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table FIELDS TERMINATED BY '\"'",
            LoadData::from('a-file.csv')->to('table')->fieldsTerminatedBy('"')->toSql()
        );
    }

    public function test_it_throw_error_when_attempting_to_use_sets_without_columns()
    {
        $this->expectException(InvalidArgumentException::class);

        LoadData::from('file.csv')
            ->to('table')
            ->setColumn('column', 'DATE()')
            ->toSql();
    }

    public function test_it_handles_sets()
    {
        $load = LoadData::from('a-file.csv')
            ->to('table')
            ->columns([
                'column_1',
                'column_2',
            ])->setColumn('column_1', 'CURRENT_DATE()');

        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table (@column_1, column_2) SET column_1 = CURRENT_DATE()",
            $load->toSql()
        );
    }

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
}
