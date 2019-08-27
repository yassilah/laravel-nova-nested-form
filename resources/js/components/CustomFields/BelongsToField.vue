<script lang="ts">
import { Component, Vue, Mixins } from 'vue-property-decorator'
import BaseBelongsToField from '@/components/Form/BelongsToField.vue'
import storage from '@/storage/BelongsToFieldStorage'

@Component
export default class BelongsToField extends Mixins(BaseBelongsToField) {
  /**
   * Get the resources that may be related to this resource.
   */
  public getAvailableResources() {
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