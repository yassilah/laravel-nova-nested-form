<script>
import MorphToField from '@/components/Form/MorphToField.vue'
import storage from '@/storage/MorphToFieldStorage'
import { Errors } from 'laravel-nova'

export default {
  mixins: [MorphToField],
  methods: {
    /**
     * Get the resources that may be related to this resource.
     */
    getAvailableResources(search = '') {
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
}
</script>