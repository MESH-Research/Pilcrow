export default {
  methods: {
    /**
     * Extract array of error codes from graphql error response
     *
     * @param {Object} errorResponse
     * @returns {array} error codes
     */
    $graphQLErrorCodes(errorResponse) {
      return (
        errorResponse.graphQLErrors
          ?.map(e => e.extensions?.code ?? false)
          .filter(Boolean) ?? []
      );
    },
    /**
     * Transform an array of error codes to error messages, optionally prefixing
     * the i18n key.
     * @param {array} errorList List of error codes
     * @param {string} [i18nDomain] prefix for i18n key
     * @returns {array}
     */
    $errorMessages(errorList, i18nDomain) {
      const msg = code => (i18nDomain ? `${i18nDomain}.${code}` : code);
      return errorList.map(code => this.$t(msg(code)));
    }
  }
};
