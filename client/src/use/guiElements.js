import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"
export function useFeedbackMessages(opts) {
  const { t } = useI18n()
  const { notify } = useQuasar()

  /**
   * Return default options with user supplied options applied onto them.
   *
   * @return {Object} Notify Options
   */
  function getOpts() {
    return Object.assign(opts, {
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
  }

  /**
   * Show a new feedback message to the user.
   *
   * @param   {string}  message  Message content
   * @param   {Object}  opts     Override default options
   */
  function newMessage(message, opts) {
    const options = Object.assign({ message, ...opts }, getOpts())
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
