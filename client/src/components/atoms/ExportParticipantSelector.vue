<template>
  <q-card>
    <q-card-section>
      <h4 class="q-my-none text-bold">
        {{ $t("export.participants.title") }}
      </h4>
      <div v-if="commenters.length == 0">
        <p>{{ $t("export.participants.no_commenters") }}</p>
      </div>
      <div v-else>
        <p>{{ $t("export.participants.byline") }}</p>
        <div class="q-gutter-sm q-mb-sm">
          <q-btn
            size="sm"
            :label="$t('export.participants.select_all')"
            @click="updateSelected([...commenters])"
          />
          <q-btn
            size="sm"
            :label="$t('export.participants.select_none')"
            @click="updateSelected([])"
          />
        </div>
        <q-list>
          <q-item
            v-for="commenter in commenters"
            :key="commenter.id"
            tag="label"
          >
            <q-item-section avatar>
              <q-checkbox
                v-model="selectedModel"
                :aria-labelledby="`commenter_label_${commenter.id}`"
                :val="commenter"
              />
            </q-item-section>
            <q-item-section top avatar>
              <avatar-image :user="commenter" rounded />
            </q-item-section>
            <q-item-section>
              <q-item-label :id="`commenter_label_${commenter.id}`">{{
                commenter.display_label
              }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment exportParticipantSelector on Submission {
    commenters(type: $commenterType) {
      id
      display_label
      ...avatarImage
    }
  }
`)
</script>

<script setup lang="ts">
import AvatarImage from "./AvatarImage.vue"
import { computed, ref, watch } from "vue"
import type { exportParticipantSelectorFragment } from "src/graphql/generated/graphql"

type Commenter = NonNullable<
  exportParticipantSelectorFragment["commenters"]
>[number]

interface Props {
  submission: exportParticipantSelectorFragment
}

interface Emits {
  "update:modelValue": [value: Commenter[]]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()
const commenters = computed(() => props.submission.commenters ?? [])
const selected = ref<Commenter[]>([])

const selectedModel = computed({
  get: () => selected.value,
  set: (val: Commenter[]) => updateSelected(val)
})

function updateSelected(val: Commenter[]) {
  selected.value = val
  emit("update:modelValue", val)
}

// Reset selection when commenters change
watch(
  commenters,
  (newCommenters) => {
    if (selected.value.length === 0 && newCommenters.length > 0) {
      updateSelected([...newCommenters])
    }
  },
  { immediate: true }
)
</script>
