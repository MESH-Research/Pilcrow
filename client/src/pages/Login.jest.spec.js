import LoginPage from "./Login.vue";
import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import { LOGIN } from 'src/graphql/mutations';
import { createMockClient } from 'mock-apollo-client';
import { DefaultApolloClient } from '@vue/apollo-composable';
import * as All from 'quasar';

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key];
  if (val && val.component && val.component.name != null) {
    object[key] = val;
  }
  return object;
}, {});

jest.mock('quasar', () => ({
  ...jest.requireActual('quasar'),
  SessionStorage: {
    remove: jest.fn(),
    getItem: jest.fn()
  },
}))



describe("LoginPage", () => {

  const apolloProvider = {};
  const mockClient = createMockClient();
  apolloProvider[DefaultApolloClient] = mockClient;

  const wrapper = mountQuasar(LoginPage, {
    quasar: {
      components
    },
    mount: {
      provide: apolloProvider,
      type: "shallow",
      mocks: {
        $t: token => token,
      },
      stubs: ["router-link"]
    }
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });

  test("login action attempts mutation", async () => {
    const mutationHandler = jest.fn().mockResolvedValue({ data: { login: { user: { id: 1 } } } });
    mockClient.setRequestHandler(LOGIN, mutationHandler);

    wrapper.vm.$v.email.$model = 'user@example.com';
    wrapper.vm.$v.password.$model = 'password';

    await wrapper.vm.handleSubmit();
    expect(mutationHandler).toBeCalled();
  });
});
