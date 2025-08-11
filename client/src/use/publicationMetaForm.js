import { useMutation } from "@vue/apollo-composable"
import { reactive } from "vue"
import useVuelidate from "@vuelidate/core"
import { required, maxLength } from "@vuelidate/validators"
import { CREATE_SUBMISSION_META_FORM } from "src/graphql/mutations"
import { GET_PUBLICATION } from "src/graphql/queries"

export const useMetaFormCreation = () => {
  const { mutate, saving } = useMutation(CREATE_SUBMISSION_META_FORM)
  const form = reactive({
    name: "",
    caption: "",
    required: false
  })

  const rules = {
    name: {
      required,
      maxLength: maxLength(512)
    },
    caption: {
      maxLength: maxLength(2048)
    },
    required: {}
  }

  const v$ = useVuelidate(rules, form)
  const createMetaForm = async () => {
    v$.value.$touch()
    if (v$.value.$invalid) {
      throw Error("FORM_VALIDATION")
    }

    const mutationResult = await mutate(
      {
        name: form.name,
        caption: form.caption,
        required: form.required
      },
      {
        refetchQueries: [{ query: GET_PUBLICATION }]
      }
    )
    return mutationResult
  }
  return { createMetaForm, v$, saving }
}
