<?php


namespace BaseTree\Tests\Unit\Eloquent;


use BaseTree\Eloquent\ConstraintsMutator;
use BaseTree\Tests\Fake\Wrappers\ConstraintsMutatorTestWrapper;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConstraintsMutatorTest extends TestCase
{
    /** @test */
    public function is_empty_should_return_boolean()
    {
        $empty = new ConstraintsMutator([]);
        $this->assertTrue($empty->isEmpty());

        DB::shouldReceive('raw')->with("`column` = 'value'")->once()->andReturn(new Expression("`column` = 'value'"));
        $notEmpty = new ConstraintsMutator(['column|=|value']);
        $this->assertFalse($notEmpty->isEmpty());
    }

    /** @test */
    public function constraint_with_one_delimiter_should_add_equal_sign_as_operation()
    {
        $instance = new ConstraintsMutatorTestWrapper([]);
        [$column, $operation, $value] = $instance->testExplode('column|value');

        $this->assertEquals('column', $column);
        $this->assertEquals('=', $operation);
        $this->assertEquals('value', $value);
    }

    /** @test */
    public function constraints_with_two_delimiters_should_be_generated_with_the_given_data()
    {
        $instance = new ConstraintsMutatorTestWrapper([]);
        [$column, $operation, $value] = $instance->testExplode('column|=|value');

        $this->assertEquals('column', $column);
        $this->assertEquals('=', $operation);
        $this->assertEquals('value', $value);
    }

    /** @test */
    public function raw_should_return_database_expression()
    {
        $instance = new ConstraintsMutatorTestWrapper([]);
        DB::shouldReceive('raw')->with("`column` > 'value'")->andReturn(new Expression("`column` > 'value'"));

        $raw = $instance->testRaw('column', '>', 'value');

        $this->assertInstanceOf(Expression::class, $raw);
        $this->assertEquals("`column` > 'value'", $raw->getValue());
    }

    /** @test */
    public function queries_should_return_generated_raw_queries()
    {
        DB::shouldReceive('raw')->with("`column` = 'value'")->andReturn(new Expression("`column` = 'value'"));
        DB::shouldReceive('raw')->with("`column2` = `value2`")->andReturn(new Expression("`column2` = `value2`"));

        $instance = new ConstraintsMutator(['column|value', 'column2|=|`value2`']);
        $queries = $instance->queries();

        $this->assertCount(2, $queries);
    }

    /** @test */
    public function is_column_should_return_boolean()
    {
        $instance = new ConstraintsMutatorTestWrapper([]);
        $this->assertFalse($instance->testIsColumn('value'));
        $this->assertTrue($instance->testIsColumn('`value`'));
    }
}