import 'reflect-metadata'
import NestedFormField from './components/NestedFormField'
import NestedFormIcon from './components/NestedFormIcon'
import NestedFormHeader from './components/NestedFormHeader'
import NestedFormAdd from './components/NestedFormAdd'
import NestedFormRemove from './components/NestedFormRemove'
import NestedFormView from './components/NestedFormView'
import NestedFormBelongsToField from './components/CustomFields/BelongsToField'
import NestedFormFileField from './components/CustomFields/FileField'
import NestedFormMorphToField from './components/CustomFields/MorphToField'

Nova.booting((Vue, router, store) => {
  Vue.component('form-nested-form', Vue.extend(NestedFormField))
  Vue.component('form-nested-form-belongs-to-field', Vue.extend(NestedFormBelongsToField))
  Vue.component('form-nested-form-morph-to-field', Vue.extend(NestedFormMorphToField))
  Vue.component('form-nested-form-file-field', Vue.extend(NestedFormFileField))
  Vue.component('nested-form-icon', Vue.extend(NestedFormIcon))
  Vue.component('nested-form-header', Vue.extend(NestedFormHeader))
  Vue.component('nested-form-add', Vue.extend(NestedFormAdd))
  Vue.component('nested-form-remove', Vue.extend(NestedFormRemove))
  Vue.component('nested-form-view', Vue.extend(NestedFormView))
})
