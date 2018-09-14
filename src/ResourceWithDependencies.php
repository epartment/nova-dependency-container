<?php

namespace Epartment\NovaDependencyContainer;

use App\Nova\Resource;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;

abstract class ResourceWithDependencies extends Resource
{
    public function availableFields(NovaRequest $request)
    {
        $fields = $this->fields($request);

        foreach ($fields as $field) {
            if (get_class($field) == NovaDependencyContainer::class) {
                /** @var $field NovaDependencyContainer */
                $fields = array_merge($fields, $field->meta['fields']);
            }
        }

        return new FieldCollection(array_values($this->filter($fields)));
    }
}