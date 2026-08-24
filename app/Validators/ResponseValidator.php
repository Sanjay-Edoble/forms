<?php

namespace App\Validators;

/**
 * Server-side response validation against form schema.
 */
class ResponseValidator
{
    /**
     * Validate answers against form schema.
     * Returns array of errors (empty if valid).
     */
    public function validate(array $schema, array $answers): array
    {
        $errors = [];
        $questions = $schema['questions'] ?? [];

        foreach ($questions as $question) {
            $qId    = $question['id'] ?? '';
            $type   = $question['type'] ?? '';
            $title  = $question['title'] ?? 'Question';
            $required = $question['required'] ?? false;
            $value  = $answers[$qId] ?? null;

            // Check required
            if ($required && ($value === null || $value === '' || $value === [])) {
                $errors[$qId] = "{$title} is required.";
                continue;
            }

            // Skip validation if empty and not required
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            // Type-specific validation
            switch ($type) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$qId] = 'Please enter a valid email address.';
                    }
                    break;

                case 'number':
                    if (!is_numeric($value)) {
                        $errors[$qId] = 'Please enter a valid number.';
                    }
                    $min = $question['validation']['min'] ?? null;
                    $max = $question['validation']['max'] ?? null;
                    if ($min !== null && (float)$value < (float)$min) {
                        $errors[$qId] = "Value must be at least {$min}.";
                    }
                    if ($max !== null && (float)$value > (float)$max) {
                        $errors[$qId] = "Value must be at most {$max}.";
                    }
                    break;

                case 'phone':
                    if (!preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $value)) {
                        $errors[$qId] = 'Please enter a valid phone number.';
                    }
                    break;

                case 'short_text':
                case 'paragraph':
                    $maxLen = $question['validation']['maxLength'] ?? null;
                    if ($maxLen && strlen($value) > $maxLen) {
                        $errors[$qId] = "Maximum {$maxLen} characters allowed.";
                    }
                    break;

                case 'multiple_choice':
                case 'dropdown':
                    $options = array_column($question['options'] ?? [], 'value');
                    if (!empty($options) && !in_array($value, $options)) {
                        $errors[$qId] = 'Please select a valid option.';
                    }
                    break;

                case 'checkboxes':
                    if (!is_array($value)) {
                        $errors[$qId] = 'Invalid selection.';
                    } else {
                        $options = array_column($question['options'] ?? [], 'value');
                        if (!empty($options)) {
                            foreach ($value as $v) {
                                if (!in_array($v, $options)) {
                                    $errors[$qId] = 'Invalid option selected.';
                                    break;
                                }
                            }
                        }
                    }
                    break;

                case 'rating':
                    $max = $question['max'] ?? 5;
                    if (!is_numeric($value) || (int)$value < 1 || (int)$value > $max) {
                        $errors[$qId] = "Please select a rating between 1 and {$max}.";
                    }
                    break;

                case 'linear_scale':
                    $min = $question['scaleMin'] ?? 1;
                    $max = $question['scaleMax'] ?? 10;
                    if (!is_numeric($value) || (int)$value < $min || (int)$value > $max) {
                        $errors[$qId] = "Please select a value between {$min} and {$max}.";
                    }
                    break;

                case 'date':
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        $errors[$qId] = 'Please enter a valid date.';
                    }
                    break;

                case 'time':
                    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                        $errors[$qId] = 'Please enter a valid time.';
                    }
                    break;
            }
        }

        return $errors;
    }
}
