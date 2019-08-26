<template>
  <div class="bg-50 p-4 items-center text-90 flex justify-between">
    <div v-html="child.heading" />
    <div class="flex">
      <nested-form-visibility
        :child="child"
        class="mx-2"
      />
      <nested-form-add
        :field="field"
        class="mx-2"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator'
import { Child } from '../../@types/Child'
import { Field } from '../../@types/Field'

@Component
export default class Header extends Vue {
  @Prop() public child: Child
  @Prop() public field: Field

  /**
   * Toggle the visibility.
   */
  public toggleVisibility() {
    this.child.opened = !this.child.opened
  }
}
</script>