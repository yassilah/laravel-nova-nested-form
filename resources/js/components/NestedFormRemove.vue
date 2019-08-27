<template>
  <nested-form-icon
    @click="removeChild"
    hover-color="danger"
    v-if="field.children.length > field.min"
  >
    <icon
      class="cursor-pointer"
      type="delete"
      viewBox="0 0 20 20"
    />
  </nested-form-icon>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator'
import { Field } from '../../@types/Field'
import { Child } from '../../@types/Child'

@Component
export default class NestedFormRemove extends Vue {
  @Prop() public field!: Field
  @Prop() public child!: Child

  /**
   * Remove a child.
   */
  public removeChild() {
    this.field.children.splice(this.field.children.indexOf(this.child), 1)
  }
}
</script>