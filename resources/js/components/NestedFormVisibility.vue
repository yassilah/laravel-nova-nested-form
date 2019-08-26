<template>
  <div
    @click="toggleVisibility"
    class="block text-80 flex items-center justify-center hover:border-70 hover:text-70"
  >
    <icon
      class="cursor-pointer"
      type="view"
      viewBox="1 -1.5 20 20"
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator'
import { Field } from '../../@types/Field'
import { Child } from '../../@types/Child'

@Component
export default class NestedFormVisibility extends Vue {
  @Prop() public field: Field
  @Prop() public child: Child
  /**
   * Toggle the visibility.
   */
  public toggleVisibility() {
    if (this.child) {
      this.child.opened = !this.child.opened
    } else if (this.field) {
      this.field.children.forEach(child => (child.opened = !child.opened))
    }
  }
}
</script>