<template>
    <div class="nested-field">
        <!-- HEADING -->
        <div class="p-4 text-90 border-b border-40 flex justify-between items-center bg-30">
            <h1 class="font-normal text-xl capitalize">{{heading}}</h1>
            <div class="flex justify-between items-center">

                <div @click="toggleVisibility"
                     class="appearance-none cursor-pointer text-70 hover:text-primary mr-3 items-center flex">
                    <icon type="view"
                          width="22"
                          height="18"
                          view-box="0 0 22 16" />
                </div>

                <div @click="showDeleteModal = true"
                     class="appearance-none cursor-pointer text-70 hover:text-danger mr-3 items-center flex">
                    <icon type="delete" />
                </div>
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

export default {
    mixins: [HandlesValidationErrors, FormField],


    components: { DeleteModal },


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
