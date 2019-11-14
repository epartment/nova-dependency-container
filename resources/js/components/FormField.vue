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
	import _ from 'lodash';

	export default {
		mixins: [FormField, HandlesValidationErrors],

		props: ['resourceName', 'resourceId', 'field'],

		mounted() {
			this.initializeComponent();
		},

		data() {
			return {
				dependencyValues: {},
				dependenciesSatisfied: false,

				/**
				 * compatibility between packages
				 */
				packageCompat: {
					// whitecubes flexible-content
					// flexible-contents adds fields on the fly by generating a hashed attribute for each added layout (group)
					// @hash "hash-LayoutName__fieldattribute"
					packages: {
						flexibleContent: {
							// the package component name
							key: 'flexibleContent',
							name: 'nova-flexible-content',
							// return value of `check`
							is: false,
							// check if container needs to run in compatMode for `package_name`
							check: function (container) {
								let component = container.$parent,
										is;

								if ((is = typeof component.field !== 'undefined' &&
										component.field.component === this.name)) {

									container.compatMode = this.key;
									// gather settings
									this.settings.group_key = component.group.key;
								}

								return is;
							},
							// settings to gather
							settings: {
								group_key: null
							},
							// callbacks to execute
							callbacks: {
								getRootComponent() {
									return this.$parent;
								}
							}
						}
					}
				},

				compatMode: null
			}
		},

		methods: {

			checkCompatability() {
				_.each(this.packageCompat.packages, function(_package) {
					_package.check(this);
				}.bind(this));
			},

			getCompatibilityCallback(callback_name) {
				if(this.compatMode !== null) {
					let callback = this.packageCompat.packages[this.compatMode].callbacks[callback_name];
					if(typeof callback !== 'undefined') {
						return callback.bind(this);
					}
				}
				return null;
			},

			initializeComponent() {
				// first check if we need to consider any compatibilities
				this.checkCompatability();
				// register dependency watchers for any changes
				this.registerDependencyWatchers(this.getRootComponent(), function() {
					this.updateDependencyStatus();
				});
			},

			getRootComponent() {
				let callback;
				if((callback = this.getCompatibilityCallback('getRootComponent')) !== null) {
					return callback();
				}
				// default
				return this.$root;
			},

			// @todo: refactor entire watcher procedure, this approach isn't maintainable ..
			registerDependencyWatchers(root, callback) {
				callback = callback || null;
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
							// @todo: changed value as argument for `updateDependencyStatus`
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

					this.registerDependencyWatchers(component)
				});

				if(callback !== null) {
					callback.call(this);
				}
			},

			// @todo: not maintainable, move to factory
			findWatchableComponentAttribute(component) {
				let attribute;
				switch(component.field.component) {
					case 'belongs-to-field':
						attribute = 'selectedResource';
						break;
					case 'morph-to-field':
						attribute = 'fieldTypeName';
						break;
					default:
						attribute = 'value';
				}
				return attribute;
			},

			componentIsDependency(component) {
				if(component.field === undefined) {
					return false;
				}
				for (let dependency of this.field.dependencies) {
					if(component.field.attribute === (this.field.attribute + dependency.field)) {
						return true;
					}
				}

				return false;
			},

			// @todo: align this method with the responsibility of updating the dependency, not verifying the dependency "values"
			updateDependencyStatus() {
				for (let dependency of this.field.dependencies) {

					let dependencyValue = this.dependencyValues[(this.field.attribute + dependency.field)];
					if(dependency.hasOwnProperty('empty') && !dependencyValue) {
						this.dependenciesSatisfied = true;
						return;
					}

					if(dependency.hasOwnProperty('notEmpty') && dependencyValue) {
						this.dependenciesSatisfied = true;
						return;
					}

					if(dependency.hasOwnProperty('nullOrZero') && 1 < [undefined, null, 0, '0'].indexOf(dependencyValue) ) {
						this.dependenciesSatisfied = true;
						return;
					}

					if(dependency.hasOwnProperty('value') && dependencyValue == dependency.value) {
						this.dependenciesSatisfied = true;
						return;
					}
				}

				this.dependenciesSatisfied = false;
			},

			fill(formData) {
				if(this.dependenciesSatisfied) {
					_.each(this.field.fields, field => {
						if (field.fill) {
							field.fill(formData)
						}
					})
				}
			}

		}
	}
</script>
