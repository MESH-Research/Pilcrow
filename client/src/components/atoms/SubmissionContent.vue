<template>
  <div class="row items-center justify-end q-pa-md">
    <q-select
      v-model="selectedFont"
      outlined
      :options="fonts"
      label="Font"
      style="min-width: 150px"
    />
    <div class="q-ml-md">
      <q-btn
        aria-label="Decrease Font Size"
        round
        flat
        icon="remove_circle"
        color="white"
        text-color="grey-7"
      />
      <q-btn
        aria-label="Increase Font Size"
        round
        flat
        icon="add_circle"
        color="white"
        text-color="grey-7"
      />
      <q-btn
        size="sm"
        class="q-ml-md"
        aria-label="Toggle Dark Mode"
        round
        :icon="darkMode ? `dark_mode` : `light_mode`"
        color="white"
        text-color="grey-7"
        @click="toggleDarkMode()"
      />
    </div>
  </div>
  <article class="col-sm-9 submission-content">
    <editor-content :editor="editor" />
  </article>
</template>
<script setup>
import { ref } from "vue"
import { Editor, EditorContent } from "@tiptap/vue-3"
import Highlight from "@tiptap/extension-highlight"
import StarterKit from "@tiptap/starter-kit"

let darkMode = ref(true)
function toggleDarkMode() {
  darkMode.value = !darkMode.value
}
const fonts = ["Sans-serif", "Serif"]
let selectedFont = ref("San-serif")

const editor = new Editor({
  editable: false,
  content: `
    <h1>Et Sollicitudin Ac Orci</h1>
    <p>
      Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
      tempor incididunt ut labore et dolore magna aliqua. Commodo ullamcorper a
      lacus vestibulum sed arcu non odio. Nisl nunc mi ipsum faucibus. Sit amet
      justo donec enim diam vulputate ut. Eget lorem dolor sed viverra ipsum
      nunc. Ut tortor pretium viverra suspendisse potenti nullam ac tortor
      vitae.
      <mark>Et sollicitudin ac orci phasellus egestas tellus rutrum tellus. Et
        egestas quis ipsum suspendisse ultrices gravida. In est ante in nibh
        mauris cursus mattis.</mark>
      Amet purus gravida quis blandit turpis cursus. Auctor neque vitae tempus
      quam pellentesque nec nam aliquam sem. At consectetur lorem donec massa
      sapien faucibus. Et ultrices neque ornare aenean. Hac habitasse platea
      dictumst vestibulum rhoncus est pellentesque elit. Risus quis varius quam
      quisque id diam vel quam elementum. Blandit massa enim nec dui. Dui ut
      ornare lectus sit amet est placerat in egestas. Sit amet commodo nulla
      facilisi nullam vehicula ipsum.
    </p>
    <p>
      Dui id ornare arcu odio ut sem. Est ullamcorper eget nulla facilisi etiam
      dignissim diam quis. In nibh mauris cursus mattis molestie. Arcu dictum
      varius duis at consectetur lorem donec massa. Ultricies tristique nulla
      aliquet enim tortor.
    </p>
    <p>
      In egestas erat imperdiet sed euismod nisi porta lorem. Ut aliquam purus
      sit amet luctus venenatis. Sagittis eu volutpat odio facilisis. Vitae
      congue eu consequat ac. Cursus sit amet dictum sit amet.
    </p>
    <p>
      Nibh tellus molestie nunc non blandit massa enim. Et tortor consequat id
      porta nibh venenatis. Dictum at tempor commodo ullamcorper. Placerat orci
      nulla pellentesque dignissim. Rhoncus dolor purus non enim praesent
      elementum facilisis.
    </p>
    <h2>Venenatis urna</h2>
    <p>
      Justo laoreet sit amet cursus sit. Ultrices neque ornare aenean euismod.
      Eget aliquet nibh praesent tristique magna sit. Aliquam nulla facilisi
      cras fermentum odio eu feugiat. Enim praesent elementum facilisis leo vel
      fringilla est ullamcorper. Arcu ac tortor dignissim convallis aenean et
      tortor at risus. Tincidunt augue interdum velit euismod in pellentesque
      massa placerat. Nisl nunc mi ipsum faucibus. Eu feugiat pretium nibh
      ipsum. Donec et odio pellentesque diam volutpat. Nunc sed velit dignissim
      sodales ut. Venenatis urna cursus eget nunc scelerisque viverra mauris.
      Sem viverra aliquet eget sit amet tellus. Magna fringilla urna porttitor
      rhoncus dolor purus non enim. In nisl nisi scelerisque eu ultrices. Tempor
      commodo ullamcorper a lacus vestibulum. Nisl nisi scelerisque eu ultrices
      vitae auctor eu. Urna id volutpat
      <mark>lacus laoreet non curabitur. Dolor magna eget est lorem ipsum dolor.
        Mauris vitae ultricies leo integer malesuada nunc vel risus
        commodo.</mark>
    </p>
    <h3>Commodo quis</h3>
    <p>
      Odio euismod lacinia at quis risus sed vulputate odio. Aliquam eleifend mi
      in nulla. Ornare arcu odio ut sem nulla pharetra diam sit amet. Nulla
      pharetra diam sit amet. Faucibus ornare suspendisse sed nisi lacus sed.
      Commodo quis imperdiet massa tincidunt nunc pulvinar sapien. Egestas
      tellus rutrum tellus pellentesque eu tincidunt tortor. Pellentesque elit
      eget gravida cum sociis natoque. Ut sem nulla pharetra diam sit amet. Sed
      lectus vestibulum mattis ullamcorper. Sit amet nisl purus in mollis nunc
      sed id semper. Non tellus orci ac auctor. In egestas erat imperdiet sed
      euismod.
    </p>
    <h3>Facilisi nullam vehicula ipsum a arcu</h3>
    <p>
      Blandit aliquam etiam erat velit scelerisque in dictum. Euismod quis
      viverra nibh cras pulvinar mattis nunc sed blandit. Risus feugiat in ante
      metus dictum at tempor. Facilisi nullam vehicula ipsum a arcu cursus vitae
      congue mauris. Sit amet facilisis magna etiam tempor
    </p>
    <p>
      orci eu lobortis. At quis risus sed vulputate odio ut enim blandit
      volutpat. Tempor id eu nisl nunc mi. Malesuada nunc vel risus commodo
      viverra maecenas accumsan lacus. Porttitor leo a diam sollicitudin tempor.
      Blandit massa enim nec dui nunc mattis enim. Elementum nisi quis eleifend
      quam adipiscing vitae proin sagittis. Placerat orci nulla pellentesque
      dignissim. Condimentum id venenatis a condimentum vitae sapien
      pellentesque.
    </p>
      `,
  extensions: [StarterKit, Highlight],
})
editor.registerPlugin(testPlugin)
</script>

