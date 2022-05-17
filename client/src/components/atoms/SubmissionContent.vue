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
    <h1>Small Buttons</h1>
    <p>
      Justo donec enim diam vulputate ut. Eget lorem dolor sed viverra ipsum
      nunc. Ut tortor pretium viverra suspendisse potenti nullam ac tortor
      vitae.
      <span
        :class="{
          highlight: comments[0].isActive || comments[1].isActive,
        }"
      >
        Et sollicitudin ac orci phasellus egestas tellus rutrum tellus.</span
      >
      <span :class="{ highlight: comments[1].isActive }">
        <q-btn
          size="md"
          flat
          dense
          icon="rate_review"
          style="margin-top: -3px"
          @click="toggleHighlightVisibility(0)"
        ></q-btn>
        Et egestas quis ipsum suspendisse ultrices gravida. </span
      ><span
        :class="{ highlight: comments[1].isActive || comments[2].isActive }"
      >
        In est ante in nibh mauris cursus mattis.</span
      ><q-btn
        size="md"
        flat
        dense
        icon="try"
        style="margin-top: -3px"
        @click="toggleHighlightVisibility(1)"
      ></q-btn>
      <q-btn
        size="md"
        flat
        dense
        icon="chat"
        style="margin-top: -3px"
        @click="toggleHighlightVisibility(2)"
      ></q-btn>

      <span :class="{ highlight: comments[3].isActive }">Amet purus</span>
      <q-btn
        size="md"
        flat
        dense
        icon="question_answer"
        style="margin-top: -3px"
        @click="toggleHighlightVisibility(3)"
      ></q-btn>
      gravida quis blandit turpis cursus. Auctor neque vitae tempus quam
      pellentesque nec nam aliquam sem.

      <span :class="{ highlight: comments[4].isActive }"
        >At consectetur lorem donec massa</span
      >
      <q-btn
        size="md"
        flat
        dense
        icon="history_edu"
        style="margin-top: -3px"
        @click="toggleHighlightVisibility(4)"
      ></q-btn>
      sapien faucibus. Et ultrices neque ornare aenean. Hac habitasse platea
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
      <mark
        >lacus laoreet non curabitur. Dolor magna eget est lorem ipsum dolor.
        Mauris vitae ultricies leo integer malesuada nunc vel risus
        commodo.</mark
      >
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
  </article>
</template>
<script setup>
import { ref } from "vue"
import { Editor } from "@tiptap/vue-3"
import Highlight from "@tiptap/extension-highlight"
import StarterKit from "@tiptap/starter-kit"

let darkMode = ref(true)
function toggleDarkMode() {
  darkMode.value = !darkMode.value
}
const fonts = ["Sans-serif", "Serif"]
let selectedFont = ref("San-serif")

let comments = ref([
  {
    id: "ic1",
    isActive: false,
  },
  {
    id: "ic2",
    isActive: true,
  },
  {
    id: "ic3",
    isActive: false,
  },
  {
    id: "ic4",
    isActive: false,
  },
  {
    id: "ic5",
    isActive: false,
  },
])

function toggleHighlightVisibility(commentIndex) {
  comments.value.forEach((comment, index) => {
    if (index != commentIndex) {
      comment.isActive = false
    }
  })
  comments.value[commentIndex].isActive = !comments.value[commentIndex].isActive
}

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
        <button>Hello</button>
      <q-btn></q-btn>

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
console.log(typeof editor)
</script>

<style lang="scss">
.submission-content {
  counter-reset: paragraph_counter;
  font-size: 16px;
  line-height: 1.8;
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

.highlight {
  color: #000;
  background-color: #bbe2e8;
}
</style>
