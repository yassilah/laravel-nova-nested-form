<template>
  <div class="relative">
    <template v-if="field.children.length > 0">
      <card
        :class="{ 'overflow-hidden': field.panel && !index }"
        class="my-4"
        :key="index"
        v-for="(child, index) in field.children"
      >
        <nested-form-header
          :child="child"
          :field="field"
        />
        <component
          :is="getComponentName(field)"
          :key="index"
          v-bind="{ field }"
          v-for="(field, index) in child.fields"
          v-show="child.opened"
        />
      </card>
    </template>

    <div
      class="flex flex-col p-8 items-center justify-center"
      v-else
    >
      <p
        class="text-center my-4 font-bold text-80 text-xl"
      >{{__('No :pluralLabel yet.', { pluralLabel: field.pluralLabel })}}</p>
      <nested-form-add :field="field" />
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Mixins, Prop } from 'vue-property-decorator'
import { FormField, HandlesValidationErrors } from 'laravel-nova'

@Component
export default class NestedFormField extends Mixins(
  FormField,
  HandlesValidationErrors
) {
  @Prop() public resourceName!: string
  @Prop() public resourceId!: string | number
  @Prop() public field!: any

  /**
   * Value.
   */
  public value: any = ''

  /*
   * Set the initial, internal value for the field.
   */
  public setInitialValue() {
    this.value = this.field.value || ''
  }

  /**
   * Fill the given FormData object with the field's internal value.
   */
  public fill(formData) {
    formData.append(this.field.attribute, this.value || '')
  }

  /**
   * Update the field's internal value.
   */
  public handleChange(value) {
    this.value = value
  }

  /**
   * Get component name.
   */
  public getComponentName(child) {
    return child.prefixComponent ? `form-${child.component}` : child.component
  }
}
</script>
