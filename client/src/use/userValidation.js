import { reactive } from "vue"
import useVuelidate from "@vuelidate/core"
import { required, email } from "@vuelidate/validators"
import { CREATE_USER } from "src/graphql/mutations"
import { useMutation } from "@vue/apollo-composable"
import zxcvbn from "zxcvbn"
import { applyExternalValidationErrors } from "src/use/validationHelpers"

export const rules = {
  name: {},
  email: {
    required,
    email,
  },
  username: {
    required,
  },
  password: {
    required,
    notComplex(value) {
      const complexity = zxcvbn(value)
      return {
        complexity,
        $valid: complexity.score >= 3,
      }
    },
  },
}

export function useUserValidation() {
  const form = reactive({
    email: "",
    password: "",
    name: "",
    username: "",
  })

  const externalValidation = reactive({
    email: [],
    password: [],
    name: [],
    username: [],
  })

  const $v = useVuelidate(rules, form, { $externalResults: externalValidation })

  const { mutate } = useMutation(CREATE_USER)

  const saveUser = async () => {
    $v.value.$touch()
    if ($v.value.$invalid || $v.value.$error) {
      throw Error("FORM_VALIDATION")
    }
    try {
      const newUser = await mutate(form)
      return newUser
    } catch (error) {
      if (
        applyExternalValidationErrors(form, externalValidation, error, "user.")
      ) {
        throw Error("FORM_VALIDATION")
      } else {
        throw Error("INTERNAL")
      }
    }
  }

  return { $v, user: form, saveUser }
}
