<template>
    <div class="nested-field-container">
        <!-- NESTED FIELD -->
        <div :class="`nested-field-${field.type} ${index}`"
             v-for="(child, index) in children"
             :key="index">
            <NestedFieldWrapper :field="child"
                                :parentField="field"
                                :errors="errors"
                                :index="index" />
        </div>
        <!-- NESTED FIELD -->

        <!-- ADD A NEW NESTED RESOURCE -->
        <div class="p-2 text-90 border-b border-40 flex justify-center items-center bg-30"
             v-if="field.has_many || children.length === 0">
            <button type="button"
                    class="btn btn-sm text-white bg-primary-dark btn-default leading-none flex items-center"
                    @click="add()">Add a new {{field.name}}</button>
        </div>
        <!-- ADD A NEW NESTED RESOURCE -->

    </div>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import NestedFieldWrapper from './NestedFieldWrapper'

export default {
    mixins: [HandlesValidationErrors, FormField],

    components: { NestedFieldWrapper },

    computed: {
        children() {
            return this.field.children.filter(child => child.status !== 'removed')
        }
    },

    methods: {


        fill(formData, parentNestedField = null) {
            this.field.children.forEach(child => child.fill(formData, parentNestedField))
            if (!this.field.heading) {
                const obj = {}
                formData.forEach((v, k) => {
                    obj[k] = v
                })
                console.log(obj)
                throw new Error
            }
        },

        replaceIndexAttributeInSchema(field = this.field) {
            const newSchema = JSON.parse(JSON.stringify(field.schema))

            newSchema.fields.forEach(subfield => {

                if (subfield.schema) {
                    subfield.schema = this.replaceIndexAttributeInSchema(subfield)
                    //subfield.schema.heading = subfield.heading.replace(this.field.INDEX, this.field.children.length + 1)
                }

                if (subfield.attribute) {
                    subfield.attribute = subfield.attribute.replace(this.field.INDEX, this.field.children.length)
                }
            })

            return newSchema
        },
        add() {
            this.field.children.push(this.replaceIndexAttributeInSchema())
        }
    }
}
</script>