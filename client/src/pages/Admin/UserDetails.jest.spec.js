import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import UserDetails from "./UserDetails.vue";

import * as All from "quasar";

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key];
  if (val.component?.name != null) {
  object[key] = val;
  }
  return object;
}, {});

const query = jest.fn();

describe('User Details page mount', () => {
  const wrapper = mountQuasar(UserDetails, {
    quasar: {
      components
    },
    mount: {
      type: 'full',
      mocks: {
        $t: token => token,
        $apollo: {
          query
        }
      }
    },
    stubs: ["router-link"]
  });

  it ('mounts without errors', () => {
    expect(wrapper).toBeTruthy();
  });

});

