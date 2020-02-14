import NestedFormField from './components/NestedFormField'
import NestedFormBelongsToField from './components/CustomFields/BelongsToField'
import NestedFormFileField from './components/CustomFields/FileField'
import NestedFormMorphToField from './components/CustomFields/MorphToField'

Nova.booting((Vue, router, store) => {
  Vue.component('form-nested-form', NestedFormField)
  Vue.component('form-nested-form-belongs-to-field', NestedFormBelongsToField)
  Vue.component('form-nested-form-morph-to-field', NestedFormMorphToField)
  Vue.component('form-nested-form-file-field', NestedFormFileField)
})
