<?php

class Validator
{
    private $errors = [];

    private $inputs;

    private $inputRules;

    private $f3;

    /**
     * Attribute name casts for error messages
     *
     * @var array
     */
    private $attributeCasts = [];

    public function __construct(array $inputs, array $inputRules)
    {
        $this->inputs = $inputs;

        $this->inputRules = $inputRules;

        $this->f3 = Base::instance();
    }

    public function validate(): bool
    {
        foreach ($this->inputRules as $inputName => $rules) {
            foreach ($rules as $rule) {
                [$rule, $arguments] = explode(':', $rule);

                $arguments = explode(',', $arguments);

                $functionName = "check" . ucfirst($rule);

                if (!method_exists('Validator', $functionName)) {
                    $this->f3->error(422, "Invalid validation rule: $rule");
                } else {
                    call_user_func([$this, $functionName], $inputName, $arguments);
                }
            }
        }

        return empty($this->errors());
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Set the attribute casts
     * 
     * @param array $casts
     * @return void 
     */
    public function setAttributeCasts(array $casts): void
    {
        $this->attributeCasts = $casts;
    }

    private function sanitizeInputs(array $inputs): array
    {
        $sanitizedInputs = [];

        foreach ($inputs as $input) {
            $sanitizedInputs[] = trim(htmlspecialchars($input));
        }

        return $sanitizedInputs;
    }

    private function checkRequired(string $inputName): bool
    {
        $input = $this->inputs[$inputName];

        if (!is_null($input) && $input !== '') {
            return true;
        }

        $this->errors[$inputName] = "The " . ($this->attributeCasts[$inputName] ?: $inputName) . " is required!";

        return false;
    }

    private function checkMin(string $inputName, array $arguments): bool
    {
        $min = $arguments[0];

        $input = $this->inputs[$inputName];

        if (strlen($input) >= $min) {
            return true;
        }

        $this->errors[$inputName] = "The " . ($this->attributeCasts[$inputName] ?: $inputName) . " is too short (min length: $min)!";

        return false;
    }

    private function checkMax(string $inputName, array $arguments): bool
    {
        $max = $arguments[0];

        $input = $this->inputs[$inputName];

        if (strlen($input) <= $max) {
            return true;
        }

        $this->errors[$inputName] = "The " . ($this->attributeCasts[$inputName] ?: $inputName) . " is too long (max length: $max)!";

        return false;
    }

    private function checkConfirmed(string $inputName, array $arguments): bool
    {
        $confirmationInputName = $arguments[0];

        echo $confirmationInputName;

        if (!$confirmationInputName) {
            $confirmationInputName = $inputName . "_confirmation";
        }

        if ($this->inputs[$inputName] === $this->inputs[$confirmationInputName]) {
            return true;
        }

        $this->errors[$inputName] = "The " . ($this->attributeCasts[$inputName] ?: $inputName) . " must be confirmed!";

        return false;
    }

    private function checkUnique(string $inputName, array $arguments)
    {
        $table = $arguments[0];
        $column = $arguments[1];

        if (!$column) {
            $column = $inputName;
        }

        $user = new DB\SQL\Mapper($this->f3->DB, $table);

        if (!$user->exists($column)) {
            $this->f3->error(422, "Unknown column ($column) in $table table");
        }

        $user->load([
            "$column=?", $this->inputs[$inputName]
        ]);

        if (!$user->id) {
            return true;
        }

        $this->errors[$inputName] = "The " . ($this->attributeCasts[$inputName] ?: $inputName) . " must be unique!";

        return false;
    }
}
