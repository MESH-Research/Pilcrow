<template>
  <div class="col-12 q-pb-md">
    <q-card flat bordered>
      <q-card-section :horizontal="true">
        <q-card-section class="q-pa-none col-2" />
        <q-card-section class="q-pa-none col">
          <q-card-section>
            <q-chip class="float-right">
              {{ $t(`admin.users.details.roles.${row.my_role.toLowerCase()}`) }}
            </q-chip>
            <div class="text-weight-medium">
              {{ row.title }}
            </div>
            <div class="text-overline">{{ row.publication.name }}</div>
            <div>{{ row.created_at }}</div>
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat icon="mdi-eye">View Submission</q-btn>
          </q-card-actions>
        </q-card-section>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup lang="ts">
interface SubmissionRow {
  my_role: string
  title: string
  publication: { name: string }
  created_at: string
}

interface Props {
  row: SubmissionRow
  dense?: boolean
}

withDefaults(defineProps<Props>(), {
  dense: false
})
</script>
