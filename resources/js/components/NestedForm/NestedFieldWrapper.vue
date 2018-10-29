<template>
    <div class="nested-field">
        <!-- HEADING -->
        <div class="p-4 text-90 border-b border-40 flex justify-between items-center bg-30">
            <h1 class="font-normal text-xl capitalize">{{heading}}</h1>
            <div class="flex justify-between items-center">
                <a @click="toggleVisibility"
                   class="cursor-pointer select-none mx-2">
                    <Caret />
                </a>
                <a class="text-2xl font-bold text-danger cursor-pointer select-none rounded-full mx-2"
                   @click="showDeleteModal = true">x</a>
            </div>
        </div>
        <!-- HEADING -->

        <!-- ACTUAL FIELDS -->
        <div v-show="visible">
            <component v-for="(subfield, index) in field.fields"
                       :key="index"
                       :field="subfield"
                       :errors="errors"
                       :resource-name="field.viaRelationship"
                       :resource-id="field.viaResourceId"
                       :is="'form-' + subfield.component" />
        </div>
        <!-- ACTUAL FIELDS -->

        <!-- DELETION MODAL -->
        <DeleteModal :resourceSingularName="field.name"
                     :resource="field"
                     @submit="remove"
                     @close="showDeleteModal = false"
                     v-if="showDeleteModal" />
        <!-- DELETION MODAL -->

    </div>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import DeleteModal from './../Modals/Delete'
import Caret from './../Caret'

export default {
    mixins: [HandlesValidationErrors, FormField],


    components: { DeleteModal, Caret },


    props: {
        field: {
            type: Object,
            required: true
        },
        index: {
            type: Number | Boolean,
            default: false
        }
    },


    data() {
        return {
            visible: false,
            showDeleteModal: false
        }
    },

    computed: {
        heading() {
            return this.field.heading.replace(/{{(.*?)}}/g, (val, key) => {
                return key === 'index' ? this.index + 1 || '' : key === 'id' ? this.field.resourceId || '' :
                    this.getFieldValue(this.field.fields.find(field => field.original_attribute === key))
            }).replace(/ - $/, '')
        }
    },


    methods: {
        /**
         * This toggles the visibility of the 
         * content of the related resource
         */
        toggleVisibility() {
            this.visible = !this.visible
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
        filterUpdatedSubFields(field = this.field) {
            return field.fields.filter(field => !field.children && (field.component === 'belongs-to-field' ?
                field.belongsToId != parseFloat(this.getFieldValue(field)) :
                field.value != this.getFieldValue(field)))
        },

        /**
         * This actually appends the current field to 
         * the formData. It also adds the status and __preffix__
         * to know what kind of request this field should send (update/create/remove)
         * and keep track of the client-side id of the input field for validation errors.
         */
        appendToFormData(formData) {
            formData.append(`${this.field.attribute}[prefix]`, this.field.attribute)
            formData.append(`${this.field.attribute}[status]`, this.field.status)

            if (this.field.status !== 'created') {
                formData.append(`${this.field.attribute}[id]`, this.field.resourceId)
            }

            if (this.field.status !== 'unchanged') {
                this.field.fields.filter(field => !field.children)
                    .forEach(field => formData.append(field.attribute, this.getFieldValue(field)))
            }
        },

        /**
         * This overrides the default fill method of Nova to only target fields that
         * actually need to be sent and trigger the fill methods of sub nested-field components.
         */
        fill(formData, parentNestedField = null) {
            this.field.fields.filter(field => field.children).forEach((child, index) => child.fill(formData, this.field))

            if (this.field.status === 'unchanged' && this.filterUpdatedSubFields().length > 0) {
                this.field.status = 'updated'
            }

            this.appendToFormData(formData)
            if (parentNestedField) parentNestedField.status = parentNestedField.status || 'unchanged'
        },

        /**
         * This removes a resource from the data array
         */
        remove() {
            if (this.field.status === 'created') {
                this.field.children.splice(this.field.children.indexOf(this.field), 1)
            } else {
                this.field.status = 'removed'
            }
        }
    }
}
</script>
