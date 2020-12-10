<template>
  <div
    class="bg-50 p-4 items-center text-90 flex justify-between nova-nested-form-header"
  >
    <div v-html="heading" v-if="heading" />
    <div class="flex">
      <nested-form-view :child="child" class="mx-2" />
      <nested-form-remove :child="child" :field="field" class="mx-2" />
      <nested-form-add :field="field" class="mx-2" />
    </div>
  </div>
</template>

<script>
import NestedFormAdd from "./NestedFormAdd";
import NestedFormRemove from "./NestedFormRemove";
import NestedFormView from "./NestedFormView";

export default {
  components: {
    NestedFormView,
    NestedFormAdd,
    NestedFormRemove,
  },
  props: {
    child: {
      type: Object,
    },
    field: {
      type: Object,
    },
  },
  computed: {
    /**
     * Get the heading.
     */
    heading() {
        // Field max is set to 0 when it is unlimited!
        // we only want field that has max and min on it, and check if the limit is 1 or less!
        if (typeof this.field.max !== 'undefined' && this.field.min !== 'undefined' && this.field.max !== 0) {
            if ((this.field.max - this.field.min) <= 1) {
                return this.child.heading.replace(/\d+\. /, '');
            }
        } 

      return this.child.heading
        ? this.child.heading.replace(
            new RegExp(
              `${this.field.wrapLeft}(.*?)(?:\\|(.*?))?${this.field.wrapRight}`,
              "g"
            ),
            (match, attribute, defaultValue = "") => {
              const field = this.child.fields.find(
                (field) => field.originalAttribute === attribute
              );
              return field ? field.value : defaultValue;
            }
          )
        : null;
    },
  },
};
</script>