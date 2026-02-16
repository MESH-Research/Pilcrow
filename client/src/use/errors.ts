import { useI18n } from "vue-i18n"
import type { ApolloError } from "@apollo/client/errors"

export function useGraphErrors() {
  const { t } = useI18n()
  return {
    /**
     * Extract array of error codes from graphql error response
     */
    graphQLErrorCodes(errorResponse: ApolloError): string[] {
      return (
        (errorResponse.graphQLErrors
          ?.map((e) => e.extensions?.code ?? false)
          .filter(Boolean) as string[]) ?? []
      )
    },
    /**
     * Transform an array of error codes to error messages, optionally prefixing
     * the i18n key.
     */
    errorMessages(errorList: string[], i18nDomain?: string) {
      const msg = (code: string) =>
        i18nDomain ? `${i18nDomain}.${code}` : code
      return errorList.map((code) => t(msg(code)))
    }
  }
}
