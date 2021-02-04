<?php

namespace Epartment\NovaDependencyContainer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Http\Requests\ActionRequest;

trait HasDependencies
{
    protected $childFieldsArr = [];

    /**
     * @param NovaRequest $request
     * @return FieldCollection|\Illuminate\Support\Collection
     */
    public function availableFields(NovaRequest $request)
    {
        // needs to be filtered once to resolve Panels
        $fields = $this->filter($this->fields($request));
        $availableFields = [];

        foreach ($fields as $field) {
            if ($field instanceof NovaDependencyContainer) {
                $availableFields[] = $this->filterFieldForRequest($field, $request);
                if($field->areDependenciesSatisfied($request) || $this->extractableRequest($request, $this->model())) {
                    if ($this->doesRouteRequireChildFields()) {
                        $this->extractChildFields($field->meta['fields']);
                    }
                }
            } else {
                $availableFields[] = $this->filterFieldForRequest($field, $request);
            }
        }

        if ($this->childFieldsArr) {
            $availableFields = array_merge($availableFields, $this->childFieldsArr);
        }


        $availableFields = new FieldCollection(array_values($this->filter($availableFields)));
        return $availableFields;
    }

    /**
     * Check if request needs to extract child fields
     *
     * @param NovaRequest $request
     * @param $model
     * @return bool
     */
    protected function extractableRequest(NovaRequest $request, $model) {
        // if form was submitted to update (method === 'PUT')
        if($request->isUpdateOrUpdateAttachedRequest() && strtoupper($request->get('_method', null)) === 'PUT') {
            return false;
        }
        // if form was submitted to create and new resource
        if($request->isCreateOrAttachRequest() && $model->id === null) {
            return false;
        }
        return true;
    }

    /**
     * @param $field
     * @param NovaRequest $request
     * @return mixed
     *
     * @todo: implement
     */
    public function filterFieldForRequest($field, NovaRequest $request) {
        // @todo: filter fields for request, e.g. show/hideOnIndex, create, update or whatever
        return $field;
    }

    /**
     * @param array $availableFields
     * @param NovaRequest $request
     */
    public function filterFieldsForRequest(Collection $availableFields, NovaRequest $request) {
        return $availableFields;
    }

    /**
     * @return bool
     */
    protected function doesRouteRequireChildFields() : bool
    {
        return Str::endsWith(Route::currentRouteAction(), [
            'FieldDestroyController@handle',
            'ResourceUpdateController@handle',
            'ResourceStoreController@handle',
            'AssociatableController@index',
            'MorphableController@index',
        ]);
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
                    // @todo: we should not randomly apply rules to child-fields.
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
     * @param  \Laravel\Nova\Http\Requests\ActionRequest  $request
     * @return void
     */
    public function validateFields(ActionRequest $request = null) {
        $availableFields = [];
        if ( !empty( ($action_fields = $this->action()->fields()) ) ) {
            foreach ($action_fields as $field) {
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
        }

        if ($this->childFieldsArr) {
            $availableFields = array_merge($availableFields, $this->childFieldsArr);
        }

        $this->validate(collect($availableFields)->mapWithKeys(function ($field) {
            return $field->getCreationRules($this);
        })->all());
    }
}
