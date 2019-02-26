<template>
  <div class="nested-form">
    <!-- HEADING -->
    <div class="p-4 border-b border-40 bg-30 flex justify-between items-center"
         :key="`${field.attribute}-${index}`">
      <h1 class="text-90 font-normal text-xl">{{heading}}</h1>
      <div class="flex justify-between items-center">

        <div @click="toggleVisibility"
             class="appearance-none cursor-pointer text-70 hover:text-primary mr-3 items-center flex">
          <icon type="view"
                width="22"
                height="18"
                view-box="0 0 22 16" />
        </div>

        <div @click="showDeleteModal = true"
             class="appearance-none cursor-pointer text-70 hover:text-danger mr-3 items-center flex">
          <icon type="delete" />
        </div>
      </div>
    </div>
    <!-- HEADING -->
    <div v-show="child.opened">

      <!-- ERROR ON ATTACHED RESOURCE -->
      <help-text class="error-text text-danger text-center m-4"
                 v-if="hasError">
        {{ firstError }}
      </help-text>
      <!-- ERROR ON ATTACHED RESOURCE -->

      <!-- ACTUAL FIELDS -->
      <component v-for="subfield in child.fields"
                 :field="subfield"
                 :key="subfield.attribute"
                 :errors="errors"
                 :resource-id="child[field.ID]"
                 :resource-name="field.resourceName"
                 :via-resource="field.viaResource"
                 :via-resource-id="field.viaResourceId"
                 :via-relationship="field.viaRelationship"
                 :related-resource-name="field.relatedResourceName"
                 :related-resource-id="field.relatedResourceId"
                 @file-deleted="$emit('file-deleted')"
                 :is="`form-${getComponent(subfield)}`" />
      <!-- ACTUAL FIELDS -->

    </div>

    <!-- DELETION MODAL -->
    <DeleteModal :resourceSingularName="field.singularLabel"
                 @submit="remove"
                 @close="showDeleteModal = false"
                 v-if="showDeleteModal" />
    <!-- DELETION MODAL -->

  </div>
</template>

<script>
import FormNestedBelongsToField from './CustomNestedFields/BelongsToField'
import FormNestedFileField from './CustomNestedFields/FileField'
import DeleteModal from './Modals/Delete'

export default {

  components: { FormNestedBelongsToField, FormNestedFileField, DeleteModal },


  props: {
    field: {
      type: Object,
      required: true
    },
    child: {
      type: Object,
      required: true
    },
    index: {
      type: Number
    },
    errors: {
      type: Object
    }
  },


  data() {
    return {
      showDeleteModal: false
    }
  },

  computed: {
    /**
     * Transforms the heading
     */
    heading() {
      return this.child.heading.replace(/{{(.*?)}}/g, (val, key) => {
        return key === 'index' ? this.index + 1 || '' : key === 'id' ? this.field.resourceId || '' :
          (this.child.fields.find(field => field.original_attribute === key) || {}).value
      }).replace(/ - $/, '')
    },

    /**
     * Get the error attribute
     */
    errorAttribute() {
      return `${this.field.attribute}${this.field.has_many ? `[${this.index}]` : ''}`
    },

    /**
     * Checks whether the field has errors
     */
    hasError() {
      return Object.keys(this.errors.errors).includes(this.errorAttribute)
    },

    /**
     * Get the first error
     */
    firstError() {
      return this.errors.errors[this.errorAttribute][0]
    }
  },

  methods: {
    /**
     * This toggles the visibility of the
     * content of the related resource
     */
    toggleVisibility() {
      this.child.opened = !this.child.opened
    },

    /**
     * This removes the current child from the
     * parent.
     */
    remove() {
      this.field.children.splice(this.index, 1)
    },

    /**
     * Fill the formData with the children.
     */
    fill(formData) {
      this.child.fields.forEach(field => field.fill(formData))

      if (this.child[this.field.ID]) {
        formData.append(`${this.field.attribute}[${this.index}][${this.field.ID}]`, this.child[this.field.ID])
      }
    },

    /**
     * Get the component dependind on the field.
     */
    getComponent(field) {

      if (['belongs-to-field', 'file-field'].includes(field.component)) {
        return 'nested-' + field.component
      }

      return field.component

    }
  },

  watch: {
    /**
     * Watches for errors in sub fields.
     */
    errors({ errors }) {
      for (let attribute in errors) {
        if (attribute.includes(this.field.attribute)) {
          this.child.opened = true
          break
        }
      }
    }
  },
  created() {
    this.child.fill = this.fill
  }
}
</script>
