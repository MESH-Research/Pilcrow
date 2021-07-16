import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import EmailVerificationSendButton from "./EmailVerificationSendButton.vue";
import { QIcon, QBtn, QSpinnerHourglass } from "quasar";

const mutate = jest.fn();
const notify = jest.fn();
describe("EmailVerificationSendButton", () => {
  const wrapper = mountQuasar(EmailVerificationSendButton, {
    quasar: {
      components: { QIcon, QBtn, QSpinnerHourglass },
    },
    mount: {
      type: "full",
      mocks: {
        $t: (token) => token,
        $apollo: {
          mutate,
        },
      },
    },
  });

  wrapper.vm.$q.notify = notify;

  beforeEach(async () => {
    mutate.mockReset();
    notify.mockReset();
    await wrapper.setData({ status: null });
  });

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy();
  });

  it("changes state on success", async () => {
    mutate.mockResolvedValue({
      data: { sendEmailVerification: { email: "test@example.com" } },
    });

    expect(wrapper.text()).toMatch(/resend_button$/);

    await wrapper.trigger("click");

    expect(wrapper.text()).toMatch(/resend_button_success$/);
    expect(notify.mock.calls[0][0].color).toBe("positive");
    expect(mutate).toBeCalled();

    const btn = wrapper.findComponent(QBtn);
    expect(btn.props("color")).toBe("positive");

    await wrapper.setProps({ noColor: true });
    expect(btn.props("color")).toBeNull();
  });

  it("returns to state on failure", async () => {
    mutate.mockRejectedValue({});

    expect(wrapper.text()).toMatch(/resend_button$/);

    await wrapper.trigger("click");

    expect(mutate).toBeCalled();
    expect(wrapper.text()).toMatch(/resend_button$/);
    expect(notify.mock.calls[0][0].color).toBe("negative");
  });
});
