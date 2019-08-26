(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["form-nested-form"],{

/***/ "./node_modules/ts-loader/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/NestedFormField.vue?vue&type=script&lang=ts&":
/*!******************************************************************************************************************************************************************!*\
  !*** ./node_modules/ts-loader??ref--11!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/NestedFormField.vue?vue&type=script&lang=ts& ***!
  \******************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var vue_property_decorator_1 = __webpack_require__(/*! vue-property-decorator */ "./node_modules/vue-property-decorator/lib/vue-property-decorator.js");
var laravel_nova_1 = __webpack_require__(/*! laravel-nova */ "./node_modules/laravel-nova/dist/index.js");
var NestedFormField = /** @class */ (function (_super) {
    __extends(NestedFormField, _super);
    function NestedFormField() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        /**
         * Data.
         */
        _this.value = '';
        return _this;
    }
    /*
     * Set the initial, internal value for the field.
     */
    NestedFormField.prototype.setInitialValue = function () {
        this.value = this.field.value || '';
    };
    /**
     * Fill the given FormData object with the field's internal value.
     */
    NestedFormField.prototype.fill = function (formData) {
        formData.append(this.field.attribute, this.value || '');
    };
    /**
     * Update the field's internal value.
     */
    NestedFormField.prototype.handleChange = function (value) {
        this.value = value;
    };
    /**
     * Get component name.
     */
    NestedFormField.prototype.getComponentName = function (child) {
        return child.prefixComponent ? "form-" + child.component : child.component;
    };
    __decorate([
        vue_property_decorator_1.Prop()
    ], NestedFormField.prototype, "resourceName", void 0);
    __decorate([
        vue_property_decorator_1.Prop()
    ], NestedFormField.prototype, "resourceId", void 0);
    __decorate([
        vue_property_decorator_1.Prop()
    ], NestedFormField.prototype, "field", void 0);
    NestedFormField = __decorate([
        vue_property_decorator_1.Component
    ], NestedFormField);
    return NestedFormField;
}(vue_property_decorator_1.Mixins(laravel_nova_1.FormField, laravel_nova_1.HandlesValidationErrors)));
exports.default = NestedFormField;


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/NestedFormField.vue?vue&type=template&id=5ea62844&":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/NestedFormField.vue?vue&type=template&id=5ea62844& ***!
  \******************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "relative" },
    [
      _vm.field.panel
        ? _c("Actions", { attrs: { field: _vm.field } })
        : _vm._e(),
      _vm._v(" "),
      _vm.field.children.length > 0
        ? _vm._l(_vm.field.children, function(child, index) {
            return _c(
              "div",
              {
                key: index,
                staticClass: "card",
                class: { "overflow-hidden": !index }
              },
              [
                _c("div", { staticClass: "bg-60 p-4 text-white font-bold" }, [
                  _vm._v(
                    "#" +
                      _vm._s(index + 1) +
                      " " +
                      _vm._s(_vm.field.singularLabel)
                  )
                ]),
                _vm._v(" "),
                _vm._l(child.fields, function(field, index) {
                  return _c(
                    _vm.getComponentName(field),
                    _vm._b(
                      { key: index, tag: "component" },
                      "component",
                      { field: field },
                      false
                    )
                  )
                })
              ],
              2
            )
          })
        : [
            _c(
              "p",
              { staticClass: "text-center p-8 font-bold text-80 text-xl" },
              [
                _vm._v(
                  _vm._s(
                    _vm.__("No :pluralLabel yet.", {
                      pluralLabel: _vm.field.pluralLabel
                    })
                  )
                )
              ]
            )
          ]
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/components/NestedFormField.vue":
/*!*****************************************************!*\
  !*** ./resources/js/components/NestedFormField.vue ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _NestedFormField_vue_vue_type_template_id_5ea62844___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./NestedFormField.vue?vue&type=template&id=5ea62844& */ "./resources/js/components/NestedFormField.vue?vue&type=template&id=5ea62844&");
/* harmony import */ var _NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./NestedFormField.vue?vue&type=script&lang=ts& */ "./resources/js/components/NestedFormField.vue?vue&type=script&lang=ts&");
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_1__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_1__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_1__["default"],
  _NestedFormField_vue_vue_type_template_id_5ea62844___WEBPACK_IMPORTED_MODULE_0__["render"],
  _NestedFormField_vue_vue_type_template_id_5ea62844___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/NestedFormField.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/NestedFormField.vue?vue&type=script&lang=ts&":
/*!******************************************************************************!*\
  !*** ./resources/js/components/NestedFormField.vue?vue&type=script&lang=ts& ***!
  \******************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_ts_loader_index_js_ref_11_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/ts-loader??ref--11!../../../node_modules/vue-loader/lib??vue-loader-options!./NestedFormField.vue?vue&type=script&lang=ts& */ "./node_modules/ts-loader/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/NestedFormField.vue?vue&type=script&lang=ts&");
/* harmony import */ var _node_modules_ts_loader_index_js_ref_11_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_ts_loader_index_js_ref_11_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_ts_loader_index_js_ref_11_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_ts_loader_index_js_ref_11_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_ts_loader_index_js_ref_11_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_script_lang_ts___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/NestedFormField.vue?vue&type=template&id=5ea62844&":
/*!************************************************************************************!*\
  !*** ./resources/js/components/NestedFormField.vue?vue&type=template&id=5ea62844& ***!
  \************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_template_id_5ea62844___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./NestedFormField.vue?vue&type=template&id=5ea62844& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/NestedFormField.vue?vue&type=template&id=5ea62844&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_template_id_5ea62844___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_NestedFormField_vue_vue_type_template_id_5ea62844___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);