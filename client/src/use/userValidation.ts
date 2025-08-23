import type { Reactive } from "vue"
import { reactive } from "vue"
import type { ValidationArgs } from "@vuelidate/core"
import useVuelidate from "@vuelidate/core"
import { required, email, helpers, maxLength } from "@vuelidate/validators"
import { CREATE_USER } from "src/graphql/mutations"
import { useMutation } from "@vue/apollo-composable"
import { applyExternalValidationErrors } from "src/use/validationHelpers"
import { omit } from "lodash"
import { zxcvbn } from "@zxcvbn-ts/core"
export const rules = {
  name: {
    maxLength: maxLength(256)
  },
  email: {
    required,
    email
  },
  username: {
    required
  },
  password: {
    required,
    notComplex(value) {
      const complexity = zxcvbn(value)
      return {
        complexity,
        $valid: !helpers.req(value) || complexity.score >= 3
      }
    }
  }
}

export const updateUserRules = omit(rules, ["name", "username"])

interface ValidationOptions {
  mutation?: () => unknown
  rules?: (args: ValidationArgs<typeof rules>) => void
  variables?: (form: Reactive<UserForm>) => UserForm
  validation_key?: string
}

interface UserForm {
  email: string
  password: string
  name: string
  username: string
}

export function useUserValidation(opts: ValidationOptions = {}) {
  const form = reactive<UserForm>({
    email: "",
    password: "",
    name: "",
    username: ""
  })

  const externalValidation = reactive({
    email: [],
    password: [],
    name: [],
    username: []
  })

  const mutate = opts.mutation ?? useMutation(CREATE_USER).mutate

  if (opts.rules) {
    opts.rules(rules)
  }

  const $v = useVuelidate(rules, form, { $externalResults: externalValidation })

  const saveUser = async () => {
    $v.value.$touch()
    if ($v.value.$invalid || $v.value.$error) {
      throw Error("FORM_VALIDATION")
    }
    const vars = opts.variables ? opts.variables(form) : form
    const validation_key = opts.validation_key ?? "user."
    try {
      const newUser = await mutate(vars)
      return newUser
    } catch (error) {
      if (
        applyExternalValidationErrors(
          form,
          externalValidation,
          error,
          validation_key
        )
      ) {
        throw Error("FORM_VALIDATION")
      } else {
        throw Error("INTERNAL")
      }
    }
  }

  return { $v, user: form, saveUser }
}
