<script lang="ts">
import { defineComponent, h } from "vue"

export default defineComponent({
  name: "CollapseToolbar",
  props: {
    collapse: {
      type: Boolean,
      default: false
    }
  },
  render() {
    if (this.$props.collapse) {
      const items = this.$slots.default!().map((vn) =>
        h("q-item", {}, [h("q-item-section", {}, [vn])])
      )
      return h(
        "q-btn-dropdown",
        { props: { "dropdown-icon": "more_horiz", flat: true, dense: true } },
        [h("q-list", {}, items)]
      )
    } else {
      return h("div", {}, this.$slots.default!())
    }
  }
})
</script>
