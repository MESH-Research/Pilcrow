import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"

/**
 * Display feedback messages to the user using the Quasar notify plugin.
 *
 * @param   {Object}  overrideDefaults  Override default options for message functions
 */
export function useFeedbackMessages(overrideDefaults) {
  const { t } = useI18n()
  const { notify } = useQuasar()

  const defaultOptions = Object.assign(overrideDefaults, {
    group: false,
    actions: [
      {
        label: t("guiElements.feedbackMessage.closeButton"),
        color: "white",
        "data-cy": "button_dismiss_notify",
      },
    ],
    timeout: 10000,
    progress: true,
    html: true,
  })

  /**
   * Show a new feedback message to the user.
   *
   * @param   {string}  message  Message content
   * @param   {Object}  opts     Override default options
   */
  function newMessage(message, opts) {
    const options = Object.assign({ message, ...opts }, defaultOptions)
    notify(options)
  }

  /**
   * Show a status (success or failure) feedback message to the user.
   *
   * @param   {string}  status   One of: failure, success
   * @param   {string}  message  Message content
   */
  function newStatusMessage(status, message) {
    const type = {
      success: "positive",
      failure: "negative",
    }[status]
    newMessage(message, { type })
  }

  return { newMessage, newStatusMessage }
}
