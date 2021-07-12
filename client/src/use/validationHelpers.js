import { computed } from '@vue/composition-api';

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