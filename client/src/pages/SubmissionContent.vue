<template>
  <nav class="q-px-lg q-pt-md q-gutter-sm">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Publications" />
      <q-breadcrumbs-el :label="submission?.publication?.name ?? ''" />
      <q-breadcrumbs-el
        :to="{
          name: 'submission:draft',
          params: { id: props.id },
        }"
      >
        {{ submission?.title ?? "" }} Draft
      </q-breadcrumbs-el>
      <q-breadcrumbs-el>Upload New Content</q-breadcrumbs-el>
    </q-breadcrumbs>
  </nav>
  <div class="row flex-center q-pa-lg">
    <div class="col-lg-5 col-md-6 col-sm-8 col-xs-12">
      <article class="q-pa-lg">
        <div v-if="status !== 'paste_success'" class="q-gutter-md">
          <h2 class="text-h3">Upload New Content</h2>
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
          <div v-if="status === 'paste_error'">
            <q-banner class="bg-negative text-white">
              <div>
                An error occurred while attempting to submit your update
              </div>
              <div class="text-caption">Please try again later</div>
            </q-banner>
          </div>
        </div>
        <div v-else class="column text-center flex-center q-px-lg">
          <q-icon color="positive" name="check_circle" size="2em" />
          <strong class="text-h3"> Success </strong>
          <p>The content for your submission has been updated successfully.</p>
          <q-btn
            class="q-mr-sm"
            color="accent"
            size="md"
            :label="`Return to Draft`"
            :to="{
              name: 'submission:draft',
              params: { id: props.id },
            }"
          />
        </div>
      </article>
    </div>
  </div>
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
let status = ref("incomplete")
const emit = defineEmits(["update:contentUploaded"])
function setContentUploaded() {
  console.log(`emit true`)
  emit("update:contentUploaded", true)
}

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
  try {
    await mutate({ id: props.id, content: pasteContent.value })
    status.value = "paste_success"
    setContentUploaded()
  } catch (error) {
    console.log(error)
    status.value = "paste_error"
  }
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
