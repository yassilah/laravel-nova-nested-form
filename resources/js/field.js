import 'reflect-metadata'
import NestedFormField from './components/NestedFormField'
import NestedFormAdd from './components/NestedFormAdd'
import NestedFormVisibility from './components/NestedFormVisibility'
import NestedFormHeader from './components/NestedFormHeader'

Nova.booting((Vue, router, store) => {
  Vue.component('form-nested-form', Vue.extend(NestedFormField))
  Vue.component('nested-form-add', Vue.extend(NestedFormAdd))
  Vue.component('nested-form-visibility', Vue.extend(NestedFormVisibility))
  Vue.component('nested-form-header', Vue.extend(NestedFormHeader))
})
