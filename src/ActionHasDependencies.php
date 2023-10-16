<?php

namespace Workup\Nova\DependencyContainer;

use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Http\Requests\ActionRequest;
use Illuminate\Validation\ValidationException;

trait ActionHasDependencies
{
    use HasChildFields;

    protected function fieldsForValidation(ActionRequest $request): array
    {
        $availableFields = [];

        foreach ($this->fields() as $field) {
            if ($field instanceof DependencyContainer) {
                // do not add any fields for validation if container is not satisfied
                if ($field->areDependenciesSatisfied($request)) {
                    $availableFields[] = $field;
                    $this->extractChildFields($field->meta['fields']);
                }
            } else {
                $availableFields[] = $field;
            }
        }

        if ($this->childFieldsArr) {
            $availableFields = array_merge($availableFields, $this->childFieldsArr);
        }

        return $availableFields;
    }

    /**
     * Validate action fields. Mostly a copy paste from Nova
     *
     * Uses the above to validate only on fields that have satisfied dependencies.
     *
     * @param  ActionRequest  $request
     *
     * @return array
     * @throws ValidationException
     */
    public function validateFields(ActionRequest $request): array
    {
        $fields = collect($this->fieldsForValidation($request));

        return Validator::make(
            $request->all(),
            $fields->mapWithKeys(function ($field) use ($request) {
                return $field->getCreationRules($request);
            })->all(),
            [],
            $fields->reject(function ($field) {
                return empty($field->name);
            })->mapWithKeys(function ($field) {
                return [$field->attribute => $field->name];
            })->all()
        )->after(function ($validator) use ($request) {
            $this->afterValidation($request, $validator);
        })->validate();
    }
}
