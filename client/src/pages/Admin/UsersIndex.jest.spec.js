import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import { DefaultApolloClient } from "@vue/apollo-composable";
import { createMockClient } from "mock-apollo-client";
import UsersIndexPage from "./UsersIndex.vue";
import { GET_USERS } from "../../graphql/queries";
import Vue from "vue";

import * as All from "quasar";

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key];
  if (val.component?.name != null) {
    object[key] = val;
  }
  return object;
}, {});

const wrapperFactory = (mocks) => {
  const apolloProvider = {};
  const mockClient = createMockClient();
  apolloProvider[DefaultApolloClient] = mockClient;

  mocks?.forEach((mock) => {
    mockClient.setRequestHandler(...mock);
  });

  return mountQuasar(UsersIndexPage, {
    quasar: {
      components,
    },
    //  plugins: [VueCompositionAPI],
    mount: {
      provide: apolloProvider,
      type: "shallow",
    },
  });
};
describe("User Index page mount", () => {
  it("mounts without errors", () => {
    expect(wrapperFactory([])).toBeTruthy();
  });
  test("users are populated on the page", async () => {
    const getUserHandler = jest.fn().mockResolvedValue({
      data: {
        userSearch: {
          data: [
            { name: "test1", email: "test1@msu.edu" },
            { name: "test2", email: "test2@msu.edu" },
          ],
          paginatorInfo: { lastPage: 10 },
        },
      },
    });
    const wrapper = wrapperFactory([[GET_USERS, getUserHandler]]);
    expect(getUserHandler).toBeCalledWith({ page: 1 });
    await Vue.nextTick();
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(2);
  });
});
