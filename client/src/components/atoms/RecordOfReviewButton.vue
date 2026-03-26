<template>
  <q-btn
    label="Record of Review"
    color="accent"
    icon="exit_to_app"
    :to="{
      name: 'record_of_review_details',
      params: { id: 100 }
    }"
  />
</template>
