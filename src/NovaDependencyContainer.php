<?php

namespace Epartment\NovaDependencyContainer;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaDependencyContainer extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-dependency-container';

    /**
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * NovaDependencyContainer constructor.
     *
     * @param $fields
     * @param null $attribute
     * @param null $resolveCallback
     */
    public function __construct($fields, $attribute = null, $resolveCallback = null)
    {
        parent::__construct('', $attribute, $resolveCallback);

        $this->withMeta(['fields' => $fields]);
        $this->withMeta(['dependencies' => []]);
    }

    /**
     * Adds a dependency
     *
     * @param $field
     * @param $value
     * @return $this
     */
    public function dependsOn($field, $value)
    {
        return $this->withMeta([
            'dependencies' => array_merge($this->meta['dependencies'], [['field' => $field, 'value' => $value]])
        ]);
    }

    /**
     *
     *
     * @param $field
     * @return NovaDependencyContainer
     */
    public function dependsOnNotEmpty($field)
    {
        return $this->withMeta([
            'dependencies' => array_merge($this->meta['dependencies'], [['field' => $field, 'notEmpty' => true]])
        ]);
    }

    /**
     * @param mixed $resource
     * @param null $attribute
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        parent::resolveForDisplay($resource, $attribute);

        foreach ($this->meta['dependencies'] as $index => $dependency) {
            if(array_key_exists('notEmpty', $dependency) && ! empty($resource->{$dependency['field']})) {
                $this->meta['dependencies'][$index]['satisfied'] = true;
            }

            if(array_key_exists('value', $dependency) && $dependency['value'] == $resource->{$dependency['field']}) {
                $this->meta['dependencies'][$index]['satisfied'] = true;
            }
        }
    }

    /**
     * Retrieve values of dependency fields
     *
     * @param mixed $resource
     * @param string $attribute
     * @return array|mixed
     */
    protected function resolveAttribute($resource, $attribute)
    {
        foreach ($this->meta['fields'] as $field) {
            $field->resolve($resource);
        }

        return [];
    }

    /**
     * Fills the attributes of the model within the container if the dependencies for the container are satisfied.
     *
     * @param NovaRequest $request
     * @param string $requestAttribute
     * @param object $model
     * @param string $attribute
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        foreach ($this->meta['fields'] as $field) {
            $field->fill($request, $model);
        }
    }
}
