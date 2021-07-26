import { computed, watch, inject } from '@vue/composition-api';
import { clone } from 'lodash';

/**
 * Create a computed property for checking for an error condition on a field.
 * Computed has the signature (field, key) where field is the field to check and
 * key is the validator or external validator message to check for.
 *
 * @inject Validator Vuelidate validation object.
 * @returns computed
 */
export const useHasErrorKey = () => {
    const validator = inject('validator');

    return computed(() => {

       return (field, key) => {
            return hasErrorKey(validator.value?.[field].$errors, key) ?? false
        }
    })
}

/**
 * Checks the supplied validation errors array for the presence of a validator
 * or an externalResults message.
 *
 * @param {Array} errors  Field errors
 * @param {String} key Key to find
 * @returns Boolean
 */
export function hasErrorKey(errors, key) {
    return  errors.some((error) => {
        return getErrorMessageKey(error) == key
    });
}

/**
 * Returns the validator name if the error is from a local validator or the message
 * returned from the external validator if the error is external.
 *
 * @param {Object} $error Error object to extract key or message from.
 * @returns String
 */
export function getErrorMessageKey($error) {
    if ($error.$validator === '$externalResults') {
      return $error.$message;
    }
    return $error.$validator;
}

/**
 * Remove the externalValidation errors when the field changes value.
 *
 * @param {reactive} data
 * @param {reactive} externalValidation
 * @param {String} field
 */
export function externalFieldWatcher(data, externalValidation, field) {
    oneShotPropertyWatch(data, field, () => {
        externalValidation[field] = [];
    });
}

/**
 * Add a one-shot watcher to a reactive property field that removes itself after
 * being called once when the property values changes.
 *
 * @param {reactive} data
 * @param {reactive} property
 * @param {Function} callback
 */
export function oneShotPropertyWatch(data, property, callback) {
    const cancel = watch(
        () => clone(data),
        (data, oldValue) => {
            if (data[property] != oldValue[property]) {
                callback();
                cancel();
            }
        }
    )
}

/**
 * Parses a GraphQL error object for any validation errors and applies them to the
 * provided externalValidation reactive.  Apply the externalValidation reactive to
 * vuelidate's $externalResults option to include GraphQL validation errors in
 * vuelidate error responses.
 *
 * @param {reactive} data Form data reactive
 * @param {reactive} externalValidation External validation reactive
 * @param {Object} error Error object returned by ApolloClient
 * @param {String} strip String to strip from the beginning of the GraphQL field name
 * @returns
 */
export function applyExternalValidationErrors(data, externalValidation, error, strip = '') {
    const gqlErrors = error?.graphQLErrors ?? [];
    const validationErrors = gqlErrors
        .map(gError => {
            const fields = gError?.extensions?.validation ?? null
            if (!fields) return null;
            const errors = {};
            for (const [key, value] of Object.entries(fields)) {
                errors[key.replace(strip, '')] = value;
            }
            return errors
        }).filter(e => e);
    if (validationErrors.length === 0) {
        return false;
    }
    Object.assign(externalValidation, ...validationErrors)
    for (const [key] of Object.entries(externalValidation)) {
        externalFieldWatcher(data, externalValidation, key);
    }
    return true;

}
