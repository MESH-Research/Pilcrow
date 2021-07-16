import DiscardChangesDialog from "../dialogs/DiscardChangesDialog.vue";

export default {
  methods: {
    beforeUnload: function (e) {
      if (this.dirty) {
        e.preventDefault();
        e.returnValue = "";
      }
    },

    dirtyDialog: function () {
      return this.$q.dialog({
        component: DiscardChangesDialog,
      });
    },
  },
  beforeRouteLeave: function (to, from, next) {
    if (!this.dirty) {
      return next();
    }
    this.dirtyDialog()
      .onOk(function () {
        next();
      })
      .onCancel(function () {
        next(false);
      });
  },
  mounted: function () {
    window.addEventListener("beforeUnload", this.beforeUnload);
  },
  beforeDestroy: function () {
    window.removeEventListener("beforeUnload", this.beforeUnload);
  },
};
