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
        if ( ! is_array($value) and $this->isColumn($value)) {
            return DB::raw("`{$column}` {$operator} {$value}");
        }

        return DB::raw("`{$column}` {$operator} '{$value}'");
    }

    protected function explode(string $constraint): array
    {
        $arguments = explode('|', $constraint);
        $mapped = $this->mapArguments($arguments);

        if ($this->shouldBeArray($value = $mapped[2]) and (strtolower($operator = $mapped[1]) == 'in')) {
            $mapped[2] = $this->extractArrayFrom($value);
        }

        return $mapped;
    }

    protected function mapArguments(array $arguments): array
    {
        if (count($arguments) === 2) {
            return [$column = $arguments[0], '=', $value = $arguments[1]];
        }

        return [$column = $arguments[0], $operation = $arguments[1], $value = $arguments[2]];
    }

    protected function shouldBeArray(string $value): bool
    {
        return ! ! preg_match("/^\[.*\]$/", $value);
    }

    protected function extractArrayFrom(string $value): array
    {
        return explode(',', substr($value, 1, -1));
    }

    protected function isColumn(string $value): bool
    {
        return ! ! preg_match("/^\`.+\`$/", $value);
    }
}