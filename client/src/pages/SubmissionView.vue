<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <q-layout
      data-cy="submission_view_layout"
      view="hHh lpR fFr"
      container
      style="min-height: calc(100vh - 70px)"
    >
      <submission-toolbar
        :id="id"
        v-model="commentDrawerOpen"
        :submission="submission"
      />
      <submission-comment-drawer
        :comment-drawer-open="commentDrawerOpen"
        :comments="comments"
      />
      <q-page-container>
        <submission-content />
        <q-separator class="page-seperator" />
        <submission-comment-section />
      </q-page-container>
    </q-layout>

    <div class="row q-col-gutter-lg q-pa-lg"></div>
  </article>
</template>

<script setup>
import SubmissionCommentDrawer from "src/components/atoms/SubmissionCommentDrawer.vue"
import SubmissionCommentSection from "src/components/atoms/SubmissionCommentSection.vue"
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import SubmissionToolbar from "src/components/atoms/SubmissionToolbar.vue"
import { ref } from "vue"
import { GET_SUBMISSION } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const submission = useResult(useQuery(GET_SUBMISSION, { id: props.id }).result)
const commentDrawerOpen = ref(true)
const comments = [
  {
    commentThread: 1,
    commentNumber: 1,
    replyTo: null,
    annotationNumber: null,
    hasAnnotation: false,
    content: `<p>
            Vitae semper quis lectus nulla at volutpat. Eleifend quam adipiscing
            vitae proin sagittis. Tellus molestie nunc non blandit massa. Odio
            tempor orci dapibus ultrices in. Condimentum id venenatis a
            condimentum vitae sapien pellentesque habitant. Auctor augue mauris
            augue neque gravida in. Etiam sit amet nisl purus in. Fringilla ut
            morbi tincidunt augue. Morbi tincidunt ornare massa eget egestas
            purus viverra accumsan. Sed odio morbi quis commodo. Velit euismod
            in pellentesque massa. Massa massa ultricies mi quis hendrerit dolor
            magna eget.
          </p>
          <p>
            Sed sed risus pretium quam vulputate. Amet mauris commodo quis
            imperdiet massa tincidunt nunc. Consequat mauris nunc congue nisi
            vitae suscipit tellus mauris a. Non nisi est sit amet facilisis
            magna. Turpis massa tincidunt dui ut ornare lectus. Vel risus
            commodo viverra maecenas accumsan lacus vel facilisis. Mauris cursus
            mattis molestie a iaculis at. Aenean sed adipiscing diam donec. Nisl
            nunc mi ipsum faucibus. Sed elementum tempus egestas sed sed risus.
            Risus pretium quam vulputate dignissim suspendisse in. Ut faucibus
            pulvinar elementum integer. Volutpat ac tincidunt vitae semper quis.
            Cras semper auctor neque vitae tempus. Malesuada bibendum arcu vitae
            elementum curabitur vitae nunc sed. Tortor vitae purus faucibus
            ornare suspendisse sed. Turpis tincidunt id aliquet risus feugiat.
            Mauris augue neque gravida in fermentum et sollicitudin ac orci. In
            pellentesque massa placerat duis ultricies lacus sed.
          </p>
    `,
    timestamp: "February 17th, 2021 at 6:35pm",
    user: {
      name: "Vitae Congue",
      email: "vitae@ccr.lndo.site",
    },
    styleCriteria: [],
  },
  {
    commentThread: 1,
    commentNumber: 2,
    replyTo: 1,
    hasAnnotation: false,
    content: `<p>
              Sagittis eu volutpat odio facilisis. Vitae congue eu consequat ac.
              Cursus sit amet dictum sit amet. Nibh tellus molestie nunc non
              blandit massa enim. Et tortor consequat id porta nibh venenatis.
              Dictum at tempor commodo ullamcorper. Placerat orci nulla
              pellentesque dignissim. Rhoncus dolor purus non enim praesent
              elementum facilisis.
            </p>
    `,
    timestamp: "February 17th, 2021 at 6:35pm",
    user: {
      name: "Vitae Congue",
      email: "vitae@ccr.lndo.site",
    },
    styleCriteria: [],
  },
]
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>
