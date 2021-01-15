<template>
  <nested-form-icon
    @click="removeChild"
    hover-color="danger"
    v-if="field.children.length > field.min"
  >
    <icon class="cursor-pointer" type="delete" viewBox="0 0 20 20" />
  </nested-form-icon>
</template>

<script>
import NestedFormIcon from "./NestedFormIcon";

export default {
  components: { NestedFormIcon },

  props: {
    field: {
      type: Object,
      required: true,
    },

    child: {
      type: Object,
      required: true,
    },
  },

  methods: {
    /**
     * Remove a child.
     */
    removeChild() {
      this.field.children.splice(this.field.children.indexOf(this.child), 1);
    },
  },
};
</script>