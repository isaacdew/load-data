<?php

namespace Tests\Unit;

use Isaacdew\LoadData\LoadData;
use Isaacdew\LoadData\LoadDataException;
use Tests\TestCase;

/**
 * @covers LoadData
 */
class LoadDataTest extends TestCase
{
    public function test_builds_basic_sql()
    {
        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table",
            LoadData::from('a-file.csv')->to('table')->useLocalKeyword(false)->toSql()
        );

        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table FIELDS TERMINATED BY '\"'",
            LoadData::from('a-file.csv')->to('table')->useLocalKeyword(false)->fieldsTerminatedBy('"')->toSql()
        );
    }

    public function test_it_throw_error_when_attempting_to_use_sets_without_column_definition()
    {
        $this->expectException(LoadDataException::class);

        LoadData::from('file.csv')
            ->to('table')
            ->setColumn('column', 'DATE()')
            ->toSql();
    }

    public function test_it_builds_set_statements()
    {
        $load = LoadData::from('a-file.csv')
            ->to('table')
            ->useLocalKeyword(false)
            ->columns([
                'column_1',
                'column_2',
            ])->setColumns([
                'column_1' => 'CURRENT_DATE()',
                'column_2' => 'CURRENT_DATE()',
            ]);

        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table (@column_1, @column_2) SET column_1 = CURRENT_DATE(), column_2 = CURRENT_DATE()",
            $load->toSql()
        );
    }

    public function test_it_builds_fields_clause()
    {
        $load = LoadData::from('a-file.csv')
            ->to('table')
            ->useLocalKeyword(false)
            ->fieldsTerminatedBy(',')
            ->fieldsEnclosedBy('"')
            ->fieldsEscapedBy('\\');

        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table FIELDS TERMINATED BY ',' ENCLOSED BY '\"' ESCAPED BY '\\'",
            $load->toSql()
        );

        // Let's change enclosed by to optionally
        $load
            ->fieldsEnclosedBy('"', optionally: true);

        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' ESCAPED BY '\\'",
            $load->toSql()
        );
    }

    public function test_it_builds_lines_clause()
    {
        $load = LoadData::from('a-file.csv')
            ->to('table')
            ->useLocalKeyword(false)
            ->linesStartingBy('"')
            ->linesTerminatedBy("\n");

        $this->assertEquals(
            "LOAD DATA INFILE 'a-file.csv' INTO TABLE table LINES STARTING BY '\"' TERMINATED BY '\n'",
            $load->toSql()
        );
    }
}
