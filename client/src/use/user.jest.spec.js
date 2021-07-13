import { mount } from "vue-composable-tester";
import { createMockClient } from "mock-apollo-client";
import { useCurrentUser } from './user';
import { DefaultApolloClient } from '@vue/apollo-composable';
import { CURRENT_USER } from 'src/graphql/queries';
import { provide } from '@vue/composition-api';
import Vue from 'vue';
import { nextTick } from 'vue';

Vue.config.productionTip = false;
Vue.config.devtools = false;
describe("useCurrentUser composable", () => {
    const mountComposable = (mocks) => {

        const mockClient = createMockClient();
        mocks.forEach(m => mockClient.setRequestHandler(...m));
        const { result } = mount(() => useCurrentUser(), {
            provider: () => {
                provide(DefaultApolloClient, mockClient)
            }
        });
        return { mockClient, result };
    };

    test('when a user is not logged in', () => {
        const { result } = mountComposable([[CURRENT_USER, jest.fn().mockResolvedValue({ data: { currentUser: null } })]])

        expect(result.currentUser.value).toBeNull();
        expect(result.isLoggedIn.value).toBe(false);
        expect(result.can.value('doSomething')).toBe(false);
        expect(result.hasRole.value('someRole')).toBe(false);
    })

    test('when a user is logged in', async () => {
        const response = {
            data: {
                currentUser: {
                    id: 1,
                    name: 'Hello',
                    email: 'hello@example.com',
                    roles: ['tester'],
                    abilities: ['doStuff'],
                }
            }
        };

        const { result } = mountComposable([[CURRENT_USER, jest.fn().mockResolvedValue(response)]])
        await nextTick();

        expect(result.currentUser.value).not.toBeNull();
        expect(result.isLoggedIn.value).toBe(true);
        expect(result.can.value('doSomething')).toBe(false);
        expect(result.hasRole.value('someRole')).toBe(false);
        expect(result.can.value('doStuff')).toBe(true);
        expect(result.hasRole.value('tester')).toBe(true);
    })

});