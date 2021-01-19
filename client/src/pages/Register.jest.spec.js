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
        $t: token => token
      },
      stubs: ["router-link"]
    }
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });
});
