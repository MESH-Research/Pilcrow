import { computed, watch } from '@vue/composition-api';
import { clone } from 'lodash';


export const useHasErrorKey = (validator) => {
    return computed(() => {

       return (field, key) => {
            return hasErrorKey(validator.value?.[field].$errors, key) ?? false
        }
    })
}


export function hasErrorKey(errors, key) {
    return  errors.some((error) => {
        return getErrorMessageKey(error) == key
    });
}


export function getErrorMessageKey($error) {
    if ($error.$validator === '$externalResults') {
      return $error.$message;
    }
    return $error.$validator;
}

export function externalFieldWatcher(data, externalValidation, field) {
    const cancel = watch(
        () => clone(data),
        (data, oldValue) => {
            if (data[field] != oldValue[field]) {
                externalValidation[field] = []
                cancel();
            }
        }
    )
}


export function applyExternalValidationErrors(data, externalValidation, error, strip = '') {
    const gqlErrors = error?.graphQLErrors ?? [];
    const validationErrors = clone(externalValidation);
    gqlErrors.forEach(item => {
        const vErrors = item?.extensions?.validation ?? false;
        if (vErrors !== false) {
            for (const [fieldName, fieldErrors] of Object.entries(vErrors)) {
                const key = fieldName.replace(strip, '')
                if (validationErrors[key]) {
                    validationErrors?.[key]?.push(...fieldErrors);
                    externalFieldWatcher(data, externalValidation, key);
                }

            }
        }
    });
    return Object.keys(validationErrors).some(f => validationErrors[f].length > 0);
}