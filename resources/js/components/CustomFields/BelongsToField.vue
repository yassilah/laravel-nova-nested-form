<script>
import BelongsToField from '@/components/Form/BelongsToField.vue'
import storage from '@/storage/BelongsToFieldStorage'

export default {
  mixins: [BelongsToField],
  methods: {
    /**
     * Get the resources that may be related to this resource.
     */
    getAvailableResources() {
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