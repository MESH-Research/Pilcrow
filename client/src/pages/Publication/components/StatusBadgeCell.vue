<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-item class="q-pa-none">
      <q-item-section>
        <div>
          <q-badge
            :color="style.color"
            :class="[
              'text-weight-medium q-pa-sm',
              style.textClass,
              style.pattern,
              canChangeStatus ? 'cursor-pointer' : ''
            ]"
            :role="canChangeStatus ? 'button' : undefined"
            :tabindex="canChangeStatus ? 0 : undefined"
            :aria-label="
              canChangeStatus
                ? $t('submissions.action.change_status.label') + ': ' + label
                : label
            "
            :aria-haspopup="canChangeStatus ? 'menu' : undefined"
            @click.stop
            @keydown.enter.stop
            @keydown.space.stop
          >
            <q-icon :name="style.icon" size="xs" />
            <q-separator vertical class="q-mx-xs" />
            <span class="pattern-text-mask">{{ label }}</span>
            <q-icon
              v-if="canChangeStatus"
              name="arrow_drop_down"
              size="xs"
              class="q-ml-xs"
            />
            <q-menu
              v-if="canChangeStatus"
              anchor="bottom start"
              self="top start"
            >
              <q-list dense style="min-width: 220px">
                <q-item
                  v-for="transition in transitions"
                  :key="transition.action"
                  v-close-popup
                  role="menuitem"
                  clickable
                  :data-cy="`change_status_${transition.action}`"
                  @click.stop="openConfirm(transition.action)"
                >
                  <q-item-section>
                    {{ $t(`submission.action.${transition.action}`) }}
                  </q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-badge>
        </div>
      </q-item-section>
    </q-item>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { useI18n } from "vue-i18n"
import { useQuasar } from "quasar"
import ConfirmStatusChangeDialog from "src/components/dialogs/ConfirmStatusChangeDialog.vue"
import type { QTableBodyCellScope } from "src/components/tables/QueryTable.vue"
import type { Submission } from "src/graphql/generated/graphql"
import { statusStyleMap } from "./statusCategories"
import { useStatusTransitions } from "src/use/submissionStatusTransitions"

interface Props {
  scope: QTableBodyCellScope
}

const props = defineProps<Props>()
const { t } = useI18n()
const { dialog } = useQuasar()

const submission = computed(
  () => props.scope.row as Pick<Submission, "id" | "status" | "reviewers">
)
const status = computed(() => submission.value.status as string)

const style = computed(
  () =>
    statusStyleMap[status.value] ?? {
      color: "grey",
      textClass: "text-white",
      icon: "help",
      pattern: ""
    }
)
const label = computed(() => t(`submission.status.${status.value}`))

const { canChangeStatus, transitions } = useStatusTransitions(submission)

function openConfirm(action: string) {
  dialog({
    component: ConfirmStatusChangeDialog,
    componentProps: {
      action,
      submissionId: submission.value.id,
      currentStatus: status.value
    }
  })
}
</script>
