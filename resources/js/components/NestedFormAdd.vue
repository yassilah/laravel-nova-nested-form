<template>
  <nested-form-icon
    @click="addChild"
    hover-color="success"
    v-if="field.max === 0 || field.children.length < field.max"
  >
    <icon
      class="cursor-pointer"
      type="add"
      viewBox="1.5 2 20 20"
    />
  </nested-form-icon>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator'
import { Field } from '../../@types/Field'

@Component
export default class NestedFormAdd extends Vue {
  @Prop() public field!: Field

  /**
   * Add a new child.
   */
  public addChild() {
    this.field.children.push(this.replaceIndexesInSchema(this.field))
  }

  /**
   * This replaces the "{{index}}" values of the schema to
   * their actual index.
   *
   */
  public replaceIndexesInSchema(field) {
    const schema = JSON.parse(JSON.stringify(field.schema))

    schema.fields.forEach(field => {
      if (field.schema) {
        field.schema = this.replaceIndexesInSchema(field)
      }
      if (field.attribute) {
        field.attribute = field.attribute.replace(
          this.field.indexKey,
          this.field.children.length
        )
      }
      if (field.displayIf) {
        field.displayIf = JSON.parse(
          JSON.stringify(field.displayIf).replace(
            new RegExp(this.field.indexKey, 'g'),
            this.field.children.length.toString()
          )
        )
      }
    })

    schema.heading = schema.heading.replace(
      this.field.indexKey,
      this.field.children.length + 1
    )

    return schema
  }

  /**
   * On created.
   */
  public created() {
    for (let i = this.field.children.length; i < this.field.min; i++) {
      this.addChild()
    }
  }
}
</script>