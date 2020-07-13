<template>
	<div v-if="dependenciesSatisfied" :class="field.class">
			<component v-for="childField in field.fields"
				:is="'detail-' + childField.component"
				:resource-id="resourceId"
				:resource-name="resourceName"
				:field="childField"
				:ref="'field-' + childField.attribute"
			/>
	</div>
</template>

<script>
	export default {
		props: ['resource', 'resourceName', 'resourceId', 'field'],

		created() {
			this.updateDependencyStatus()
		},

		data() {
			return {
				dependenciesSatisfied: false,
			}
		},

		methods: {

			updateDependencyStatus() {
				for (let dependency of this.field.dependencies) {
					if(dependency.satisfied) {
						this.dependenciesSatisfied = true;
						return;
					}
				}

				this.dependenciesSatisfied = false;
			},

		}
	}
</script>
