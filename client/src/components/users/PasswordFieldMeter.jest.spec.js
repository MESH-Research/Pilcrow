import { mount } from "@vue/test-utils";
import PasswordFieldMeter from "./PasswordFieldMeter.vue";

describe("PasswordFieldMeterComponent", () => {
  const wrapper = mount(PasswordFieldMeter, {
    propsData: {
      max: 4,
      valid: false
    }
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();

    const div = wrapper.find(".password-meter");
    expect(div.element.tagName).toBe("DIV");
  });

  it("has correct number of child divs", async () => {
    const bars = wrapper.findAll(".col");
    await wrapper.setProps({ max: 4 });
    expect(wrapper.findAll(".col").length).toBe(4);

    await wrapper.setProps({ max: 10 });
    expect(wrapper.findAll(".col").length).toBe(10);
  });

  it("has correct active divs", async () => {
    const bars = wrapper.findAll(".col");

    await wrapper.setProps({ max: 4, score: 0 });
    expect(bars.filter(w => w.classes("active")).length).toBe(0);

    await wrapper.setProps({ max: 4, score: 2 });
    expect(bars.filter(w => w.classes("active")).length).toBe(2);
  });

  it("changes class when valid", async () => {
    await wrapper.setProps({ valid: true });
    expect(wrapper.classes("password-success")).toBe(true);
    expect(wrapper.classes("password-error")).toBe(false);

    await wrapper.setProps({ valid: false });
    expect(wrapper.classes("password-success")).toBe(false);
    expect(wrapper.classes("password-error")).toBe(true);
  });
});
