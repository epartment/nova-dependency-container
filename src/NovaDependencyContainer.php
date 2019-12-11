<?php

namespace Epartment\NovaDependencyContainer;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Str;

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
            'dependencies' => array_merge($this->meta['dependencies'], [
                $this->getFieldLayout($field, $value)
            ])
        ]);
    }

    /**
     * Adds a dependency for not
     *
     * @param $field
     * @return NovaDependencyContainer
     */
    public function dependsOnNot($field, $value)
    {
        return $this->withMeta([
            'dependencies' => array_merge($this->meta['dependencies'], [
                array_merge($this->getFieldLayout($field), ['not' => $value])
            ])
        ]);
    }

    /**
     * Adds a dependency for not empty
     *
     * @param $field
     * @return NovaDependencyContainer
     */
    public function dependsOnEmpty($field)
    {
        return $this->withMeta([
            'dependencies' => array_merge($this->meta['dependencies'], [
                array_merge($this->getFieldLayout($field), ['empty' => true])
            ])
        ]);
    }

    /**
     * Adds a dependency for not empty
     *
     * @param $field
     * @return NovaDependencyContainer
     */
    public function dependsOnNotEmpty($field)
    {
        return $this->withMeta([
            'dependencies' => array_merge($this->meta['dependencies'], [
                array_merge($this->getFieldLayout($field), ['notEmpty' => true])
            ])
        ]);
    }

    /**
     * Adds a dependency for null or zero (0)
     *
     * @param $field
     * @param $value
     * @return $this
     */
    public function dependsOnNullOrZero($field)
    {
        return $this->withMeta([
            'dependencies' => array_merge($this->meta['dependencies'], [
                array_merge($this->getFieldLayout($field), ['nullOrZero' => true])
            ])
        ]);
    }

    /**
     * Get layout for a specified field. Dot notation will result in {field}.{property}. If no dot was found it will
     * result in {field}.{field}, as it was in previous versions by default.
     *
     * @param $field
     * @param $value
     * @return array
     */
    protected function getFieldLayout($field, $value = null)
    {
        if (count( ($field = explode('.', $field)) ) === 1) {
            // backwards compatibility, property becomes field
            $field[1] = $field[0];
        }
        return [
            // literal form input name
            'field' => $field[0],
            // property to compare
            'property' => $field[1],
            // value to compare
            'value' => $value,
        ];
    }

    /**
     * Resolve dependency fields for display
     *
     * @param mixed $resource
     * @param null $attribute
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        foreach ($this->meta['fields'] as $field) {
            $field->resolveForDisplay($resource);
        }

        foreach ($this->meta['dependencies'] as $index => $dependency) {

            $this->meta['dependencies'][$index]['satisfied'] = false;

            if (array_key_exists('empty', $dependency) && empty($resource->{$dependency['property']})) {
                $this->meta['dependencies'][$index]['satisfied'] = true;
                continue;
            }
            // inverted `empty()`
            if (array_key_exists('notEmpty', $dependency) && !empty($resource->{$dependency['property']})) {
                $this->meta['dependencies'][$index]['satisfied'] = true;
                continue;
            }
            // inverted
            if (array_key_exists('nullOrZero', $dependency) && in_array($resource->{$dependency['property']}, [null, 0, '0'], true)) {
                $this->meta['dependencies'][$index]['satisfied'] = true;
                continue;
            }

            if (array_key_exists('not', $dependency) && $resource->{$dependency['property']} != $dependency['not']) {
                $this->meta['dependencies'][$index]['satisfied'] = true;
                continue;
            }

            if (array_key_exists('value', $dependency)) {
                if ($dependency['value'] == $resource->{$dependency['property']}) {
                    $this->meta['dependencies'][$index]['satisfied'] = true;
                    continue;
                }
                // @todo: quickfix for MorphTo
                $morphable_attribute = $resource->getAttribute($dependency['property'].'_type');
                if ($morphable_attribute !== null && Str::endsWith($morphable_attribute, '\\'.$dependency['value'])) {
                    $this->meta['dependencies'][$index]['satisfied'] = true;
                    continue;
                }
            }

        }
    }

    /**
     * Resolve dependency fields
     *
     * @param mixed $resource
     * @param string $attribute
     * @return array|mixed
     */
    public function resolve($resource, $attribute = null)
    {
        foreach ($this->meta['fields'] as $field) {
            $field->resolve($resource, $attribute);
        }
    }

    /**
     * Forward fillInto request for each field in this container
     *
     * @trace fill/fillForAction -> fillInto -> *
     *
     * @param NovaRequest $request
     * @param $model
     * @param $attribute
     * @param null $requestAttribute
     */
    public function fillInto(NovaRequest $request, $model, $attribute, $requestAttribute = null)
    {
        foreach($this->meta['fields'] as $field) {
            $field->fill($request, $model);
        }
    }

    /**
     * Checks whether to add validation rules
     *
     * @param NovaRequest $request
     * @return bool
     */
    public function areDependenciesSatisfied(NovaRequest $request)
    {
        if (!isset($this->meta['dependencies'])
            || !is_array($this->meta['dependencies'])) {
            return false;
        }

        $satisfiedCounts = 0;
        foreach ($this->meta['dependencies'] as $index => $dependency) {

            if (array_key_exists('empty', $dependency) && empty($request->has($dependency['property']))) {
                $satisfiedCounts++;
            }

            if (array_key_exists('notEmpty', $dependency) && !empty($request->has($dependency['property']))) {
                $satisfiedCounts++;
            }

            // inverted
            if (array_key_exists('nullOrZero', $dependency) && in_array($request->get($dependency['property']), [null, 0, '0'], true)) {
                $satisfiedCounts++;
            }

            if (array_key_exists('not', $dependency) && $dependency['not'] != $request->get($dependency['property'])) {
                $satisfiedCounts++;
            }

            if (array_key_exists('value', $dependency) && $dependency['value'] == $request->get($dependency['property'])) {
                $satisfiedCounts++;
            }
        }

        return $satisfiedCounts == count($this->meta['dependencies']);
    }

    /**
     * Get a rule set based on field property name
     *
     * @param NovaRequest $request
     * @param string $propertyName
     * @return array
     */
    protected function getSituationalRulesSet(NovaRequest $request, string $propertyName = 'rules')
    {
        $fieldsRules = [$this->attribute => []];
        if (!$this->areDependenciesSatisfied($request)
            || !isset($this->meta['fields'])
            || !is_array($this->meta['fields'])) {
            return $fieldsRules;
        }

        /** @var Field $field */
        foreach ($this->meta['fields'] as $field) {
            $fieldsRules[$field->attribute] = is_callable($field->{$propertyName})
                ? call_user_func($field->{$propertyName}, $request)
                : $field->{$propertyName};
        }

        return $fieldsRules;
    }

    /**
     * Get the validation rules for this field.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function getRules(NovaRequest $request)
    {
        return $this->getSituationalRulesSet($request);
    }

    /**
     * Get the creation rules for this field.
     *
     * @param NovaRequest $request
     * @return array|string
     */
    public function getCreationRules(NovaRequest $request)
    {
        $fieldsRules = $this->getSituationalRulesSet($request, 'creationRules');

        return array_merge_recursive(
            $this->getRules($request),
            $fieldsRules
        );
    }

    /**
     * Get the update rules for this field.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function getUpdateRules(NovaRequest $request)
    {
        $fieldsRules = $this->getSituationalRulesSet($request, 'updateRules');

        return array_merge_recursive(
            $this->getRules($request),
            $fieldsRules
        );
    }
}
