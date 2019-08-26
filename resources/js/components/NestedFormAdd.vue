<template>
  <div
    @click="addChild"
    class="block text-80 flex items-center justify-center hover:border-70 hover:text-70"
  >
    <icon
      class="cursor-pointer"
      type="add"
      viewBox="1.5 2 20 20"
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator'

@Component
export default class NestedFormAdd extends Vue {
  @Prop() public field!: any

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
    })

    schema.heading = schema.heading.replace(
      this.field.indexKey,
      this.field.children.length + 1
    )

    return schema
  }
}
</script>