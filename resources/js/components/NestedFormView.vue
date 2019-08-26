<template>
  <nested-form-icon
    @click="toggleVisibility"
    hover-color="info"
  >
    <icon
      class="cursor-pointer"
      type="view"
      viewBox="1 -1.5 20 20"
    />
  </nested-form-icon>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator'
import { Field } from '../../@types/Field'
import { Child } from '../../@types/Child'
import NestedFormIcon from './NestedFormIcon.vue'

@Component({
  components: { NestedFormIcon }
})
export default class NestedFormView extends Vue {
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