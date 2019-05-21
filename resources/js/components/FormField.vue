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
			this.registerDependencyWatchers(this.$root)
			this.updateDependencyStatus()
		},

		data() {
			return {
				dependencyValues: {},
				dependenciesSatisfied: false,
			}
		},

		methods: {

			registerDependencyWatchers(root) {
				root.$children.forEach(component => {
					if (this.componentIsDependency(component)) {

						component.$watch('value', (value) => {
							this.dependencyValues[component.field.attribute] = value;
							this.updateDependencyStatus()
						}, {immediate: true})

						this.dependencyValues[component.field.attribute] = component.field.value;

					}

					this.registerDependencyWatchers(component)
				})
			},

			componentIsDependency(component) {
				if(component.field === undefined) {
					return false;
				}

				for (let dependency of this.field.dependencies) {
					if(component.field.attribute === dependency.field) {
						return true;
					}
				}

				return false;
			},

			updateDependencyStatus() {
				for (let dependency of this.field.dependencies) {
					if(dependency.hasOwnProperty('notEmpty') && ! this.dependencyValues[dependency.field]) {
						this.dependenciesSatisfied = false;
						return;
					}
					
					if(dependency.hasOwnProperty('values')) {
						let valuesSatisfied = false;
						for (let value of dependency.values) {
							// Use coercion as form values are always string
							if (this.dependencyValues[dependency.field] == value) {
								valuesSatisfied = true;
							}
						}
						if ( ! valuesSatisfied ) {
							this.dependenciesSatisfied = false;
							return;
						}
					}
				}

				this.dependenciesSatisfied = true;
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
