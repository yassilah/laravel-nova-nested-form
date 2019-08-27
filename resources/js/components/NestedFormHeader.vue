<template>
  <div class="bg-50 p-4 items-center text-90 flex justify-between">
    <div
      v-html="heading"
      v-if="heading"
    />
    <div class="flex">
      <nested-form-view
        :child="child"
        class="mx-2"
      />
      <nested-form-remove
        :child="child"
        :field="field"
        class="mx-2"
      />
      <nested-form-add
        :field="field"
        class="mx-2"
      />
    </div>
  </div>
</template>

<script>
import NestedFormAdd from './NestedFormAdd'
import NestedFormRemove from './NestedFormRemove'
import NestedFormView from './NestedFormView'

export default {
  components: {
    NestedFormView,
    NestedFormAdd,
    NestedFormRemove
  },
  props: {
    child: {
      type: Object
    },
    field: {
      type: Object
    }
  },
  computed: {
    /**
     * Get the heading.
     */
    heading() {
      return this.child.heading
        ? this.child.heading.replace(
            new RegExp(
              `${this.field.wrapLeft}(.*?)(?:\\|(.*?))?${this.field.wrapRight}`,
              'g'
            ),
            (match, attribute, defaultValue = '') => {
              const field = this.child.fields.find(
                field => field.originalAttribute === attribute
              )
              return field ? field.value : defaultValue
            }
          )
        : null
    }
  }
}
</script>