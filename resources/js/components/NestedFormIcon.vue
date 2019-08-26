<template>
  <div
    :class="`hover:border-${hoverColor} hover:text-${hoverColor}`"
    @click="$emit('click')"
    class="block text-80 flex items-center justify-center h-8 w-8"
  >
    <slot />
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator'

@Component
export default class NestedFormIcon extends Vue {
  @Prop() public hoverColor: string
}
</script>