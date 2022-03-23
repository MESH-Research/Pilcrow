<template>
  <div v-if="editor">
    <q-btn-group spread unelevated>
      <q-btn
        aria-label="Toggle Bold Selected Text"
        color="primary"
        outline
        size="sm"
        :class="{ 'is-active': editor.isActive('bold') }"
        @click="editor.chain().focus().toggleBold().run()"
      >
        <q-icon name="format_bold"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Toggle Italic Selected Text"
        color="primary"
        outline
        size="sm"
        :class="{ 'is-active': editor.isActive('italic') }"
        @click="editor.chain().focus().toggleItalic().run()"
      >
        <q-icon name="format_italic"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Toggle bulleted list"
        color="primary"
        outline
        size="sm"
        :class="{ 'is-active': editor.isActive('bulletList') }"
        @click="editor.chain().focus().toggleBulletList().run()"
      >
        <q-icon name="list"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Toggle numbered list"
        color="primary"
        outline
        size="sm"
        :class="{ 'is-active': editor.isActive('orderedList') }"
        @click="editor.chain().focus().toggleOrderedList().run()"
      >
        <q-icon name="format_list_numbered"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Indent list item"
        color="primary"
        outline
        size="sm"
        :disabled="!editor.can().sinkListItem('listItem')"
        @click="editor.chain().focus().sinkListItem('listItem').run()"
      >
        <q-icon name="format_indent_increase"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Unindent list item"
        color="primary"
        outline
        size="sm"
        :disabled="!editor.can().liftListItem('listItem')"
        @click="editor.chain().focus().liftListItem('listItem').run()"
      >
        <q-icon name="format_indent_decrease"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Insert a link"
        color="primary"
        outline
        size="sm"
        :class="{ 'is-active': editor.isActive('link') }"
        @click="setLink"
      >
        <q-icon name="insert_link"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Unset a link"
        color="primary"
        outline
        size="sm"
        :disabled="!editor.isActive('link')"
        @click="editor.chain().focus().unsetLink().run()"
      >
        <q-icon name="link_off"></q-icon>
      </q-btn>
    </q-btn-group>
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
      content: "<p>I’m running Tiptap with Vue.js.</p>",
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

    function setLink() {
      const previousUrl = editor.value.getAttributes("link").href
      const url = window.prompt("URL", previousUrl)

      // cancelled
      if (url === null) {
        return
      }

      // empty
      if (url === "") {
        editor.value.chain().focus().extendMarkRange("link").unsetLink().run()

        return
      }

      // update link
      editor.value
        .chain()
        .focus()
        .extendMarkRange("link")
        .setLink({ href: url })
        .run()
    }

    return { editor, setLink }
  },
}
</script>
