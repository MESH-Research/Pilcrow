import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import { Profile } from "./Profile.vue";

import DiscardChangesDialog from "components/dialogs/DiscardChangesDialog.vue";

describe("Profile", () => {
  const wrapper = mountQuasar(Profile, {
    quasar: {
      components: {
        DiscardChangesDialog
      }
    },
    mount: {
      type: "full",
    }
  });
});

it("mounts without errors", () => {
  expect(wrapper).toBeTruthy();
});
