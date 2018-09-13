<template>
    <div v-show="show">
        <div v-for="field in childFields">
            <component
                    :is="'form-' + field.component"
                    :resource-id="resourceId"
                    :resource-name="resourceName"
                    :field="field"
                    :ref="'field-' + field.attribute"
            />
        </div>
    </div>
</template>

<script>
  import {FormField, HandlesValidationErrors} from 'laravel-nova'

  export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    created() {
      this.field.fields.forEach(f => {
        f.value = this.field.value[f.attribute]
      })
    },

    mounted() {
      this.field.dependencies.forEach(dependency => {
        Nova.$on(dependency.field + '-value-changed', (e) => {
          this.compareValues[dependency.field] = e.value

          var vm = this
          vm.show = true
          this.field.dependencies.forEach(dependency => {
            if (dependency.value !== this.compareValues[dependency.field]) {
              vm.show = false
            }
          })
        })
      })

      this.registerValueWatchers(this.$root)
    },

    data() {
      return {
        compareValues: {},
        show: false
      }
    },

    computed: {
      childFields() {
        return this.field.fields
      }
    },

    methods: {
      registerValueWatchers (root) {
        root.$children.forEach(component => {
          if (component.constructor.options !== undefined && component.constructor.options.name !== undefined) {
            if (component.constructor.options.name.endsWith('-field') || this.field.depends_custom.includes(component.constructor.options.name)) {
              component.$watch('value', (value, oldValue) => {
                Nova.$emit(component.field.attribute + '-value-changed', {
                  field: component.field,
                  value: component.value
                })
              }, {immediate: true})

              component.$watch('selectedResource.value', (value, oldValue) => {
                Nova.$emit(component.field.attribute + '-value-changed', {
                  field: component.field,
                  value: component.selectedResource != null ? component.selectedResource.value : component.value
                })
              }, {immediate: true})
            }
          }

          this.registerValueWatchers(component)
        })
      },

      /**
       * Fill the given FormData object with the field's internal value.
       */
      fill(formData) {
        this.field.fields.forEach(f => {
          formData.append(f.attribute, this.$refs['field-' + f.attribute][0].value)
        })
      }

    }
  }
</script>
