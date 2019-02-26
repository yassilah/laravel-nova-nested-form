<template>
  <modal @modal-close="handleClose">
    <form @submit.prevent="handleConfirm"
          class="bg-white rounded-lg shadow-lg overflow-hidden"
          style="width: 460px">
      <slot>
        <div class="p-8">
          <heading :level="2"
                   class="mb-6">{{ __('Remove a :resourceSingularName', { resourceSingularName}) }}</heading>
          <p class="text-80 leading-normal">{{__('Are you sure you want to remove this :resourceSingularName?', { resourceSingularName })}}</p>
        </div>
      </slot>
      <div class="bg-30 px-6 py-3 flex">
        <div class="ml-auto">
          <button type="button"
                  @click.prevent="handleClose"
                  class="btn text-80 font-normal h-9 px-3 mr-3 btn-link">{{__('Cancel')}}</button>
          <button type="submit"
                  ref="confirmButton"
                  class="btn btn-default btn-danger">{{__('Remove')}}</button>
        </div>
      </div>
    </form>
  </modal>
</template>

<script>
export default {
  props: {
    resourceSingularName: {
      type: String,
      required: true
    }
  },
  methods: {
    handleClose() {
      this.$emit('close')
    },

    handleConfirm() {
      this.$emit('submit')
      this.$emit('close')
    }
  },
  mounted() {
    this.$refs.confirmButton.focus()
  }
}
</script>
