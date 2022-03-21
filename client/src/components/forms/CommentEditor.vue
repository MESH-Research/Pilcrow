<template>
  <div v-if="editor">
    <button
      aria-label="Toggle Bold Selected Text"
      :class="{ 'is-active': editor.isActive('bold') }"
      @click="editor.chain().focus().toggleBold().run()"
    >
      <q-icon name="format_bold"></q-icon>
    </button>
    <button
      aria-label="Toggle Italic Selected Text"
      :class="{ 'is-active': editor.isActive('italic') }"
      @click="editor.chain().focus().toggleItalic().run()"
    >
      <q-icon name="format_italic"></q-icon>
    </button>
    <button
      :class="{ 'is-active': editor.isActive('bulletList') }"
      @click="editor.chain().focus().toggleBulletList().run()"
    >
      toggleBulletList
    </button>
    <button
      :class="{ 'is-active': editor.isActive('orderedList') }"
      @click="editor.chain().focus().toggleOrderedList().run()"
    >
      toggleOrderedList
    </button>
    <button
      :disabled="!editor.can().splitListItem('listItem')"
      @click="editor.chain().focus().splitListItem('listItem').run()"
    >
      splitListItem
    </button>
    <button
      :disabled="!editor.can().sinkListItem('listItem')"
      @click="editor.chain().focus().sinkListItem('listItem').run()"
    >
      sinkListItem
    </button>
    <button
      :disabled="!editor.can().liftListItem('listItem')"
      @click="editor.chain().focus().liftListItem('listItem').run()"
    >
      liftListItem
    </button>
    <button :class="{ 'is-active': editor.isActive('link') }" @click="setLink">
      setLink
    </button>
    <button
      :disabled="!editor.isActive('link')"
      @click="editor.chain().focus().unsetLink().run()"
    >
      unsetLink
    </button>
    <editor-content :editor="editor" />
  </div>
</template>

<script>
import { useEditor, EditorContent } from "@tiptap/vue-3"
import StarterKit from "@tiptap/starter-kit"
import Bold from "@tiptap/extension-bold"
import Italic from "@tiptap/extension-italic"
import BulletList from "@tiptap/extension-bullet-list"
import OrderedList from "@tiptap/extension-ordered-list"
import Link from "@tiptap/extension-link"

export default {
  components: {
    EditorContent,
  },

  setup() {
    const editor = useEditor({
      content: "<p>I’m running Tiptap with Vue.js. 🎉</p>",
      extensions: [
        StarterKit,
        Bold,
        Italic,
        BulletList,
        OrderedList,
        Link.configure({
          openOnClick: false,
        }),
      ],
    })

    return { editor }
  },
}
</script>
