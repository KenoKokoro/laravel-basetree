<?php


namespace BaseTree\Tests\Fake\Wrappers;


use BaseTree\Eloquent\ConstraintsMutator;
use Illuminate\Database\Query\Expression;

class ConstraintsMutatorTestWrapper extends ConstraintsMutator
{
    public function testRaw($column, $operator, $value): Expression
    {
        return parent::raw($column, $operator, $value);
    }

    public function testExplode(string $constraint): array
    {
        return parent::explode($constraint);
    }

    public function testIsColumn(string $value): bool
    {
        return parent::isColumn($value);
    }
}