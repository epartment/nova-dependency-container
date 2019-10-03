<?php

namespace Epartment\NovaDependencyContainer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
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
                if ($this->doesRouteRequireChildFields() && self::doesFieldSatisfyConstraints($field, $request)) {
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
     * @param \Laravel\Nova\Fields\Field $field
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return bool
     */
    static function doesFieldSatisfyConstraints(Field $field, NovaRequest $request)
    {

        /**
         * Check if any constrain has been satisfied otherwise bail the execution,
         * if user has multiple instances of NovaDependencyContainer::make()
         * this ensure only the one that has been satisfied is filled
         */
        foreach ($field->meta[ 'dependencies' ] as $dependency) {

            $inputValue = $request->input($dependency[ 'field' ]);

            if (array_key_exists('notEmpty', $dependency) && is_null($inputValue) || $inputValue != $dependency[ 'value' ]) {

                return false;

            }

        }

        return true;

    }

    /**
     * @return bool
     */
    protected function doesRouteRequireChildFields() : bool
    {
        return Str::endsWith(Route::currentRouteAction(), 'AssociatableController@index')
            || Str::endsWith(Route::currentRouteAction(), 'ResourceStoreController@handle')
            || Str::endsWith(Route::currentRouteAction(), 'ResourceUpdateController@handle')
            || Str::endsWith(Route::currentRouteAction(), 'FieldDestroyController@handle');
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
        if ( !empty( ($action_fields = $this->action()->fields()) ) ) {
            foreach ($action_fields as $field) {
                if ($field instanceof NovaDependencyContainer) {
                    $availableFields[] = $field;
                    $this->extractChildFields($field->meta['fields']);
                } else {
                    $availableFields[] = $field;
                }
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
