<template>
  <div class="nested-form-container">

    <template v-if="field.children.length > 0">

      <div :key="index"
           v-for="(child, index) in field.children">

        <!-- HEADING -->
        <div class="p-4 bg-40 flex justify-between items-center cursor-pointer text-80 hover:bg-30 hover:text-70"
             @click.stop="child.opened = !child.opened">
          <h4 class="font-bold"
              :key="`heading-${index}`">{{ getProperHeading(child) }}</h4>
          <div class="flex">
            <div @click.stop="remove(index)"
                 class="appearance-none cursor-pointer text-70 hover:text-danger mr-3">
              <icon type="delete" />
            </div>
            <div v-if="displayAddButton && index === field.children.length - 1"
                 @click.stop="add"
                 class="appearance-none cursor-pointer text-70 hover:text-warning mr-3">
              <icon type="add" />
            </div>
          </div>
        </div>
        <!-- HEADING -->

        <template v-if="child.opened">
          <component v-for="(subfield, index) in child.fields"
                     :key="`${subfield.attribute}-${index}`"
                     :is="'form-' + subfield.component"
                     :field="subfield"
                     :errors="errors"
                     :resourceName="subfield.resourceName" />
        </template>
      </div>
    </template>

    <field-wrapper v-else>
      <div class="w-1/5 py-6 px-8">
        <form-label :label-for="field.attribute">
          {{ __('No :resourcePluralName', { resourcePluralName: field.pluralLabel }) }}.
        </form-label>
      </div>

      <div class="py-6">
        <button v-if="displayAddButton"
                type="button"
                class="btn btn-default btn-primary btn-sm leading-none"
                @click="add">
          {{ __('Add a new :resourceSingularName', { resourceSingularName: field.singularLabel }) }}
        </button>
      </div>
    </field-wrapper>
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
        (this.field.isManyRelationship || this.field.children.length === 0) &&
        (this.field.max > 0 ? this.field.max > this.field.children.length : true)
      )
    }
  },

  methods: {

    /**
     * Get the templated heading for a given
     * child form.
     */
    getProperHeading(child) {
      return child.heading.replace(/{{(.*?)}}/g, (match, key) => {
        const field = child.fields.find(field => field.originalAttribute === key)
        return field ? field.value : ''
      })
    },

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
     * This removes the current child.
     */
    remove(index) {
      this.field.children.splice(index, 1)
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
     */
    replaceIndexesInSchema(field) {
      return this.replaceIndexKeys(field.schema)
    },

    /**
     * Recursively replace the INDEX key.
     */
    replaceIndexKeys(current) {
      current = JSON.parse(JSON.stringify(current))

      for (let key in current) {
        if (Array.isArray(current[key]) || typeof current[key] === 'object') {
          current[key] = this.replaceIndexKeys(current[key])
        } else if (key !== 'INDEX' && typeof current[key] === 'string') {
          current[key] = this.replaceIndexValue(current[key], key === 'heading')
        }
      }

      return current
    },

    /**
     * Replace the INDEX key with the number of children.
     */
    replaceIndexValue(value, isHeading) {
      return value.replace(this.field.INDEX, this.field.children.length + (isHeading ? 1 : 0))
    }
  }
}
</script>
