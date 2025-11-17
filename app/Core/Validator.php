<?php

namespace App\Core;

/**
 * Validador centralizado para validación de datos
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $customMessages = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validar datos con reglas
     */
    public function validate(array $rules, array $customMessages = []): bool
    {
        $this->rules = $rules;
        $this->customMessages = $customMessages;
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $ruleArray = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            
            foreach ($ruleArray as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Aplicar regla individual
     */
    private function applyRule(string $field, string $rule): void
    {
        // Parsear regla con parámetros (ej: max:255)
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        $value = $this->data[$field] ?? null;

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'required');
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'email');
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, 'numeric');
                }
                break;

            case 'integer':
                if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'integer');
                }
                break;

            case 'min':
                if (isset($params[0])) {
                    $min = $params[0];
                    if (is_string($value) && strlen($value) < $min) {
                        $this->addError($field, 'min', ['min' => $min]);
                    } elseif (is_numeric($value) && $value < $min) {
                        $this->addError($field, 'min', ['min' => $min]);
                    }
                }
                break;

            case 'max':
                if (isset($params[0])) {
                    $max = $params[0];
                    if (is_string($value) && strlen($value) > $max) {
                        $this->addError($field, 'max', ['max' => $max]);
                    } elseif (is_numeric($value) && $value > $max) {
                        $this->addError($field, 'max', ['max' => $max]);
                    }
                }
                break;

            case 'in':
                if ($value && !in_array($value, $params)) {
                    $this->addError($field, 'in', ['values' => implode(', ', $params)]);
                }
                break;

            case 'date':
                if ($value && !strtotime($value)) {
                    $this->addError($field, 'date');
                }
                break;

            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'url');
                }
                break;

            case 'alpha':
                if ($value && !preg_match('/^[a-zA-Z]+$/', $value)) {
                    $this->addError($field, 'alpha');
                }
                break;

            case 'alpha_num':
                if ($value && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                    $this->addError($field, 'alpha_num');
                }
                break;

            case 'boolean':
                if ($value !== null && !is_bool($value) && !in_array($value, [0, 1, '0', '1', true, false], true)) {
                    $this->addError($field, 'boolean');
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (!isset($this->data[$confirmField]) || $value !== $this->data[$confirmField]) {
                    $this->addError($field, 'confirmed');
                }
                break;

            case 'unique':
                // Requiere parámetros: unique:table,column
                if (isset($params[0], $params[1])) {
                    if (!$this->isUnique($params[0], $params[1], $value)) {
                        $this->addError($field, 'unique');
                    }
                }
                break;
        }
    }

    /**
     * Agregar error de validación
     */
    private function addError(string $field, string $rule, array $params = []): void
    {
        $message = $this->customMessages[$field . '.' . $rule] 
                   ?? $this->getDefaultMessage($field, $rule, $params);
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }

    /**
     * Obtener mensaje de error por defecto
     */
    private function getDefaultMessage(string $field, string $rule, array $params = []): string
    {
        $fieldName = $this->formatFieldName($field);
        
        $messages = [
            'required' => "El campo $fieldName es requerido",
            'email' => "El campo $fieldName debe ser un email válido",
            'numeric' => "El campo $fieldName debe ser numérico",
            'integer' => "El campo $fieldName debe ser un número entero",
            'min' => "El campo $fieldName debe ser mayor o igual a {$params['min']}",
            'max' => "El campo $fieldName debe ser menor o igual a {$params['max']}",
            'in' => "El campo $fieldName debe ser uno de: {$params['values']}",
            'date' => "El campo $fieldName debe ser una fecha válida",
            'url' => "El campo $fieldName debe ser una URL válida",
            'alpha' => "El campo $fieldName solo debe contener letras",
            'alpha_num' => "El campo $fieldName solo debe contener letras y números",
            'boolean' => "El campo $fieldName debe ser verdadero o falso",
            'confirmed' => "La confirmación de $fieldName no coincide",
            'unique' => "El valor de $fieldName ya existe"
        ];

        return $messages[$rule] ?? "El campo $fieldName no es válido";
    }

    /**
     * Formatear nombre de campo
     */
    private function formatFieldName(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Verificar si un valor es único en la base de datos
     */
    private function isUnique(string $table, string $column, $value): bool
    {
        try {
            $db = \App\Services\Database::getInstance();
            $query = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
            $result = $db->selectOne($query, [$value]);
            return $result['count'] == 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener errores de validación
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener primer error
     */
    public function firstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }
        
        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0] ?? null;
    }

    /**
     * Método estático para validación rápida
     */
    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        $validator = new self($data);
        $validator->validate($rules, $customMessages);
        return $validator;
    }

    /**
     * Verificar si la validación pasó
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Verificar si la validación falló
     */
    public function fails(): bool
    {
        return !$this->passes();
    }
}
