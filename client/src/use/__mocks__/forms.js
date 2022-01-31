/* eslint-env node */
import { ref } from "vue"
const forms = jest.createMockFromModule("../forms")
forms.useFormState.mockImplementation(() => ({
  dirty: ref(false),
  saved: ref(false),
  state: ref("idle"),
  queryLoading: ref(false),
  mutationLoading: ref(false),
  errorMessage: ref(""),
}))
module.exports = {
  ...forms,
}
