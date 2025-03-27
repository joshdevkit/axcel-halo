<?php

namespace Axcel\AxcelCore\Validators;

use Axcel\AxcelCore\Http\Request;
use Exception;

abstract class FormRequest extends Request
{
    protected array $validatedData = [];
    protected array $validationErrors = [];

    /**
     * Determine if the request is authorized.
     */
    abstract public function authorize(): bool;

    /**
     * Define validation rules.
     */
    abstract public function rules(): array;

    /**
     * Auto-validate when FormRequest is instantiated.
     */
    public function __construct()
    {
        parent::__construct();

        if (!$this->authorize()) {
            $this->failedAuthorization();
        }

        $this->mergeRequestData();
        $this->validatedData = $this->validate($this->rules());
    }

    /**
     * Merge request data properly.
     */
    protected function mergeRequestData(): void
    {
        $mergedData = array_merge($this->query->all(), $_POST);
        $this->request->replace($mergedData);
    }

    /**
     * Validate the request data.
     */
    public function validate(array $rules): array
    {
        $validator = new Validator();
        $result = $validator->validate($this->all(), $rules);

        if (!empty($result['errors'])) {
            $this->validationErrors = $result['errors'];
        }

        unset($result['errors']);
        $this->validatedData = $result;

        return $this->validatedData;
    }

    /**
     * Return only validated input data.
     */
    public function validated(): array
    {
        return $this->validatedData;
    }

    /**
     * Return whether validation failed.
     */
    public function fails(): bool
    {
        return !empty($this->validationErrors);
    }

    /**
     * Return all validation errors.
     */
    public function errors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Handle failed authorization.
     */
    protected function failedAuthorization(): void
    {
        throw new Exception('This action is unauthorized.', 403);
    }
}
