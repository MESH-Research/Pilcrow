import { shallowMount } from "@vue/test-utils";
import PasswordFieldAnalysis from "./NewPasswordInputAnalysis.vue";
import { merge } from "lodash";
import { QIcon, QList, QItem, QItemSection } from "quasar";
describe("NewPasswordInputAnalysis", () => {
  const mergeProps = (props = {}) => {
    return merge(
      {
        complexity: {
          score: 2,
          crack_times_display: {
            offline_slow_hashing_1e4_per_second: "1 week"
          },
          feedback: {
            warning: "warning_message",
            suggestions: ["suggestion_1", "suggestion_2"]
          }
        }
      },
      props
    );
  };

  const wrapper = shallowMount(PasswordFieldAnalysis, {
    components: { QIcon, QItem, QItemSection, QList },
    mocks: {
      $t: (token, params) => token
    },
    propsData: mergeProps()
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });

  it("correctly displays suggestions", async () => {
    expect(wrapper.findAll(".suggestion").length).toBe(2);

    await wrapper.setProps(
      mergeProps({
        complexity: {
          feedback: { suggestions: ["one", "second suggestions", "three"] }
        }
      })
    );
    expect(wrapper.vm.suggestions.length).toBe(3);
    const suggestions = wrapper.findAll(".suggestion");
    expect(suggestions.length).toBe(3);
    expect(suggestions.at(1).html()).toContain("second suggestion");
  });

  it("correctly displays warnings", async () => {
    expect(wrapper.vm.warning).toBe("warning_message");

    const warning = wrapper.findAll(".warning");
    expect(warning.length).toBe(1);
    expect(warning.at(0).html()).toContain("warning_message");

    await wrapper.setProps(
      mergeProps({ complexity: { feedback: { warning: "" } } })
    );

    expect(wrapper.findAll(".warning").length).toBe(0);
  });
});
