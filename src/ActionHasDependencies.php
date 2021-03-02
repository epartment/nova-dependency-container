<?php

namespace Epartment\NovaDependencyContainer;

use Laravel\Nova\Http\Requests\ActionRequest;

trait ActionHasDependencies
{
    use HasChildFields;

    /**
     * Validate action fields
     *
     * @param  \Laravel\Nova\Http\Requests\ActionRequest  $request
     * @return void
     */
    public function validateFields(ActionRequest $request)
    {
        $availableFields = [];

        foreach ($this->fields() as $field) {
            if ($field instanceof NovaDependencyContainer) {
                // do not add any fields for validation if container is not satisfied
                if($field->areDependenciesSatisfied($this)) {
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

        $this->validate(collect($availableFields)->mapWithKeys(function ($field) {
            return $field->getCreationRules($this);
        })->all());
    }
}
