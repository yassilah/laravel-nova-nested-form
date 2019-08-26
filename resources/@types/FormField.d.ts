import { Field } from './Field'
import { Vue, Component } from 'vue-property-decorator'
import { VueConstructor } from 'vue'

export interface FormField extends VueConstructor {
  resourceName: string
  field: Field
  value: any
  mounted(): void
  destroyed(): void

  /**
   * Determine if the field is in readonly mode
   */
  isReadonly: boolean

  /*
   * Set the initial value for the field
   */
  setInitialValue(): void

  /**
   * Provide a function that fills a passed FormData object with the
   * field's internal value attribute
   */
  fill(formData: FormData): void

  /**
   * Update the field's internal value
   */
  handleChange(value: any): void
}
