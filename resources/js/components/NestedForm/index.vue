<template>
    <div class="nested-field-container">
        <!-- NESTED FIELD -->
        <div :class="`nested-field-${field.type} ${index}`"
             v-for="(child, index) in children"
             :key="index">

            <NestedFieldWrapper :field="child"
                                :parent="field"
                                :errors="errors"
                                :index="index"
                                @remove="remove" />
        </div>
        <!-- NESTED FIELD -->

        <!-- ADD A NEW NESTED RESOURCE -->
        <div class="p-2 text-90 border-b border-40 flex justify-center items-center bg-30"
             v-if="displayAddButton">
            <button type="button"
                    class="btn btn-sm text-white bg-primary-dark btn-default leading-none flex items-center"
                    @click="add()">{{ __('Add a new :resourceSingularName', { resourceSingularName: field.singularName }) }}</button>
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
            return this.field.children.filter(child => child[this.field.STATUS] !== this.field.REMOVED)
        },
        displayAddButton() {
            return (this.field.has_many || this.children.length === 0) && (this.field.max > 0 ? this.field.max > this.children.length : true)
        }
    },

    methods: {


        fill(formData, parentNestedField) {
            this.field.children.forEach(child => this.fillChildren(child, formData, parentNestedField))

            // if (this.field.name === 'Post') {
            //     const obj = {}
            //     formData.forEach((v, k) => {
            //         obj[k] = v
            //     })
            //     console.log(obj)
            //     throw new Error
            // }
        },

        replaceIndexesInSchema(field) {
            const schema = JSON.parse(JSON.stringify(field.schema))

            schema.fields.forEach(child => {

                if (child.schema) {
                    child.schema.opened = false
                    child.schema = this.replaceIndexesInSchema(child)
                }

                if (child.attribute) {
                    child.attribute = child.attribute.replace(this.field.INDEX, this.field.children.length)
                }
            })

            schema.attribute = schema.attribute.replace(this.field.INDEX, this.children.length)
            schema.heading = schema.heading.replace(this.field.INDEX, this.children.length + 1)

            return schema
        },

        /**
         * This adds a resource to the children
         */
        add() {
            this.field.children.push(this.replaceIndexesInSchema(this.field))
        },

        /**
         * This removes a resource from the children
         */
        remove(child) {
            if (child[this.field.STATUS] === this.field.CREATED) {
                this.field.children.splice(this.field.children.indexOf(child), 1)
            } else {
                child[this.field.STATUS] = this.field.REMOVED
            }

        },

        /**
         * This creates a temp FormData object
         * to get the actual value of an attribute
         * and therefore know whether it should be updated
         */
        getFieldValue(field) {
            if (!field) return ''
            else if (!field.fill) return field.value || ''

            const temp = new FormData
            field.fill(temp)
            return temp.get(field.attribute) || null
        },

        /**
         * This filters any subfields of a given field
         * that should be updated
         */
        filterUpdatedSubFields(child) {
            return child.fields.filter(field => !field.children && (field.component === 'belongs-to-field' ?
                field.belongsToId != parseFloat(this.getFieldValue(field)) :
                field.value != this.getFieldValue(field)))
        },

        /**
         * This actually appends the current field to 
         * the formData. It also adds the status and __preffix__
         * to know what kind of request this field should send (update/create/remove)
         * and keep track of the client-side id of the input field for validation errors.
         */
        appendToFormData(child, formData) {
            formData.append(`${child.attribute}[${this.field.PREFIX}]`, child.attribute)
            formData.append(`${child.attribute}[${this.field.STATUS}]`, child[this.field.STATUS])

            if (child[this.field.STATUS]) {
                if (child[this.field.STATUS] !== this.field.CREATED) {
                    formData.append(`${child.attribute}[id]`, child.resourceId)
                }

                if (![this.field.UNCHANGED, this.field.REMOVED].includes(child[this.field.STATUS])) {
                    child.fields.filter(field => !field.children).forEach(field => formData.append(field.attribute, this.getFieldValue(field)))
                }
            }
        },

        /**
         * This overrides the default fill method of Nova to only target fields that
         * actually need to be sent and trigger the fill methods of sub nested-field components.
         */
        fillChildren(child, formData, parent = null) {
            if (child[this.field.STATUS] !== this.field.REMOVED) {
                child.fields.filter(field => field.children).forEach(field => field.fill(formData, child))

                if (!child[this.field.STATUS] && this.filterUpdatedSubFields(child).length > 0) {
                    child[this.field.STATUS] = this.field.UPDATED
                }
            }

            if (child[this.field.STATUS]) {
                this.appendToFormData(child, formData)

                if (parent) {
                    parent[this.field.STATUS] = parent[this.field.STATUS] || this.field.UNCHANGED
                }
            }
        },
    },
    created() {
        if (this.field.min > 0 && this.field.children.length < this.field.min) {
            const diff = this.field.min - this.field.children.length
            for (let i = 0; i < diff; i++) {
                this.add()
            }
        }
    }
}
</script>