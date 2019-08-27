<script lang="ts">
import { Component, Vue, Mixins } from 'vue-property-decorator'
import BaseFileField from '@/components/Form/FileField.vue'
import { Errors } from 'laravel-nova'

declare const Nova: any

@Component
export default class FileField extends Mixins(BaseFileField) {
  /**
   * Remove the linked file from storage
   */
  async removeFile() {
    this.uploadErrors = new Errors()

    const {
      //@ts-ignore
      resourceName,
      resourceId,
      relatedResourceName,
      relatedResourceId,
      viaRelationship
    } = this

    //@ts-ignore
    const attribute = this.field.originalAttribute

    const uri = this.viaRelationship
      ? `/nova-api/${resourceName}/${resourceId}/${relatedResourceName}/${relatedResourceId}/field/${attribute}?viaRelationship=${viaRelationship}`
      : `/nova-api/${resourceName}/${resourceId}/field/${attribute}`

    try {
      await Nova.request().delete(uri)
      this.closeRemoveModal()
      this.deleted = true
      this.$emit('file-deleted')
    } catch (error) {
      this.closeRemoveModal()
      if (error.response.status == 422) {
        this.uploadErrors = new Errors(error.response.data.errors)
      }
    }
  }
}
</script>