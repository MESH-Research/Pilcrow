import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import QInputPassword from "./QInputPassword.vue";

describe("QInputPasswordComponent", () => {
  const wrapper = mountQuasar(QInputPassword, {
    mount: {
      slots: {
        hint: "Hint Content",
        error: "Error Content",
      },
    },
    propsData: {
      "bottom-slots": true,
      error: true,
    },
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });

  it("uses hint slot for errors", async () => {
    expect(wrapper.html()).toContain("Hint Content");
    expect(wrapper.html()).not.toContain("Error Content");
  });
});
