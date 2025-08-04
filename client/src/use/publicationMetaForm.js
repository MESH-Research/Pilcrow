import { useMutation } from "@vue/apollo-composable"
import { reactive } from "vue"
import useVuelidate from "@vuelidate/core"
import { required, maxLength } from "@vuelidate/validators"
import { CREATE_SUBMISSION_DRAFT } from "src/graphql/mutations"

export const useMetaFormCreation = () => {
  const { mutate, saving } = useMutation(CREATE_SUBMISSION_DRAFT)
  const form = reactive({
    form_name: "",
    formCaption: "",
    isRequired: false
  })

  const rules = {
    name: {
      required,
      maxLength: maxLength(512)
    },
    caption: {
      maxLength: maxLength(2048)
    }
  }

  const v$ = useVuelidate(rules, form)
  const createMetaForm = async () => {
    v$.value.$touch()
    if (v$.value.$invalid) {
      throw Error("FORM_VALIDATION")
    }

    const mutationResult = await mutate(
      {
        form_name: form.form_name
      },
      {
        refetchQueries: [{ query: CURRENT_USER_SUBMISSIONS }]
      }
    )
    return mutationResult
  }
  return { createMetaForm, v$, saving }
}
