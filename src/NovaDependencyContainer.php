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
        $this->withMeta(['depends_custom' => []]);
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
     * Allows you to pass component names that should be watched
     * by the container for value changes
     *
     * @param $componentName
     * @return $this
     */
    public function dependsOnCustomComponent($componentName)
    {
        return $this->withMeta([
            'depends_custom' => array_merge($this->meta['depends_custom'], [$componentName])
        ]);
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
        $values = [];

        foreach ($this->meta['fields'] as $field) {
            $values[$field->attribute] = $resource->{$field->attribute};
        }

        return $values;
    }

    /**
     * Fills models attributes based on dependency fields
     *
     * @param NovaRequest $request
     * @param string $requestAttribute
     * @param object $model
     * @param string $attribute
     */
    protected function fillAttributeFromRequest(\Laravel\Nova\Http\Requests\NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        foreach ($this->meta['fields'] as $field) {
            if ($request->exists($field->attribute)) {
                $model->{$field->attribute} = $request[$field->attribute];
            }
        }
    }
}
