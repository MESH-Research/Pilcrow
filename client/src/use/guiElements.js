import { useQuasar } from "quasar"

export function useFeedbackMessages(opts) {
  function getOpts() {
    return Object.assign(opts, {
      group: false,
      actions: [
        {
          label: "Close",
          color: "white",
          "data-cy": "button_dismiss_notify",
        },
      ],
      timeout: 10000,
      progress: true,
      html: true,
    })
  }
  const { notify } = useQuasar()

  function newMessage(color, icon, message) {
    notify(Object.assign({ message, color, icon }, getOpts()))
  }

  function newStatusMessage(status, message) {
    const [color, icon] = {
      success: ["positive", "check_circle"],
      failure: ["negative", "error"],
    }[status]
    newMessage(color, icon, message)
  }

  return { newMessage, newStatusMessage }
}
