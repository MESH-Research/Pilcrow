import { vi, afterEach } from "vitest"

const Dialog = {
  ok: true,
  value: undefined,
  resolveOk: function (value) {
    this.value = value
    this.ok = true
  },
  resolveCancel: function () {
    this.ok = false
  },
  dialog: vi.fn(function () {
    return {
      onOk: function (cb) {
        if (Dialog.ok) {
          cb(Dialog.value)
        }
        return this
      },
      onCancel: function (cb) {
        if (!Dialog.ok) {
          cb()
        }
        return this
      }
    }
  }),
  install({ $q }) {
    $q.dialog = this.dialog
  }
}

afterEach(() => {
  Dialog.dialog.mockClear()
})
export default Dialog
