<template>
  <nested-form-icon @click="toggleVisibility" hover-color="info">
    <icon class="cursor-pointer" type="view" viewBox="1 -1.5 20 20" />
  </nested-form-icon>
</template>

<script>
import NestedFormIcon from "./NestedFormIcon";

export default {
  components: { NestedFormIcon },

  props: {
    field: {
      type: Object,
    },

    child: {
      type: Object,
    },
  },

  methods: {
    /**
     * Toggle the visibility.
     */
    toggleVisibility() {
      if (this.child) {
        this.child.opened = !this.child.opened;
      } else if (this.field) {
        this.field.children.forEach((child) => (child.opened = !child.opened));
      }
    },
  },
};
</script>