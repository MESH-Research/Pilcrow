<template>
  <nav class="q-px-lg q-pt-md q-gutter-sm">
    <q-breadcrumbs>
      <q-breadcrumbs-el :label="$t('header.publications')" />
      <q-breadcrumbs-el
        :label="
          submission?.publication?.name ?? $t(`publications.term`, { count: 1 })
        "
      />
      <q-breadcrumbs-el
        :to="{
          name: 'submission:draft',
          params: { id: submission?.id },
        }"
      >
        {{
          $t(`submissions.create.draft_title`, {
            submission_title:
              submission?.title ?? $t(`submissions.term`, { count: 1 }),
          })
        }}
      </q-breadcrumbs-el>
      <q-breadcrumbs-el>{{
        $t(`submissions.content.heading`)
      }}</q-breadcrumbs-el>
    </q-breadcrumbs>
  </nav>
  <div class="row flex-center q-pa-lg">
    <div class="col-lg-5 col-md-6 col-sm-10 col-xs-12">
      <article class="q-pa-lg">
        <div v-if="status !== 'paste_success'" class="q-gutter-md">
          <h2 class="text-h3">{{ $t(`submissions.content.heading`) }}</h2>
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
                :label="$t(`submissions.content.upload.label`)"
              />
            </div>
            <div class="text-caption">
              {{ $t(`submissions.content.upload.caption`) }}
            </div>
            <template v-if="updateMethod !== ''" #action>
              <q-btn
                flat
                :label="$t(`submissions.content.back_btn_label`)"
                @click.stop="clearMethod"
              />
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
              :label="$t(`submissions.content.paste.label`)"
            />
            <div class="text-caption">
              {{ $t(`submissions.content.paste.caption`) }}
            </div>
            <template v-if="updateMethod !== ''" #action>
              <q-btn
                flat
                :label="$t(`submissions.content.back_btn_label`)"
                @click.stop="clearMethod"
              />
            </template>
          </q-banner>
          <div v-if="updateMethod == 'paste'">
            <q-editor v-model="pasteContent" min-height="5rem" />
            <q-btn
              color="primary"
              class="q-mt-md"
              :label="$t(`submissions.content.submit.btn_label`)"
              @click="submitPaste"
            />
          </div>
          <div v-if="status === 'paste_error'">
            <q-banner class="bg-negative text-white">
              {{ $t(`submissions.content.submit.error`) }}
            </q-banner>
          </div>
        </div>
        <div v-else class="column text-center flex-center q-px-lg">
          <q-icon color="positive" name="check_circle" size="2em" />
          <strong class="text-h3">{{
            $t(`submissions.content.submit.success.title`)
          }}</strong>
          <p>{{ $t(`submissions.content.submit.success.message`) }}</p>
          <q-btn
            class="q-mr-sm"
            color="accent"
            size="md"
            :label="$t(`submissions.content.submit.success.btn_label`)"
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
