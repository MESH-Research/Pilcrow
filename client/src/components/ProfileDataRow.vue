<template>
  <div class="row q-pa-md">
    <div class="col-xs-3 col-md-2 text-weight-medium">
      {{ label }}
    </div>
    <div class="col-xs-8 col-md-2">
      {{ value }}
    </div>
    <q-btn flat icon="edit" size="sm" clickable @click="$emit('editClick')" />
    <q-separator class="q-mt-md" />
  </div>
</template>

<script>
export default {
  name: "ProfileDataRow",
  props: {
    label: {
      type: String,
      default: "",
    },
    value: {
      type: String,
      default: "",
    },
  },
};
</script>
