import { mountQuasar } from '@quasar/quasar-app-extension-testing-unit-jest';
import RegisterPage from "./Register.vue";
import { CREATE_USER, LOGIN } from 'src/graphql/mutations';
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


describe("RegisterPage", () => {

  const wrapperFactory = (mocks = []) => {
    const apolloProvider = {};
    const mockClient = createMockClient();
    apolloProvider[DefaultApolloClient] = mockClient;

    mocks?.forEach((mock) => {
      mockClient.setRequestHandler(...mock)
    });

    return {
      wrapper: mountQuasar(RegisterPage, {
        quasar: {
          components
        },
        mount: {
        provide: apolloProvider,
        stubs: ['router-link'],
        mocks: {
          $t: token => token,
        },
      },
    }), mockClient};
  }



  it("mounts without errors", () => {
    expect(wrapperFactory().wrapper).toBeTruthy();
  });

  test("form submits on valid data", async () => {
    const { wrapper, mockClient } = wrapperFactory();

    const user = {
      username: "user",
      password: "albancub4Grac&",
      name: "Joe Doe",
      email: "test@example.com"
    };

    const mutateHandler = jest.fn().mockResolvedValue({ id: 1, ...user });

    mockClient.setRequestHandler(CREATE_USER, mutateHandler)
    mockClient.setRequestHandler(LOGIN, jest.fn().mockResolvedValue({id: 1, ...user}));
    Object.assign(wrapper.vm.user, user);


    await wrapper.vm.handleSubmit();
    expect(wrapper.vm.formErrorMsg.value).toBeFalsy();
    expect(mutateHandler).toBeCalled();
  });

});
