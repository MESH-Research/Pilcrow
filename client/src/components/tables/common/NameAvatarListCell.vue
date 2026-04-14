<template>
  <q-td :props="scope" :dense="scope.dense">
    <div
      v-if="users.length"
      class="row items-center no-wrap q-gutter-xs"
      role="group"
      :aria-label="ariaLabel"
    >
      <span class="sr-only">{{ ariaLabel }}</span>
      <template v-if="expanded">
        <ul class="column q-gutter-xs col q-pa-none q-ma-none no-list-style">
          <li v-for="user in users" :key="user.email">
            <q-item class="q-pa-none">
              <q-item-section side>
                <avatar-image
                  :user="user"
                  rounded
                  :aria-label="user.name ?? user.email"
                />
              </q-item-section>
              <q-item-section>
                <q-item-label v-if="user.name">{{ user.name }}</q-item-label>
              </q-item-section>
            </q-item>
          </li>
        </ul>
      </template>
      <template v-else>
        <div
          v-for="user in users"
          :key="user.email"
          class="q-pa-none relative-position"
        >
          <avatar-image
            :user="user"
            size="sm"
            rounded
            :aria-label="user.name ?? user.email"
          />
          <q-tooltip>{{ user.name ?? user.email }}</q-tooltip>
        </div>
      </template>
      <q-btn
        v-if="users.length > 1"
        flat
        dense
        round
        size="xs"
        :icon="expanded ? 'unfold_less' : 'unfold_more'"
        :aria-label="expanded ? 'Collapse user list' : 'Expand user list'"
        :aria-expanded="expanded"
        class="q-ml-xs"
        @click.stop="expanded = !expanded"
      >
        <q-tooltip>{{ expanded ? "Collapse" : "Expand" }}</q-tooltip>
      </q-btn>
    </div>
    <span v-else class="text-grey" aria-label="No users assigned">&mdash;</span>
  </q-td>
</template>

<script setup lang="ts">
import { computed, ref } from "vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { useI18n } from "vue-i18n"
import type { NameAvatarCellFragment } from "src/graphql/generated/graphql"
import type { QTableBodyCellScope } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
}

const props = defineProps<Props>()
const { t } = useI18n()

const users = computed(
  () => (props.scope.value as NameAvatarCellFragment[]) ?? []
)

const expanded = ref(false)

const ariaLabel = computed(() =>
  t("tables.name_avatar_list.users_assigned", users.value.length, {
    named: {
      names: users.value
        .map((u) => u.name ?? u.email)
        .filter(Boolean)
        .join(", ")
    }
  })
)
</script>

<style scoped>
.no-list-style {
  list-style: none;
}
</style>
