<template>
  <ul>
    <li
      v-for="error in errors"
      :key="error.$validator"
      data-cy="name_field_error"
    >
      {{ $t(`${prefix}.${getErrorMessageKey(error)}`) }}
    </li>
  </ul>
</template>

<script>
import {getErrorMessageKey} from "src/use/validationHelpers";

export default {
    props: {
        errors: {
            type: Array,
            required: true
        },
        prefix: {
            type: String,
            required: false,
            default: ''
        }
    },
    setup () {
        return {getErrorMessageKey}
    }
}
</script>

<style scoped>
li {
  display: block;
}
ul {
  margin-block: 0;
  padding-inline: 0;
}

</style>