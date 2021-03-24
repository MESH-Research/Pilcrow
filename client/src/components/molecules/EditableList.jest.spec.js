import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest";
import EditableList from "./EditableList.vue";
import { QIcon, QBtn, QItem, QList, QItemLabel, QInput, QItemSection } from "quasar";

describe("EditableList Component", () => {
  const factory = (value) => {
    return mountQuasar(EditableList, {
        quasar: {
          components: { QIcon, QBtn, QItem, QList, QItemLabel, QInput, QItemSection }
      },
      mount: {
        type: "full",
        mocks: {
          $t: token => token
        },
        propsData: {
          value
        }
      }
    });
  }

  const findByAria = (wrapper, label) => {
    return wrapper.find(`[aria-label="${label}"]`);
  };

  it("mounts without errors", () => {
    const wrapper = factory([]);
    expect(wrapper).toBeTruthy();
  });

  it('renders list items', async () => {
    const wrapper = factory(['a', 'b', 'c']);

    expect(wrapper.findAllComponents({ name: 'q-item' })).toHaveLength(3)
  });

  it("adds items to list", async () => {
    const wrapper = factory([]);
    const inputWrapper = wrapper.find('input')

    await inputWrapper.setValue('newItem');
    await wrapper.findComponent({ ref: "addBtn" }).trigger('click');

    expect(wrapper.emitted('input')[0][0]).toHaveLength(1);
    expect(wrapper.emitted('input')[0][0]).toEqual(['newItem']);

    await wrapper.setProps({ value: ['item'] });
    await inputWrapper.setValue('another new item');
    await wrapper.findComponent({ ref: 'addBtn' }).trigger('click');

    expect(wrapper.emitted('input')[1][0]).toHaveLength(2);
    expect(wrapper.emitted('input')[1][0]).toEqual(['item', 'another new item']);

  });

  it('deletes list items', async () => {
    const wrapper = factory(['a', 'b', 'c']);

    const items = wrapper.findAllComponents({ name: 'q-item' });
    expect(items).toHaveLength(3);

    await findByAria(items.at(1), "lists.delete").trigger('click');
    expect(wrapper.emitted('input')[0][0]).toEqual(['a', 'c']);

    await findByAria(items.at(0), "lists.delete").trigger('click');
    expect(wrapper.emitted('input')[1][0]).toEqual(['b', 'c']);

    await findByAria(items.at(2), "lists.delete").trigger('click');
    expect(wrapper.emitted('input')[2][0]).toEqual(['a', 'b']);
  });

  it('moves items', async () => {
    const wrapper = factory(['a', 'b', 'c']);

    const items = wrapper.findAllComponents({ name: 'q-item' });

    await findByAria(items.at(0), "lists.move_down").trigger('click');
    expect(wrapper.emitted('input')[0][0]).toEqual(['b', 'a', 'c']);

    await findByAria(items.at(0), "lists.move_up").trigger('click');
    expect(wrapper.emitted('input')).toHaveLength(1);

    await findByAria(items.at(2), "lists.move_down").trigger('click');
    expect(wrapper.emitted('input')).toHaveLength(1);

    await findByAria(items.at(2), "lists.move_up").trigger('click');
    expect(wrapper.emitted('input')[1][0]).toEqual(['a', 'c', 'b']);
  });

  it('edits items', async () => {
    const wrapper = factory(['a', 'b', 'c']);

    const items = wrapper.findAllComponents({ name: 'q-item' });
    await findByAria(items.at(1), 'lists.edit').trigger('click');

    await items.at(1).find('input').setValue('d');
    await findByAria(items.at(1), 'lists.save').trigger('click');

    expect(wrapper.emitted('input')[0][0]).toEqual(['a', 'd', 'c']);
  })

  it('cancels edit', async () => {
    const wrapper = factory(['a', 'b', 'c']);

    const items = wrapper.findAllComponents({ name: 'q-item' });
    await findByAria(items.at(1), 'lists.edit').trigger('click');
    await items.at(1).find('input').setValue('d');

    await findByAria(items.at(1), 'lists.cancel').trigger('click');

    expect(wrapper.emitted('input')).toBeUndefined();
  });

  test('label click triggers edit', async () => {
    const wrapper = factory(['a', 'b', 'c']);

    const items = wrapper.findAllComponents({ name: 'q-item' });
    await items.at(1).findComponent({ name: 'q-item-label' }).trigger('click');

    expect(items.at(1).findAll('input')).toHaveLength(1);
  });

  it('does not add duplicates', async () => {
    const wrapper = factory([]);

    await wrapper.setProps({ value: ['a', 'b', 'c'], allowDuplicates: false });

    await wrapper.find('input').setValue('a');
    await wrapper.findComponent({ ref: 'addBtn' }).trigger('click');

    expect(wrapper.emitted('input')).toBeUndefined();
  });

  it('can allow duplicates', async () => {
    const wrapper = factory([]);

    await wrapper.setProps({ value: ['a', 'b', 'c'], allowDuplicates: true });

    await wrapper.find('input').setValue('a');
    await wrapper.findComponent({ ref: 'addBtn' }).trigger('click');

    expect(wrapper.emitted('input')[0][0]).toEqual(['a', 'b', 'c', 'a']);
  });
});
