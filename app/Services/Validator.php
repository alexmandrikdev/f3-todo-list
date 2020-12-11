<?php

class Validator
{
    private $errors = [];

    private $attributes;

    private $attributeRules;

    private $f3;

    /**
     * Attribute name casts for error messages
     *
     * @var array
     */
    private $attributeCasts = [];

    public function __construct(array $attributes, array $attributeRules)
    {
        $this->attributes = $attributes;

        $this->attributeRules = $attributeRules;

        $this->f3 = Base::instance();
    }

    public function validate(): bool
    {
        foreach ($this->attributeRules as $attributeName => $rules) {
            foreach ($rules as $rule) {
                [$rule, $arguments] = explode(':', $rule);

                $arguments = explode(',', $arguments);

                $functionName = "validate" . ucfirst($rule);

                if (!method_exists('Validator', $functionName)) {
                    $this->f3->error(422, "Invalid validation rule: $rule");
                } else {
                    call_user_func([$this, $functionName], $attributeName, $arguments);
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

    private function sanitizeInputs(array $attributes): array
    {
        $sanitizedInputs = [];

        foreach ($attributes as $attribute) {
            $sanitizedInputs[] = trim(htmlspecialchars($attribute));
        }

        return $sanitizedInputs;
    }

    private function validateRequired(string $attributeName): bool
    {
        $attribute = $this->attributes[$attributeName];

        if (!is_null($attribute) && $attribute !== '') {
            return true;
        }

        $this->errors[$attributeName] = "The " . $this->determineAttributeNameForErrorMessage($attributeName) . " is required!";

        return false;
    }

    private function validateMin(string $attributeName, array $arguments): bool
    {
        $min = $arguments[0];

        $attribute = $this->attributes[$attributeName];

        if (strlen($attribute) >= $min) {
            return true;
        }

        $this->errors[$attributeName] = "The " . $this->determineAttributeNameForErrorMessage($attributeName) . " is too short (min length: $min)!";

        return false;
    }

    private function validateMax(string $attributeName, array $arguments): bool
    {
        $max = $arguments[0];

        $attribute = $this->attributes[$attributeName];

        if (strlen($attribute) <= $max) {
            return true;
        }

        $this->errors[$attributeName] = "The " . $this->determineAttributeNameForErrorMessage($attributeName) . " is too long (max length: $max)!";

        return false;
    }

    private function validateConfirmed(string $attributeName, array $arguments): bool
    {
        $confirmationInputName = $arguments[0];

        echo $confirmationInputName;

        if (!$confirmationInputName) {
            $confirmationInputName = $attributeName . "_confirmation";
        }

        if ($this->attributes[$attributeName] === $this->attributes[$confirmationInputName]) {
            return true;
        }

        $this->errors[$attributeName] = "The " . $this->determineAttributeNameForErrorMessage($attributeName) . " must be confirmed!";

        return false;
    }

    private function validateUnique(string $attributeName, array $arguments): bool
    {
        $table = $arguments[0];
        $column = $arguments[1];

        if (!$column) {
            $column = $attributeName;
        }

        $user = new DB\SQL\Mapper($this->f3->DB, $table);

        if (!$user->exists($column)) {
            $this->f3->error(422, "Unknown column ($column) in $table table");
        }

        $user->load([
            "$column=?", $this->attributes[$attributeName]
        ]);

        if (!$user->id) {
            return true;
        }

        $this->errors[$attributeName] = "The " . $this->determineAttributeNameForErrorMessage($attributeName) . " must be unique!";

        return false;
    }

    private function determineAttributeNameForErrorMessage($attributeName)
    {
        return $this->attributeCasts[$attributeName] ?: $attributeName;
    }
}
