<template>
	<div v-if="dependenciesSatisfied">
		<div v-for="childField in field.fields">
			<component
				:is="'form-' + childField.component"
				:errors="errors"
				:resource-id="resourceId"
				:resource-name="resourceName"
				:field="childField"
				:ref="'field-' + childField.attribute"
			/>
		</div>
	</div>
</template>

<script>
	import {FormField, HandlesValidationErrors} from 'laravel-nova'

	export default {
		mixins: [FormField, HandlesValidationErrors],

		props: ['resourceName', 'resourceId', 'field'],

		mounted() {
			this.isNestedForm = this.$parent.$parent.$options.name == 'form-nested-form';
			this.registerDependencyWatchers(this.$root, function() {
				this.updateDependencyStatus();
			}, true);
		},

		data() {
			return {
				dependencyValues: {},
				dependenciesSatisfied: false,
			}
		},

		methods: {

			// @todo: refactor entire watcher procedure, this approach isn't maintainable ..
			registerDependencyWatchers(root, callback, onMounted) {
				callback = callback || null;
				root = onMounted && this.isNestedForm ? this.$parent.$parent : root;
				root.$children.forEach(component => {
					if (this.componentIsDependency(component)) {
						// @todo: change `findWatchableComponentAttribute` to return initial state(s) of current dependency.
						let attribute = this.findWatchableComponentAttribute(component),
							initial_value = component.field.value; // @note: quick-fix for issue #88

						component.$watch(attribute, (value) => {
							// @todo: move to reactive factory
							if (attribute === 'selectedResource') {
								value = (value && value.value) || null;
							}
							this.dependencyValues[component.field.attribute] = value;
							// @todo: change value as argument for `updateDependencyStatus`
							this.updateDependencyStatus()
						}, {immediate: true});

						// @todo: move to initial state
						// @note quick-fix for issue #88
						if (attribute === 'fieldTypeName') {
							initial_value = component.field.resourceLabel;
						}

						// @todo: replace with `updateDependencyStatus(initial_value)` and let it resolve dependency state
						this.dependencyValues[component.field.attribute] = initial_value;
					}

					this.registerDependencyWatchers(component, null, false)
				});

				if (callback !== null) {
					callback.call(this);
				}
			},

			// @todo: not maintainable, move to factory
			findWatchableComponentAttribute(component) {

				let attribute;
				switch(component.field.component) {
					case 'belongs-to-many-field':
					case 'belongs-to-field':
					case 'nested-form-belongs-to-many-field':
					case 'nested-form-belongs-to-field':
						attribute = 'selectedResource';
						break;
					case 'morph-to-field':
					case 'nested-form-morph-to-field':
						attribute = 'fieldTypeName';
						break;
					default:
						attribute = 'value';
				}
				return attribute;
			},

			componentIsDependency(component) {
				if (component.field === undefined) {
					return false;
				}

				for (let dependency of this.field.dependencies) {
					// #93 compatability with flexible-content, which adds a generated attribute for each field
					let attributeToCheck;
					if(this.isNestedForm) {
						attributeToCheck = this.field.attribute + '[' + dependency.field + ']';
					} else {
						attributeToCheck = this.field.attribute + dependency.field;
					}
					
					if (component.field.attribute === attributeToCheck) {
						return true;
					}
				}

				return false;
			},

			// @todo: align this method with the responsibility of updating the dependency, not verifying the dependency "values"
			updateDependencyStatus() {
				for (let dependency of this.field.dependencies) {

					let attribute;
					if(this.isNestedForm) {
						attribute = this.field.attribute + '[' + dependency.field + ']';
					} else {
						attribute = this.field.attribute + dependency.field;
					}

					// #93 compatability with flexible-content, which adds a generated attribute for each field
					let dependencyValue = this.dependencyValues[attribute];
					if (dependency.hasOwnProperty('empty') && !dependencyValue) {
						this.dependenciesSatisfied = true;
						return;
					}

					if (dependency.hasOwnProperty('notEmpty') && dependencyValue) {
						this.dependenciesSatisfied = true;
						return;
					}

					if (dependency.hasOwnProperty('nullOrZero') && 1 < [undefined, null, 0, '0'].indexOf(dependencyValue) ) {
						this.dependenciesSatisfied = true;
						return;
					}

					if (dependency.hasOwnProperty('not') && dependencyValue !== dependency.not) {
						this.dependenciesSatisfied = true;
						return;
					}

					if (dependency.hasOwnProperty('value') && dependencyValue == dependency.value) {
						this.dependenciesSatisfied = true;
						return;
					}
				}

				this.dependenciesSatisfied = false;
			},

			fill(formData) {
				if (this.dependenciesSatisfied) {
					_.each(this.field.fields, field => {
						if (field.fill) {
							if(this.isNestedForm) {
                                field.fill(formData);
                                for (const [key, value] of formData.entries()) {
                                    if (key == field.attribute) {
                                        formData.append(`${this.field.attribute}[${field.attribute}]`, value)
                                        formData.delete(key)
                                    }
                                }
                            } else {
                                field.fill(formData);
                            }
						}
					})
				}
			}

		}
	}
</script>
