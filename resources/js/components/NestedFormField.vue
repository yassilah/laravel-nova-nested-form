<template>
  <div class="relative">
    <template v-if="shouldDisplay()">
      <template v-if="field.children.length > 0">
        <card
          :class="{ 'overflow-hidden': field.panel && !index }"
          :key="childIndex"
          v-for="(child, childIndex) in field.children"
        >
          <nested-form-header
            :child="child"
            :field="field"
          />
          <component
            :conditions="conditions"
            :errors="errors"
            :field="childField"
            :index="childIndex"
            :is="getComponentName(childField)"
            :key="childFieldIndex"
            :parent-index="index"
            :resource-id="child.resourceId"
            :resource-name="field.resourceName"
            :via-relationship="field.viaRelationship"
            :via-resource="field.viaResource"
            :via-resource-id="field.viaResourceId"
            @file-deleted="$emit('file-deleted')"
            v-for="(childField, childFieldIndex) in child.fields"
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
        >{{__('No related :pluralLabel yet.', { pluralLabel: field.pluralLabel })}}</p>
        <nested-form-add :field="field" />
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Mixins, Prop, Watch } from 'vue-property-decorator'
import { FormField, HandlesValidationErrors } from 'laravel-nova'

@Component
export default class NestedFormField extends Mixins(
  FormField,
  HandlesValidationErrors
) {
  @Prop() public resourceName!: string
  @Prop() public resourceId!: string | number
  @Prop() public field!: any
  @Prop({ default: () => ({}) }) public conditions: any
  @Prop() public index: number
  @Prop() public parentIndex: number

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
    this.field.children.forEach(child => {
      if (child[this.field.keyName]) {
        formData.append(
          `${child.attribute}[${this.field.keyName}]`,
          child[this.field.keyName]
        )
      }
      child.fields.forEach(field => field.fill(formData))
    })

    const regex = /(.*?(?:\[.*?\])+)(\[.*?)\]((?!\[).+)$/

    for (const [key, value] of formData.entries()) {
      if (key.match(regex)) {
        formData.append(key.replace(regex, '$1$2$3]'), value)
        formData.delete(key)
      }
    }
  }

  /**
   * Update the field's internal value.
   */
  public handleChange(value) {
    this.value = value
  }

  /**
   * Whether the current form should be displayed.
   */
  public shouldDisplay() {
    if (!this.field.displayIf) {
      return true
    }

    let shouldDisplay: boolean[] = []

    for (let i in this.field.displayIf) {
      const {
        attribute,
        is,
        isNot,
        isNotNull,
        isMoreThan,
        isLessThan,
        isMoreThanOrEqual,
        isLessThanOrEqual,
        includes
      } = this.field.displayIf[i]

      if (attribute) {
        const values = Object.keys(this.conditions)
          .filter(key => key.match(`^${attribute}$`))
          .map(key => this.conditions[key])

        if (typeof is !== 'undefined') {
          shouldDisplay.push(values.every(v => v === is))
        } else if (typeof isNot !== 'undefined') {
          shouldDisplay.push(values.every(v => v !== is))
        } else if (isNotNull) {
          shouldDisplay.push(values.every(v => Boolean(v)))
        } else if (typeof isMoreThan !== 'undefined') {
          shouldDisplay.push(values.every(v => v > isMoreThan))
        } else if (typeof isLessThan !== 'undefined') {
          shouldDisplay.push(values.every(v => v < isLessThan))
        } else if (typeof isMoreThanOrEqual !== 'undefined') {
          shouldDisplay.push(values.every(v => v >= isMoreThanOrEqual))
        } else if (typeof isLessThanOrEqual !== 'undefined') {
          shouldDisplay.push(values.every(v => v <= isLessThanOrEqual))
        } else if (includes) {
          shouldDisplay.push(values.every(v => v && includes.includes(v)))
        }
      }
    }

    return shouldDisplay.every(should => should)
  }

  /**
   * Get all the fields of the instance.
   */
  public setAllAttributeWatchers(instance: any) {
    if (
      instance.fieldAttribute &&
      typeof this.conditions[instance.fieldAttribute] === 'undefined'
    ) {
      this.field.displayIf
        .filter(field => instance.fieldAttribute.match(`^${field.attribute}$`))
        .forEach(field => {
          this.$set(this.conditions, instance.fieldAttribute, instance.value)
          instance.$watch('value', value => {
            this.$set(this.conditions, instance.fieldAttribute, value)
          })
        })
    }

    if (instance.$children) {
      instance.$children.map(child => this.setAllAttributeWatchers(child))
    }
  }

  /**
   * Get component name.
   */
  public getComponentName(child) {
    return child.prefixComponent ? `form-${child.component}` : child.component
  }

  /**
   * Set all the conditions.
   */
  @Watch('field.children')
  public setConditions() {
    if (this.field.displayIf) {
      this.setAllAttributeWatchers(this.$root)
    }
  }

  /**
   * On mounted.
   */
  public mounted() {
    if (this.field.displayIf) {
      this.setConditions()
    }
  }
}
</script>
