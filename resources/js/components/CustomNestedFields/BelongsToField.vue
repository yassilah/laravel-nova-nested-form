<script>
import BelongsToField from '@/components/Form/BelongsToField'
import storage from '@/storage/BelongsToFieldStorage'

export default {
  mixins: [BelongsToField],

  methods: {
    /**
     * Fill the forms formData with details from this field
     */
    fill(formData) {
      formData.append(
        this.field.attribute,
        this.selectedResource ? this.selectedResource.value : ''
      )

      formData.append(
        this.field.attribute.replace(/]$/, '_trashed]'),
        this.withTrashed
      )
    },

    /**
     * Get the resources that may be related to this resource.
     */
    getAvailableResources() {
      return storage
        .fetchAvailableResources(
          this.resourceName,
          this.field.original_attribute,
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
