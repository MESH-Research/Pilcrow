import { mount } from 'vue-composable-tester';
import { useUserValidation } from './userValidation';
import { provide } from '@vue/composition-api';
import { DefaultApolloClient } from '@vue/apollo-composable';
import { createMockClient } from 'mock-apollo-client';
import { text } from 'body-parser';
import { CREATE_USER } from 'src/graphql/mutations';
import Vue from 'vue';
Vue.config.devtools = false
Vue.config.productionTip = false
describe('test uservalidation composable', () => {

    const mountComposable = () => {

        const mockClient = createMockClient();
        const { result } = mount(() => useUserValidation(), {
            provider: () => {
                provide(DefaultApolloClient, mockClient)
            }
        });
        return { mockClient, result };
    }

    test('locally validates username', () => {
        const { result: { $v, user }} = mountComposable();
        user.username = '';

        $v.value.$touch();
        expect($v.value.username.required.$invalid).toBeTruthy();
    })

    test('locally validates email', () => {
        const { result: { $v, user } } = mountComposable();
        const eV = $v.value.email;

        user.email = '';

        eV.$touch();
        expect(eV.required.$invalid).toBeTruthy();

        user.email = "notanemail";
        eV.$touch();
        expect(eV.email.$invalid).toBeTruthy();
        expect(eV.required.$invalid).toBeFalsy();

        user.email = 'isanemail@place.com';
        eV.$touch();
        expect(eV.email.$invalid).toBeFalsy();
        expect(eV.required.$invalid).toBeFalsy();
        expect(eV.$invalid).toBeFalsy();

    });

    text('locally validated password', () => {
        const { result: { $v, user } } = mountComposable();
        const eV = $v.value.password;

        user.password = "";
        eV.$touch();
        expect(eV.required.$invalid).toBeTruthy();
        expect(eV.complexity.$invalid).toBeTruthy();

        user.password = "password";
        eV.$touch();
        expect(eV.complexity.$invalid).toBeTruthy();
        expect(eV.required.$invalid).toBeTruthy();

        user.password = "albancub4Grac&"
        expect(eV.complexity.$invalid).toBeFalsy();
        expect(eV.$invalid).toBeFalsy();
    });

    test('external validated data', async () => {
        const { mockClient, result: { $v, user, saveUser } } = mountComposable();
        Object.assign(user, {
            username: "user",
            password: "albancub4Grac&",
            name: "Joe Doe",
            email: "test@example.com"
        });

        const error = {
            errors: [
                {
                    "message": "Validation failed for the field [createUser].",
                    "extensions": {
                        "validation": {
                            "user.username": [
                                "USERNAME_IN_USE"
                            ],
                            "user.email": [
                                "EMAIL_IN_USE"
                            ]
                        },
                        "category": "validation"
                    },
                    "locations": [
                        {
                            "line": 2,
                            "column": 3
                        }
                    ],
                    "path": [
                        "createUser"
                    ],
                }
            ]
        };

        mockClient.setRequestHandler(CREATE_USER, jest.fn().mockResolvedValue(error));

        await expect(saveUser())
            .rejects
            .toThrow("CREATE_FORM_VALIDATION");

        expect($v.value.username.$externalResults[0].$message).toEqual("USERNAME_IN_USE");
        expect($v.value.email.$externalResults[0].$message).toEqual("EMAIL_IN_USE");

        user.username = "anotherusername";
        await Vue.nextTick();
        expect($v.value.username.$externalResults.length).toBe(0)

        user.email = "email@example.com";
        await Vue.nextTick();
        expect($v.value.email.$externalResults.length).toBe(0)

    });

    test('external error', async () => {
        const { mockClient, result: { user, saveUser } } = mountComposable();
        Object.assign(user, {
            username: "user",
            password: "albancub4Grac&",
            name: "Joe Doe",
            email: "test@example.com"
        });

        mockClient.setRequestHandler(CREATE_USER, jest.fn().mockRejectedValue({}));

        await expect(saveUser())
            .rejects
            .toThrow("CREATE_FORM_INTERNAL");
    });
})
