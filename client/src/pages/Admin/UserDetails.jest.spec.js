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
  const wrapperFactory = (userId) => {
    return mountQuasar(UserDetails, {
      quasar: {
        components
      },
      mount: {
        type: 'full',
        mocks: {
          $t: token => token,
          $apollo: {
            query
          },
          $route: {
            params: {
              id: userId
            }
          }
        },
        stubs: ["router-link"]
      }
    });
  }

  it ('mounts without errors', () => {
    expect(wrapperFactory(0)).toBeTruthy();
  });

  it('queries for a specific user', () => {
    query.mockClear();
    const wrapper = wrapperFactory(1);
    expect(wrapper).toBeTruthy();

    expect(
      wrapper
        .vm
        .$options
        .apollo
        .user
        .variables
        .bind(wrapper.vm)().id
    ).toBe(1)
  });

  // it('reflects the lack of roles for an regular user', () => {

  // it('reflects the role of an application administrator', async () => {
  //   query.mockClear();
  //   const wrapper = wrapperFactory(2);

  //   expect(wrapper).toBeTruthy();

  //   await wrapper.setData({
  //     user: {
  //       name:'Application Admin User ',
  //       roles: [
  //         {
  //           name: "Application Administrator"
  //         }
  //       ]
  //     }
  //   });
  //   expect(wrapper.findAll('[data-roles="has_roles"]').toBeTruthy())
  // });

});
