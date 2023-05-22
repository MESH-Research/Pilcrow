<template>
  <nav class="q-px-lg q-pt-md q-gutter-sm">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Publications" />
      <q-breadcrumbs-el :label="submission?.publication?.name ?? ''" />
      <q-breadcrumbs-el
        :to="{
          name: 'submission:draft',
          params: { id: submission?.id },
        }"
      >
        {{ submission?.title ?? "" }} Draft
      </q-breadcrumbs-el>
      <q-breadcrumbs-el>Upload New Content</q-breadcrumbs-el>
    </q-breadcrumbs>
  </nav>
  <article class="q-pa-lg">
    <div class="q-gutter-md">
      <q-banner
        v-if="updateMethod === 'upload' || updateMethod == ''"
        class="bg-primary text-white"
        inline-actions
        @click="setMethod('upload')"
      >
        <div>
          <q-radio
            v-model="updateMethod"
            color="secondary"
            val="upload"
            label="Upload Document (Coming soon!)"
          />
        </div>
        <div class="text-caption">Supported file types, etc, etc</div>
        <template v-if="updateMethod !== ''" #action>
          <q-btn flat @click.stop="clearMethod">Back</q-btn>
        </template>
      </q-banner>
      <q-banner
        v-if="updateMethod === 'paste' || updateMethod == ''"
        class="bg-primary text-white"
        inline-actions
        @click="setMethod('paste')"
      >
        <q-radio
          v-model="updateMethod"
          color="secondary"
          val="paste"
          label="Paste Content"
        />
        <template v-if="updateMethod !== ''" #action>
          <q-btn flat @click.stop="clearMethod">Back</q-btn>
        </template>
      </q-banner>
      <div v-if="updateMethod == 'paste'" class="q-gutter-sm">
        <q-editor v-model="pasteContent" min-height="5rem" />
        <q-btn color="primary" @click="submitPaste">Update Content</q-btn>
      </div>
    </div>
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import { useQuery, useMutation } from "@vue/apollo-composable"
import { computed, ref } from "vue"

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const updateMethod = ref("")
const pasteContent = ref("")
const { result } = useQuery(GET_SUBMISSION, props)
const submission = computed(() => result.value?.submission)

function clearMethod() {
  updateMethod.value = ""
}

function setMethod(value) {
  if (updateMethod.value === "") {
    updateMethod.value = value
  }
}

const { mutate } = useMutation(UPDATE_CONTENT)
async function submitPaste() {
  const result = await mutate({ id: props.id, content: pasteContent.value })

  console.log(result)
}
</script>

<script>
import { gql } from "graphql-tag"

const UPDATE_CONTENT = gql`
  mutation UpdateSubmissionContent($id: ID!, $content: String!) {
    updateSubmissionContent(input: { content: $content, id: $id }) {
      id
      content {
        data
      }
    }
  }
`
</script>
