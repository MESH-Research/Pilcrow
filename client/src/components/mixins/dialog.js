export default {
  methods: {
    // following method is REQUIRED
    // (don't change its name --> "show")
    show: function() {
      this.$refs.dialog.show();
    },

    // following method is REQUIRED
    // (don't change its name --> "hide")
    hide: function() {
      this.$refs.dialog.hide();
    },

    onDialogHide: function() {
      // required to be emitted
      // when QDialog emits "hide" event
      this.$emit("hide");
    },

    onOKClick: function() {
      // on OK, it is REQUIRED to
      // emit "ok" event (with optional payload)
      // before hiding the QDialog
      this.$emit("ok");
      // or with payload: this.$emit('ok', { ... })

      // then hiding dialog
      this.hide();
    },

    onCancelClick: function() {
      // we just need to hide dialog
      this.hide();
    }
  }
};
