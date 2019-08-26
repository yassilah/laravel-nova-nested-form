import 'reflect-metadata'
import NestedFormField from './components/NestedFormField'
import NestedFormAdd from './components/NestedFormAdd'
import NestedFormRemove from './components/NestedFormRemove'
import NestedFormView from './components/NestedFormView'
import NestedFormHeader from './components/NestedFormHeader'

Nova.booting((Vue, router, store) => {
  Vue.component('form-nested-form', Vue.extend(NestedFormField))
  Vue.component('nested-form-add', Vue.extend(NestedFormAdd))
  Vue.component('nested-form-remove', Vue.extend(NestedFormRemove))
  Vue.component('nested-form-view', Vue.extend(NestedFormView))
  Vue.component('nested-form-header', Vue.extend(NestedFormHeader))
})
