import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import RegisterPage from "./Register.vue";

import {
  QIcon,
  QCardSection,
  QInput,
  QCard,
  QCardActions,
  QBtn,
  QForm,
  QPage
} from "quasar";
describe("RegisterPage", () => {
  const mutate = jest.fn();

  const wrapper = mountQuasar(RegisterPage, {
    quasar: {
      components: {
        QIcon,
        QCardSection,
        QInput,
        QCard,
        QCardActions,
        QBtn,
        QForm,
        QPage
      }
    },
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

  test("isServerError method returns correct values", async () => {
    expect(wrapper.vm.isServerError("user.password", "SOME_ERROR")).toBe(false);

    expect(wrapper.vm.isServerError("user.password")).toBe(false);
    expect(wrapper.vm.isServerError("user.nonexistant_field")).toBe(false);

    await wrapper.setData({
      serverValidationErrors: { "user.password": ["COMPLEX_PASSWORD"] }
    });
    expect(wrapper.vm.isServerError("user.password", "COMPLEX_PASSWORD")).toBe(
      true
    );
    expect(wrapper.vm.isServerError("user.password")).toBe(true);
    expect(wrapper.vm.isServerError("user.password", "RANDOM_TOKEN")).toBe(
      false
    );
  });

  test("form submits on valid data", async () => {
    await wrapper.setData({
      serverValidationErrors: {},
      username: "user",
      password: "albancub4Grac&",
      name: "Joe Doe",
      email: "test@example.com"
    });

    mutate.mockClear().mockResolvedValue({});

    wrapper.vm.submit();
    expect(wrapper.vm.formErrorMsg).toBeFalsy();
    expect(mutate).toBeCalled();
  });

  test("password is correctly validated", async () => {
    await wrapper.setData({ password: "" });
    expect(wrapper.vm.$v.password.$invalid).toBeTruthy();

    await wrapper.setData({ password: "password" });
    expect(wrapper.vm.$v.password.$invalid).toBeTruthy();

    await wrapper.setData({ password: "albancub4Grac&" });
    expect(wrapper.vm.$v.password.$invalid).toBeFalsy();
  });

  test("username is correctly validated", async () => {
    await wrapper.setData({ username: "" });
    expect(wrapper.vm.$v.username.$invalid).toBeTruthy();

    await wrapper.setData({ username: "test" });
    expect(wrapper.vm.$v.username.$invalid).toBeFalsy();

    await wrapper.setData({
      username: "user",
      password: "albancub4Grac&",
      name: "Joe Doe",
      email: "test@example.com"
    });

    const error = {
      graphQLErrors: [
        {
          extensions: {
            validation: {
              "user.username": ["USERNAME_IN_USE"]
            }
          }
        }
      ]
    };
    mutate.mockClear().mockRejectedValue(error);

    await wrapper.vm.submit();
    expect(mutate).toBeCalled();

    expect(wrapper.vm.formErrorMsg).toBeTruthy();
  });

  test("email is correctly validated", async () => {
    await wrapper.setData({ email: "" });
    expect(wrapper.vm.$v.email.$invalid).toBe(true);

    await wrapper.setData({ email: "Notanemail" });
    expect(wrapper.vm.$v.email.$invalid).toBe(true);

    await wrapper.setData({ email: "test@example.com" });
    expect(wrapper.vm.$v.email.$invalid).toBe(false);
  });
});
