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
    if (this.$props.collapse) {
      const items = this.$slots
        .default()
        .map((vn) =>
          h("q-item", { directives: { ClosePopup } }, [
            h("q-item-section", {}, [vn]),
          ])
        )
      return h(
        "q-btn-dropdown",
        { props: { "dropdown-icon": "more_horiz", flat: true, dense: true } },
        [h("q-list", {}, items)]
      )
    } else {
      return this.$slots.default()
    }
  },
}
</script>
