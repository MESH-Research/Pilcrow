import { flushPromises, mount } from "@vue/test-utils"
import { installApolloClient, installQuasarPlugin } from "app/test/vitest/utils"
import { Notify } from "quasar"
import {
  SubmitSubmissionDocument,
  UpdateSubmissionStatusDocument
} from "src/graphql/generated/graphql"
import { beforeEach, describe, expect, test, vi } from "vitest"
import { ref } from "vue"
import ConfirmStatusChangeDialog from "./ConfirmStatusChangeDialog.vue"

const { push } = vi.hoisted(() => ({ push: vi.fn() }))

vi.mock("vue-router", () => ({
  useRouter: () => ({ push })
}))

vi.mock("quasar", async (importOriginal) => {
  const actual = await importOriginal<typeof import("quasar")>()
  const useDialogPluginComponent = Object.assign(
    () => ({
      dialogRef: ref(null),
      onDialogHide: vi.fn(),
      onDialogOK: vi.fn(),
      onDialogCancel: vi.fn()
    }),
    { emits: actual.useDialogPluginComponent.emits }
  )
  return { ...actual, useDialogPluginComponent }
})

installQuasarPlugin({ plugins: { Notify } })
const mockClient = installApolloClient()

const submitMutation = vi.fn()
const changeStatusMutation = vi.fn()
mockClient.setRequestHandler(SubmitSubmissionDocument, submitMutation)
mockClient.setRequestHandler(
  UpdateSubmissionStatusDocument,
  changeStatusMutation
)

const submissionResult = (status: string, comment: string | null) => ({
  __typename: "Submission",
  id: "1",
  status,
  status_change_comment: comment
})

function makeWrapper(action: string, currentStatus: string) {
  return mount(ConfirmStatusChangeDialog, {
    props: { action, submissionId: "1", currentStatus },
    global: {
      stubs: { QDialog: { template: "<div><slot /></div>" }, "i18n-t": true }
    }
  })
}

describe("ConfirmStatusChangeDialog", () => {
  beforeEach(() => {
    vi.resetAllMocks()
  })

  test("submit for review sends the draft in with submitSubmission", async () => {
    submitMutation.mockResolvedValue({
      data: {
        submitSubmission: submissionResult("INITIALLY_SUBMITTED", "Ready")
      }
    })
    const wrapper = makeWrapper("submit_for_review", "DRAFT")

    await wrapper
      .findComponent({ name: "QInput" })
      .vm.$emit("update:modelValue", "Ready")
    await wrapper.find('[data-cy="dirtyYesChangeStatus"]').trigger("click")
    await flushPromises()

    expect(submitMutation).toHaveBeenCalledWith({
      id: "1",
      status_change_comment: "Ready"
    })
    expect(changeStatusMutation).not.toHaveBeenCalled()
    expect(push).toHaveBeenCalledWith({ path: "/submission/1/view/" })
  })

  test("editorial actions change the status with changeSubmissionStatus", async () => {
    changeStatusMutation.mockResolvedValue({
      data: {
        changeSubmissionStatus: submissionResult("AWAITING_REVIEW", null)
      }
    })
    const wrapper = makeWrapper("accept_for_review", "INITIALLY_SUBMITTED")

    await wrapper.find('[data-cy="dirtyYesChangeStatus"]').trigger("click")
    await flushPromises()

    expect(changeStatusMutation).toHaveBeenCalledWith({
      id: "1",
      status: "AWAITING_REVIEW",
      status_change_comment: null
    })
    expect(submitMutation).not.toHaveBeenCalled()
    expect(push).toHaveBeenCalledWith({ path: "/submission/1/review/" })
  })
})
