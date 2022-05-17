<template>
  <h2>
    Material Design <code>{{ iconName }}</code> Icons
  </h2>
  <p>
    <span
      :class="{
        highlight: comments[5].isActive,
      }"
    >
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
        Et egestas quis ipsum suspendisse ultrices gravida. </span
      ><span
        :class="{ highlight: comments[1].isActive || comments[2].isActive }"
      >
        In est ante in nibh mauris cursus mattis.</span
      >
      <span :class="{ highlight: comments[3].isActive }">Amet purus</span>
      gravida quis blandit turpis cursus. Auctor neque vitae tempus quam
      pellentesque nec nam aliquam sem.

      <span :class="{ highlight: comments[4].isActive }"
        >At consectetur lorem donec massa</span
      >
      sapien faucibus. Et ultrices neque ornare aenean. Hac habitasse
      platea.</span
    >
  </p>
  <section class="q-my-md q-gutter-sm">
    <inline-comment-indicator1
      :icon-name="iconName"
      :is-active="isActive(0)"
      @click="toggleHighlightVisibility(0)"
    />
    <inline-comment-indicator1
      :icon-name="iconName"
      :is-active="isActive(1)"
      @click="toggleHighlightVisibility(1)"
    />
    <inline-comment-indicator1
      :icon-name="iconName"
      :is-active="isActive(2)"
      @click="toggleHighlightVisibility(2)"
    />
    <inline-comment-indicator1
      :icon-name="iconName"
      :is-active="isActive(3)"
      @click="toggleHighlightVisibility(3)"
    />
    <inline-comment-indicator1
      :icon-name="iconName"
      :is-active="isActive(4)"
      @click="toggleHighlightVisibility(4)"
    />
    <inline-comment-indicator1
      :icon-name="iconName"
      :is-active="isActive(5)"
      @click="toggleHighlightVisibility(5)"
    />
  </section>

  <p>
    Dui id ornare arcu odio ut sem. Est ullamcorper eget nulla facilisi etiam
    dignissim diam quis.
    <span :class="{ highlight: comments[6].isActive }"
      >In nibh mauris cursus mattis molestie.</span
    ><span :class="{ highlight: comments[6].isActive || comments[7].isActive }">
      Arcu dictum varius duis at consectetur lorem donec massa.</span
    ><span :class="{ highlight: comments[7].isActive }">
      Ultricies tristique nulla aliquet enim tortor.</span
    >
  </p>
  <section class="q-my-md q-gutter-sm">
    <inline-comment-indicator1
      :icon-name="iconName"
      :is-active="isActive(6)"
      @click="toggleHighlightVisibility(6)"
    />
    <inline-comment-indicator1
      :icon-name="iconName"
      :is-active="isActive(7)"
      @click="toggleHighlightVisibility(7)"
    />
  </section>
  <p>
    In egestas erat imperdiet sed euismod nisi porta lorem. Ut aliquam purus sit
    amet luctus venenatis. Sagittis eu volutpat odio facilisis. Vitae congue eu
    consequat ac. Cursus sit amet dictum sit amet.
  </p>

  <q-separator />
</template>
<script setup>
import { ref } from "vue"
import InlineCommentIndicator1 from "./InlineCommentIndicator1.vue"

let comments = ref([
  {
    id: "ic0",
    isActive: false,
  },
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
  {
    id: "ic6",
    isActive: false,
  },
  {
    id: "ic7",
    isActive: false,
  },
])

function isActive(commentIndex) {
  return comments.value[commentIndex].isActive
}

function toggleHighlightVisibility(commentIndex) {
  comments.value.forEach((comment, index) => {
    if (index != commentIndex) {
      comment.isActive = false
    }
  })
  comments.value[commentIndex].isActive = !isActive(commentIndex)
}
defineProps({
  iconName: {
    type: String,
    default: "chat",
  },
})
</script>
