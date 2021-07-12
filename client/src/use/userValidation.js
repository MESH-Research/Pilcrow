import { reactive, watch } from '@vue/composition-api';
import useVuelidate from "@vuelidate/core";
import { required, email } from "@vuelidate/validators";
import { CREATE_USER } from "src/graphql/mutations";
import { useMutation } from "@vue/apollo-composable";
import zxcvbn from "zxcvbn";
import { forIn, clone } from 'lodash';




export function  useUserValidation() {
    const form = reactive({
          email: "",
          password: "",
          name: "",
          username: ""
    });

    const externalValidation = reactive({
        email: [],
        password: [],
        name: [],
        username: []
    });

    function externalFieldWatcher(field) {
        const cancel = watch(
            () => clone(form),
            (form, oldValue) => {
                if (form[field] != oldValue[field]) {

                    externalValidation[field] = []
                    cancel();
                }
            }
        )
    }


    function applyExternalValidationErrors(externalValidation, error, strip = '') {
        const gqlErrors = error?.graphQLErrors ?? [];
        const validationErrors = clone(externalValidation);
        gqlErrors.forEach(item => {
            const vErrors = item?.extensions?.validation ?? false;
            if (vErrors !== false) {
                for (const [fieldName, fieldErrors] of Object.entries(vErrors)) {
                    const key = fieldName.replace(strip, '')
                    if (validationErrors[key]) {
                        validationErrors?.[key]?.push(...fieldErrors);
                        externalFieldWatcher(key);
                    }

                }
            }
        });
        return Object.keys(validationErrors).some(f => validationErrors[f].length > 0);
    }
    const rules = {
        name: {

        },
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
                const complexity = zxcvbn(value);
                return {
                    complexity,
                    $valid: complexity.score >= 3
                }

            }
        }
      };

    const $v = useVuelidate(rules, form, { $externalResults: externalValidation });

    const { mutate } = useMutation(CREATE_USER);

    const saveUser = async () => {
        $v.value.$touch();
        if ($v.value.$invalid || $v.value.$error) {
            throw Error("CREATE_FORM_VALIDATION")
        }
        try {
            const newUser = await mutate(form);
            return newUser
        } catch (error) {
            if (applyExternalValidationErrors(externalValidation, error, 'user.')) {
                throw Error("CREATE_FORM_VALIDATION");
            } else {
                throw Error("CREATE_FORM_INTERNAL");
            }
        }
    }

    return { $v, user: form, saveUser }
  }