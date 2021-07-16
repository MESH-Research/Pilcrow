import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";

import PasswordInput from "./PasswordInput.vue";

import { QIcon } from "quasar";
describe("PasswordInputComponent", () => {
  const wrapper = mountQuasar(PasswordInput, {
    quasar: {
      components: { QIcon },
    },
    mount: {
      type: "full",
      mocks: {
        $t: (token) => token,
      },
    },
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });

  it("uses input type password", () => {
    expect(wrapper.find("input").attributes("type")).toBe("password");
  });

  it("has correct aria attributes", () => {
    const i = wrapper.find("i");
    const iAttrs = i.attributes();
    expect(iAttrs.role).toBe("button");
    expect(iAttrs["aria-hidden"]).toBe("false");
    expect(iAttrs["aria-pressed"]).toBe("false");
    expect(iAttrs.tabindex).toBe("0");
    expect(iAttrs["aria-label"]).toBeTruthy();
    expect(i.classes("cursor-pointer")).toBe(true);
  });

  it("switches input type when visibility button clicked", async () => {
    await wrapper.findComponent(QIcon).trigger("click");

    expect(wrapper.find("input").attributes("type")).toBe("text");
    expect(wrapper.find("i").attributes("aria-pressed")).toBe("true");
  });

  it("passes input event up the component tree", async () => {
    const input = wrapper.find("input");

    input.setValue("test");
    expect(wrapper.emitted("input")[0]).toEqual(["test"]);
  });

  it("has current-password autocomplete attr by default", () => {
    expect(wrapper.find("input").attributes("autocomplete")).toEqual(
      "current-password"
    );
  });
});
