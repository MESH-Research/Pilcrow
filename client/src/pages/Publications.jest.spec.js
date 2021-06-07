import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import PublicationsPage from "./Publications.vue";

import * as All from "quasar";

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key];
  if (val.component?.name != null) {
  object[key] = val;
  }
  return object;
}, {});

const query = jest.fn();
const notify = jest.fn();

describe('publications page mount', () => {
  const wrapper = mountQuasar(PublicationsPage, {
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
    }
  });

  wrapper.vm.$q.notify = notify;

  it('mounts without errors', () => {
    expect(wrapper).toBeTruthy();
  });

  test('all existing publications appear within the list', async () => {
    await wrapper.setData({
      publications: {
        data: [
          {id:'1',name:'Sample Jest Publication 1'},
          {id:'2',name:'Sample Jest Publication 2'},
          {id:'3',name:'Sample Jest Publication 3'},
          {id:'4',name:'Sample Jest Publication 4'},
        ],
      }
    });
    expect(wrapper.findAllComponents({name: 'q-item'})).toHaveLength(4);
  });
});

