<script>
import { ClosePopup } from "quasar"
import { h } from "vue"
export default {
  name: "CollapseToolbar",
  props: {
    collapse: {
      type: Boolean,
      default: false,
    },
  },
  render: function () {
    const items = this.collapse
      ? this.$slots
          .default()
          .map((vn) =>
            h("q-item", { directives: { ClosePopup } }, [
              h("q-item-section", {}, [vn]),
            ])
          )
      : this.$slots.default()
    return this.collapse
      ? h(
          "q-btn-dropdown",
          { props: { "dropdown-icon": "more_horiz", flat: true, dense: true } },
          [h("q-list", {}, items)]
        )
      : h("div", {}, items)
  },
}
</script>
