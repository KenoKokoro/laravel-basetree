<?php


namespace BaseTree\Eloquent;


use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class ConstraintsMutator
{
    /**
     * @var array
     */
    protected $constraints;

    protected $empty = true;

    protected $queries = [];

    public function __construct(array $constraints)
    {
        $this->constraints = $constraints;
        $this->empty = empty($this->constraints);
        $this->execute();
    }

    public function execute(): void
    {
        foreach ($this->constraints as $constraint) {
            [$column, $operator, $value] = $this->explode($constraint);
            $this->queries[] = $this->raw($column, $operator, $value);
        }
    }

    public function isEmpty(): bool
    {
        return $this->empty;
    }

    public function queries(): array
    {
        return $this->queries;
    }

    protected function raw($column, $operator, $value): Expression
    {
        # TODO: Inspect raw for security
        if ($this->isColumn($value)) {
            return DB::raw("`{$column}` {$operator} {$value}");
        }

        return DB::raw("`{$column}` {$operator} '{$value}'");
    }

    protected function explode(string $constraint): array
    {
        $arguments = explode('|', $constraint);

        if (count($arguments) === 2) {
            return [$column = $arguments[0], '=', $value = $arguments[1]];
        }

        return [$column = $arguments[0], $operation = $arguments[1], $value = $arguments[2]];
    }

    protected function isColumn(string $value): bool
    {
        return ! ! preg_match("/^\`.+\`$/", $value);
    }
}