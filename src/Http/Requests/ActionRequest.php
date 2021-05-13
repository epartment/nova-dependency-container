<?php

namespace Epartment\NovaDependencyContainer\Http\Requests;

use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Laravel\Nova\Http\Requests\ActionRequest as NovaActionRequest;

class ActionRequest extends NovaActionRequest {

    use HasDependencies;

    /**
     * Handles child fields.
     *
     * @return void
     */
    public function validateFields() {
        $availableFields = [];

        foreach ($this->action()->fields() as $field) {
            if ($field instanceof NovaDependencyContainer) {
                // do not add any fields for validation if container is not satisfied
                if ($field->areDependenciesSatisfied($this)) {
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
