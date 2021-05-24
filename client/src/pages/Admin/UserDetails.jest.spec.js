import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import UsersDetails from "./UsersDetails.vue";

import * as All from "quasar";

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key];
  if (val.component?.name != null) {
  object[key] = val;
  }
  return object;
}, {});

describe('User Details page mount', () => {
  const query = jest.fn();
  const wrapper = mountQuasar(UsersDetails, {
  quasar: {
    components
  },
  mount: {
    type: 'shallow',
    mocks: {
      $apollo: {
        query
      }
    }
  }
  });
  it ('mounts without errors', () => {
    expect(wrapper).toBeTruthy();
  });
});

