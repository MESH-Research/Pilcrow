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
        <div
          v-if="status !== 'paste_success' && status !== 'upload_success'"
          class="q-gutter-md"
        >
          <h1 class="text-h3" data-cy="submission_content_title">
            {{ $t(`submissions.content.heading`) }}
          </h1>
          <q-banner
            v-if="updateMethod === 'upload' || updateMethod == ''"
            data-cy="upload_option"
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
            data-cy="paste_option"
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
          <div v-if="updateMethod == 'upload'">
            <q-file
              v-model="uploadFile"
              data-cy="file_picker"
              clearable
              outlined
              color="accent"
              :label="$t(`submissions.content.upload.file_picker_label`)"
            >
              <template #prepend>
                <q-icon name="attach_file" />
              </template>
            </q-file>
            <q-btn
              data-cy="submit_upload_btn"
              color="primary"
              class="q-mt-md"
              :label="$t(`submissions.content.submit.btn_label`)"
              @click="submitUpload"
            />
          </div>
          <div v-if="status === 'upload_error'">
            <q-banner class="bg-negative text-white">
              {{ $t(`submissions.content.submit.error`) }}
            </q-banner>
          </div>
          <div v-if="updateMethod == 'paste'">
            <q-editor
              v-model="pasteContent"
              data-cy="content_editor"
              min-height="5rem"
            />
            <q-btn
              data-cy="submit_paste_btn"
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
            data-cy="content_submit_success_btn"
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
import {
  UPDATE_SUBMISSION_CONTENT,
  UPDATE_SUBMISSION_CONTENT_WITH_FILE,
} from "src/graphql/mutations"
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
const uploadFile = ref(null)
const { result } = useQuery(GET_SUBMISSION, props)
const submission = computed(() => result.value?.submission)
let status = ref("incomplete")

function clearMethod() {
  status.value = "incomplete"
  updateMethod.value = ""
  uploadFile.value = null
}

function setMethod(value) {
  updateMethod.value = value
}
const { mutate: updateContent } = useMutation(UPDATE_SUBMISSION_CONTENT)
async function submitPaste() {
  try {
    await updateContent({ id: props.id, content: pasteContent.value })
    status.value = "paste_success"
  } catch (error) {
    status.value = "paste_error"
  }
}

const uploadOpts = {
  variables: {
    submission_id: props.id,
    file_upload: uploadFile.value,
  },
  context: {
    hasUpload: true,
  },
}
const { mutate: updateContentWithFile } = useMutation(
  UPDATE_SUBMISSION_CONTENT_WITH_FILE,
  uploadOpts
)
async function submitUpload() {
  try {
    uploadOpts.variables.file_upload = uploadFile.value
    await updateContentWithFile()
    status.value = "upload_success"
  } catch (error) {
    status.value = "upload_error"
  }
}
</script>
