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
                       :resource-name="field.resourceName"
                       :resource-id="field.viaResourceId"
                       :is="'form-' + subfield.component" />
        </div>
        <!-- ACTUAL FIELDS -->

        <!-- DELETION MODAL -->
        <DeleteModal :resourceSingularName="$parent.field.name"
                     :resource="field"
                     @submit="$emit('remove', field)"
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
        },
        parent: {
            type: Object,
            required: true
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
                    this.$parent.getFieldValue(this.field.fields.find(field => field.original_attribute === key))
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
    }
}
</script>
