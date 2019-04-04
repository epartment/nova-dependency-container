<?php

namespace Epartment\NovaDependencyContainer;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasDependencies
{
    protected $childFieldsArr = [];
    
    /**
     * @param NovaRequest $request
     * @return FieldCollection|\Illuminate\Support\Collection
     */
    public function availableFields(NovaRequest $request)
    {
        // Needs to be filtered once to resolve Panels
        $fields = $this->filter($this->fields($request));
        $availableFields = [];

        foreach ($fields as $field) {
            if ($field instanceof NovaDependencyContainer) {
                $availableFields[] = $field;
                if ($this->doesRouteRequireChildFields()) {
                    $this->extractChildFields($field->meta['fields']);
                }
            } else {
                $availableFields[] = $field;
            }
        }

        if ($this->childFieldsArr) {
            $availableFields = array_merge($availableFields, $this->childFieldsArr);
        }
        
        return new FieldCollection(array_values($this->filter($availableFields)));
    }

    /**
     * @return bool
     */
    protected function doesRouteRequireChildFields() : bool
    {
        return ends_with(Route::currentRouteAction(), 'AssociatableController@index')
            || ends_with(Route::currentRouteAction(), 'ResourceStoreController@handle')
            || ends_with(Route::currentRouteAction(), 'ResourceUpdateController@handle')
            || ends_with(Route::currentRouteAction(), 'FieldDestroyController@handle');
    }

    /**
     * @param  [array] $childFields [meta fields]
     * @return void
     */
    protected function extractChildFields($childFields)
    {
        foreach ($childFields as $childField) {
            if ($childField instanceof NovaDependencyContainer) {
                $this->extractChildFields($childField->meta['fields']);
            } else {
                if (array_search($childField->attribute, array_column($this->childFieldsArr, 'attribute')) === false) {
                    $childField = $this->applyRulesForChildFields($childField);
                    $this->childFieldsArr[] = $childField;
                }
            }
        }
    }

    /**
     * @param  [array] $childField
     * @return [array] $childField
     */
    protected function applyRulesForChildFields($childField)
    {
        if (isset($childField->rules)) {
            $childField->rules[] = "sometimes:required:".$childField->attribute;
        }
        if (isset($childField->creationRules)) {
            $childField->creationRules[] = "sometimes:required:".$childField->attribute;
        }
        if (isset($childField->updateRules)) {
            $childField->updateRules[] = "sometimes:required:".$childField->attribute;
        }
        return $childField;
    }

    /**
     * Validate action fields
     * Overridden using ActionController & ActionRequest by modifying routes
     * @return void
     */
    public function validateFields() {
        $availableFields = [];
        foreach ($this->action()->fields() as $field) {
            if ($field instanceof NovaDependencyContainer) {
                $availableFields[] = $field;
                $this->extractChildFields($field->meta['fields']);
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