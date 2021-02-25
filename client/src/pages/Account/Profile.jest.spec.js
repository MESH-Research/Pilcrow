import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import Profile from "./Profile.vue";

import * as All from "quasar";

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key];
  if (val.component?.name != null) {
    object[key] = val;
  }
  return object;
}, {});

describe("Profile", () => {
  const wrapper = mountQuasar(Profile, {
    quasar: { components },
    mount: {
      type: "full",
    }
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });
});