<script>
import { Plugin } from "prosemirror-state"
import { Decoration, DecorationSet } from "prosemirror-view"

function getDecorations(doc) {
  let decos = [
    Decoration.inline(8, 25, { class: "comment-highlight" }),
    Decoration.inline(20, 43, { class: "comment-highlight2" }),
    Decoration.inline(100, 122, { class: "comment-highlight" }),
    Decoration.inline(300, 322, { class: "comment-highlight2" }),
    Decoration.widget(300, lintIcon()),
    Decoration.widget(302, lintIcon()),
  ]
  return DecorationSet.create(doc, decos)
}
function lintIcon() {
  let icon = document.createElement("div")
  icon.className = "lint-icon"

  return icon
}

let testPlugin = new Plugin({
  state: {
    init(_, { doc }) {
      return getDecorations(doc)
    },
    apply(tr) {
      return getDecorations(tr.doc)
    },
  },
  props: {
    decorations(state) {
      return this.getState(state)
    },
  },
})
</script>

<style lang="scss">
.comment-highlight {
  text-decoration: underline;
}
.comment-highlight2 {
  background: rgba(120, 0, 100, 0.5);
}
.submission-content {
  counter-reset: paragraph_counter;
  font-size: 16px;
  margin: 0 auto;
  max-width: 700px;
  padding: 10px 60px 60px;
}

.submission-content p {
  position: relative;
}

.submission-content p:before {
  color: #555;
  content: "¶ " counter(paragraph_counter);
  right: 0px;
  counter-increment: paragraph_counter;
  display: block;
  font-family: Helvetica, Arial, san-serif;
  font-size: 1em;
  margin-right: 10px;
  min-width: 50px;
  position: absolute;
  right: 100%;
  text-align: right;
  top: 0;
  white-space: nowrap;
}

mark {
  color: #000;
  background-color: #bbe2e8;
}

.lint-icon {
  display: inline-block;
  right: 2px;
  cursor: pointer;
  float: right;
  border-radius: 100px;
  background: #f22;
  color: white;
  font-family: times, georgia, serif;
  font-size: 15px;
  font-weight: bold;
  width: 1.1em;
  height: 1.1em;
  text-align: center;
  padding-left: 0.5px;
  line-height: 1.1em;
}
</style>
