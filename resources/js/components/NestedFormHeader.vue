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

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator'
import { Child } from '../../@types/Child'
import { Field } from '../../@types/Field'

@Component
export default class NestedFormHeader extends Vue {
  @Prop() public child: Child
  @Prop() public field: Field

  /**
   * Get the heading.
   */
  get heading() {
    return this.child.heading
      ? this.child.heading.replace(
          new RegExp(
            `${this.field.wrapLeft}(.*?)(?:\\|(.*?))?${this.field.wrapRight}`,
            'g'
          ),
          (match, name, defaultValue = '') => {
            const field = this.child.fields.find(field => field.name === name)
            return field ? field.value : defaultValue
          }
        )
      : null
  }
}
</script>