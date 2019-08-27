<script lang="ts">
import { Component, Vue, Mixins } from 'vue-property-decorator'
import BaseMorphToField from '@/components/Form/MorphToField.vue'
import storage from '@/storage/MorphToFieldStorage'
import { Errors } from 'laravel-nova'

declare const Nova: any

@Component
export default class MorphToField extends Mixins(BaseMorphToField) {
  /**
   * Get the resources that may be related to this resource.
   */
  public getAvailableResources(search = '') {
    return storage
      .fetchAvailableResources(
        this.resourceName,
        this.field.originalAttribute,
        this.queryParams
      )
      .then(({ data: { resources, softDeletes, withTrashed } }) => {
        if (this.initializingWithExistingResource || !this.isSearchable) {
          this.withTrashed = withTrashed
        }

        this.initializingWithExistingResource = false
        this.availableResources = resources
        this.softDeletes = softDeletes
      })
  }
}
</script>