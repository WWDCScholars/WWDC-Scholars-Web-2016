<template lang="pug">
.form
  slot
</template>

<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'

@Component
export default class BaseForm extends Vue {}
</script>

<style lang="sass" scoped>
.form
  width: 100%
  display: grid
  gird-template-columns: 1fr 1fr
  grid-gap: 0 15px

  .group
    width: 100%
    margin-bottom: 30px
    grid-column: span 2
    display: grid
    grid-template-columns: 1fr 1fr
    grid-gap: 15px

    &.group-width-50
      grid-column: span 1

    h3, .field, p
      grid-column: span 2

    p
      margin: 0

    h3
      margin: 0
      margin-bottom: -5px
      font-size: 1em
      font-weight: 400

    .field
      &.field-width-50
        grid-column: span 1

  /deep/ .btn-cta
    grid-column: 2
    justify-self: end
    white-space: nowrap
    width: auto
</style>

<style lang="sass">
+form-colors
  $bg: dyn-temp('bg')
  $fg: dyn-temp('fg')

  .group h3
    color: $bg
</style>


