<?php

namespace Epartment\NovaDependencyContainer;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasDependencies
{
    /**
     * @param NovaRequest $request
     * @return FieldCollection|\Illuminate\Support\Collection
     */
    public function availableFields(NovaRequest $request)
    {
        $fields = $this->fields($request);
        $availableFields = [];

        foreach ($fields as $field) {
            if ($field instanceof NovaDependencyContainer) {
                $availableFields[] = $field;
                if ($this->doesRouteRequireChildFields()) {
                    $availableFields = array_merge($availableFields, $field->meta['fields']);
                }
            } else {
                $availableFields[] = $field;
            }
        }

        return new FieldCollection(array_values($this->filter($availableFields)));
    }

    /**
     * @return bool
     */
    protected function doesRouteRequireChildFields(): bool
    {
        return ends_with(Route::currentRouteAction(), 'AssociatableController@index')
            || ends_with(Route::currentRouteAction(), 'ResourceStoreController@handle')
            || ends_with(Route::currentRouteAction(), 'ResourceUpdateController@handle');
    }
}
