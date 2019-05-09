<template>
  <div class="nested-form-container">
    <!-- HEADING -->
    <div class="p-4 border-b border-40 bg-30 flex justify-between items-center">
      <h1 class="text-90 font-normal text-xl">{{ field.name }}</h1>
      <button
        v-if="displayAddButton"
        type="button"
        class="btn btn-default btn-primary btn-sm leading-none"
        @click="add"
      >
        {{
          __('Add a new :resourceSingularName', {
            resourceSingularName: field.singularLabel
          })
        }}
      </button>
    </div>
    <!-- HEADING -->
    <template v-if="field.children.length > 0">
      <!-- ACTUAL FIELDS -->
      <template v-for="(child, index) in field.children">
        <component
          v-for="(subfield, index) in child.fields"
          :key="`${subfield.attribute}-${index}`"
          :is="'form-' + subfield.component"
          :field="subfield"
          :errors="errors"
          :resourceName="field.relatedResourceName"
          :resourceId="field.relatedResourceId"
        />
      </template>
      <!-- ACTUAL FIELDS -->
    </template>

    <template v-else>
      <p class="m-8">
        {{
          __('No :resourcePluralName', {
            resourcePluralName: field.pluralLabel
          })
        }}.
      </p>
    </template>
  </div>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  computed: {
    /**
     * Whether or not to display the add button
     */
    displayAddButton() {
      return (
        (this.field.is_many_relationship || this.field.children.length === 0) &&
        (this.field.max > 0
          ? this.field.max > this.field.children.length
          : true)
      )
    }
  },

  methods: {
    /**
     * This toggles the visibility of the
     * content of the related resource
     */
    toggleVisibility() {
      this.field.opened = !this.field.opened
    },
    /**
     * This adds a resource to the children
     */
    add() {
      this.field.children.push(this.replaceIndexesInSchema(this.field))
    },

    /**
     * Overrides the fill method.
     */
    fill(formData) {
      this.field.children.forEach(child => {
        child.fields.forEach(field => field.fill(formData))
        formData.append(
          child.attribute + this.field.SEPARATOR + this.field.ID,
          child[this.field.ID]
        )
      })

      console.dir(formData.forEach((v, k) => console.log(v, k)))
    },

    /**
     * This replaces the "{{index}}" values of the schema to
     * their actual index.
     *
     */
    replaceIndexesInSchema(field) {
      const schema = JSON.parse(JSON.stringify(field.schema))

      schme.fields.forEach(field => {
        field.attribute = field.attribute.replace(
          this.field.INDEX,
          this.field.children.length
        )
      })

      return schema
    }
  }
}
</script>
