<script>
import { QInput } from "quasar";
import { slot } from "quasar/src/utils/slot.js";

export default {
  name: "QInputPassword",
  extends: QInput,
  methods: {
    __getBottom(h) {
      let msg, key;

      if (this.hideHint !== true || this.focused === true) {
        if (this.hint !== void 0) {
          msg = [h("div", [this.hint])];
          key = this.hint;
        } else {
          msg = slot(this, "hint");
          key = "q--slot-hint";
        }
      }

      const hasCounter =
        this.counter === true || this.$scopedSlots.counter !== void 0;

      if (
        this.hideBottomSpace === true &&
        hasCounter === false &&
        msg === void 0
      ) {
        return;
      }

      const main = h(
        "div",
        {
          key,
          staticClass: "q-field__messages col"
        },
        msg
      );

      return h(
        "div",
        {
          staticClass:
            "q-field__bottom row items-start q-field__bottom--" +
            (this.hideBottomSpace !== true ? "animated" : "stale")
        },
        [
          this.hideBottomSpace === true
            ? main
            : h(
                "transition",
                { props: { name: "q-transition--field-message" } },
                [main]
              ),

          hasCounter === true
            ? h(
                "div",
                {
                  staticClass: "q-field__counter"
                },
                this.$scopedSlots.counter !== void 0
                  ? this.$scopedSlots.counter()
                  : [this.computedCounter]
              )
            : null
        ]
      );
    }
  }
};
</script>
