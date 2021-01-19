import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import NewPasswordInput from "./NewPasswordInput.vue";
import { QIcon, QList, QItem, QItemSection, QChip } from "quasar";

describe("NewPasswordInput", () => {
  const wrapper = mountQuasar(NewPasswordInput, {
    quasar: {
      components: { QIcon, QItem, QItemSection, QList, QChip }
    },
    mount: {
      type: "full",

      mocks: {
        $t: (token, params) => token
      },
      propsData: {
        complexity: {
          score: 2,
          crack_times_display: {
            offline_slow_hashing_1e4_per_second: "1 week"
          },
          feedback: {
            suggestions: [],
            warning: ""
          }
        }
      }
    }
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });

  it("passes input event up component tree", async () => {
    const input = wrapper.find("input");

    await input.setValue("test");
    expect(wrapper.emitted("input")).toBeTruthy();
    expect(wrapper.emitted("input")[0]).toEqual(["test"]);
  });

  it("input has new-password auto-complete attr", () => {
    const input = wrapper.find("input");

    expect(input.attributes("autocomplete")).toEqual("new-password");
  });

  it("shows and hides details on click with correct aria", async () => {
    expect(wrapper.findAll(".password-details").length).toBe(0);

    const detailsChip = wrapper.findComponent(QChip);
    expect(detailsChip.attributes("aria-expanded")).toEqual("false");
    expect(detailsChip.attributes("aria-controls").length).toBeGreaterThan(0);
    await detailsChip.trigger("click");
    expect(detailsChip.attributes("aria-expanded")).toEqual("true");
    expect(wrapper.findAll(".password-details").length).toBe(1);
  });
});
