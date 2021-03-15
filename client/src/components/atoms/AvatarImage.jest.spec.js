import AvatarImage from "./AvatarImage.vue";

import { shallowMount } from "@vue/test-utils";


describe("AvatarImage Component", () => {
    const factory = (email) => {
        return shallowMount(AvatarImage, {
            propsData: {
                user: {
                    email
                }
            }
        });
    }

    it('returns a deterministic value', () => {
        const wrapper = factory('test@ccrproject.dev');

        expect(wrapper.vm.avatarSrc).toBe('avatar-yellow.png');
    });

})