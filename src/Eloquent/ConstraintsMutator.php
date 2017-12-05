<?php


namespace BaseTree\Eloquent;


use Illuminate\Support\Facades\DB;

class ConstraintsMutator
{
    /**
     * @var array
     */
    private $constraints;

    protected $empty = true;

    protected $queries = [];

    public function __construct(array $constraints)
    {
        $this->constraints = $constraints;
        $this->empty = empty($this->constraints);
        $this->execute();
    }

    public function execute()
    {
        foreach ($this->constraints as $constraint) {
            [$column, $operator, $value] = explode('|', $constraint);
            $this->queries[] = $this->appendQuery($column, $operator, $value);
        }
    }

    public function isEmpty()
    {
        return $this->empty;
    }

    public function queries()
    {
        return $this->queries;
    }

    private function appendQuery($column, $operator, $value)
    {
        if ($this->isColumn($value)) {
            return $this->raw($column, $operator, $value);
        }

        return [$column, $operator, $value];
    }

    private function isColumn($value)
    {
        return preg_match("/^\`\w+\`$/", $value);
    }

    private function raw($column, $operator, $value)
    {
        return DB::raw("{$column} {$operator} {$value}");
    }
}