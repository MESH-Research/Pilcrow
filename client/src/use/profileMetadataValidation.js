import { maxLength } from "@vuelidate/validators"

const rules = {
  professional_title: { maxLength: maxLength(256) },
  specialization: { maxLength: maxLength(256) },
  affiliation: { maxLength: maxLength(256) },
}

export default { rules }
