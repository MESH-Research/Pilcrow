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
  const mutate = jest.fn();
  const wrapper = mountQuasar(Profile, {
    quasar: { components },
    mount: {
      type: "shallow",
      mocks: {
        $t: token => token,
        $apollo: {
          mutate
        }
      },
      stubs: ["router-link"]
    }
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });

  test("form submits valid data", async () => {
    await wrapper.setData({
      serverValidationErrors: {},
      form: {
        name: "Joe Doe",
        username: "joedoe",
        password: "albancub4Grac&",
        email: "joedoe@example.com"
      }
    });

    mutate.mockClear().mockResolvedValue({});

    wrapper.vm.updateUser();
    expect(wrapper.vm.formErrorMsg).toBeFalsy();
    expect(mutate).toBeCalled();
  });
});
