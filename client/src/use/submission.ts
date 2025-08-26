import { useMutation } from "@vue/apollo-composable"
import { useCurrentUser } from "./user"
import { reactive } from "vue"
import useVuelidate from "@vuelidate/core"
import { required, maxLength } from "@vuelidate/validators"
import { CREATE_SUBMISSION_DRAFT } from "src/graphql/mutations"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"

export const useSubmissionCreation = () => {
  const { mutate, saving } = useMutation(CREATE_SUBMISSION_DRAFT)

  const submission = reactive({
    title: "",
    acknowledgement: false
  })
  const isTrue = (value) => value === true

  const rules = {
    title: {
      required,
      maxLength: maxLength(512)
    },
    acknowledgement: {
      isTrue
    }
  }
  const { currentUser } = useCurrentUser()
  const v$ = useVuelidate(rules, submission)
  const createSubmission = async (publication) => {
    v$.value.$touch()
    if (v$.value.$invalid) {
      throw Error("FORM_VALIDATION")
    }
    const mutationResult = await mutate(
      {
        title: submission.title,
        publication_id: publication.value.id,
        submitter_user_id: currentUser.value.id
      },
      {
        refetchQueries: [{ query: CURRENT_USER_SUBMISSIONS }]
      }
    )
    return mutationResult
  }

  return { createSubmission, v$, saving }
}
