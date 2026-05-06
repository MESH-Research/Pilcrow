<template>
  <q-td :props="scope" :dense="scope.dense" class="text-right">
    <span
      v-if="total > 0"
      class="row items-center justify-end q-gutter-xs"
      role="list"
      :aria-label="
        $t('publication.manage.users.submission_icon_list', { n: total })
      "
    >
      <span
        v-for="sub in submissions"
        :key="sub.id"
        role="listitem"
        :class="[
          'submission-chip',
          `bg-${styleFor(sub.status).color}`,
          styleFor(sub.status).textClass,
          styleFor(sub.status).pattern
        ]"
        :title="$t(`submission.status.${sub.status}`)"
        :aria-label="$t(`submission.status.${sub.status}`)"
      >
        <q-icon name="description" size="sm" class="pattern-text-mask" />
        <span
          :class="[
            'category-badge',
            `bg-${styleFor(sub.status).color}`,
            styleFor(sub.status).textClass
          ]"
          aria-hidden="true"
        >
          <q-icon :name="styleFor(sub.status).icon" size="10px" />
        </span>
      </span>
      <!-- Whenever the total exceeds what the preview returned, show
           an overflow chip (+N) so the row reads as "5 icons + more"
           rather than collapsing to a bare number. -->
      <span
        v-if="overflow > 0"
        class="submission-chip overflow-chip bg-grey-3 text-grey-8"
        :title="
          $t('publication.manage.users.submission_icon_overflow', {
            n: overflow
          })
        "
        :aria-label="
          $t('publication.manage.users.submission_icon_overflow', {
            n: overflow
          })
        "
      >
        +{{ overflow }}
      </span>
    </span>
    <span v-else class="text-grey-5" aria-label="no submissions">—</span>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { statusStyleMap } from "src/pages/Publication/components/statusCategories"
import type { QTableBodyCellScope } from "../QueryTable.vue"

interface SubmissionSummary {
  id: string
  status: string
}

interface SubmissionPreview {
  paginatorInfo: { total: number }
  data: SubmissionSummary[]
}

interface Props {
  scope: QTableBodyCellScope
}
const props = defineProps<Props>()

const value = computed(() => props.scope.value)

const paginator = computed<SubmissionPreview | null>(() =>
  value.value && typeof value.value === "object" && "data" in value.value
    ? (value.value as SubmissionPreview)
    : null
)

const total = computed(() =>
  paginator.value
    ? paginator.value.paginatorInfo.total
    : Number(value.value ?? 0)
)

const submissions = computed(() => paginator.value?.data ?? [])

// How many submissions exist beyond what the preview returned —
// rendered as a "+N" chip after the icons.
const overflow = computed(() =>
  Math.max(0, total.value - submissions.value.length)
)

function styleFor(status: string) {
  return (
    statusStyleMap[status] ?? {
      color: "grey-5",
      textClass: "text-white",
      icon: "description",
      pattern: ""
    }
  )
}
</script>

<style scoped>
.submission-chip {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  /* Let the category-badge peek outside the chip. */
  overflow: visible;
}
.overflow-chip {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.02em;
}
/* Small colored circle in the lower-right corner holding the
   category icon (flag / hourglass / edit_note / check_circle).
   The white halo makes the badge readable regardless of the chip's
   background pattern and helps distinguish adjacent chips. */
.category-badge {
  position: absolute;
  right: -4px;
  bottom: -4px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 0 1.5px #fff;
}
.body--dark .category-badge {
  box-shadow: 0 0 0 1.5px #1d1d1d;
}
</style>
