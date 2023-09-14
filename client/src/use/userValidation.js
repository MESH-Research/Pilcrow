import { reactive } from "vue"
import useVuelidate from "@vuelidate/core"
import { required, email, helpers } from "@vuelidate/validators"
import { CREATE_USER } from "src/graphql/mutations"
import { useMutation } from "@vue/apollo-composable"
import zxcvbn from "zxcvbn"
import { applyExternalValidationErrors } from "src/use/validationHelpers"
import { omit } from "lodash"

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
        $valid: !helpers.req(value) || complexity.score >= 3,
      }
    },
  },
}

export const updateUserRules = omit(rules, ['name', 'username'])

export function useUserValidation(opts = {}) {
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

  const mutate = opts.mutation ?? useMutation(CREATE_USER).mutate
  if (opts.rules && typeof opts.rules === "function") {
    opts.rules(rules)
  }
  const $v = useVuelidate(rules, form, { $externalResults: externalValidation })

  const saveUser = async () => {
    $v.value.$touch()
    if ($v.value.$invalid || $v.value.$error) {
      throw Error("FORM_VALIDATION")
    }
    const vars =
      opts.variables && typeof opts.variables === "function"
        ? opts.variables(form)
        : form
    try {
      const newUser = await mutate(vars)
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
