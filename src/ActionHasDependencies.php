<?php

namespace Epartment\NovaDependencyContainer;

use Laravel\Nova\Http\Requests\ActionRequest;
use Illuminate\Support\Facades\Validator;

trait ActionHasDependencies
{
    use HasChildFields;

    protected function fieldsForValidation(ActionRequest $request)
    {
        $availableFields = [];

        foreach ($this->fields() as $field) {
            if ($field instanceof NovaDependencyContainer) {
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
    }

    /**
     * Validate action fields. Mostly a copy paste from Nova
     *
     * Uses the above to validate only on fields that have satisfied dependencies.
     *
     * @param  \Laravel\Nova\Http\Requests\ActionRequest  $request
     * @return void
     */
    public function validateFields(ActionRequest $request)
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
