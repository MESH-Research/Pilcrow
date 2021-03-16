import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import UsersIndexPage from "./UsersIndex.vue";

import * as All from "quasar";

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key];
  if (val.component?.name != null) {
  object[key] = val;
  }
  return object;
}, {});

describe('User Index page mount', () => {
  const query = jest.fn();
  const wrapper = mountQuasar(UsersIndexPage, {
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
  test ('users are populated on the page', async () => {
    await wrapper.setData({
      users: { 
        data: [
          {name:'test1', email:'test1@msu.edu'}, 
          {name:'test2', email:'test2@msu.edu'}
        ],
        paginatorInfo: { lastPage:10 }
      }
    });
    expect(wrapper.findAllComponents({name: 'q-item'})).toHaveLength(2);
  });
});

