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
      <q-header reveal class="bg-grey-9 text-white">
        <q-toolbar>
          <q-btn
            dense
            aria-label="Back to Submission Details"
            flat
            round
            icon="arrow_back_ios_new"
            :to="{
              name: 'submission_details',
              params: { id: props.id },
            }"
          />
          <q-toolbar-title>
            {{ submission.title }}
          </q-toolbar-title>

          <q-btn
            aria-label="Toggle Inline Comments"
            dense
            flat
            round
            icon="question_answer"
            @click="toggleRightDrawer"
          />
        </q-toolbar>
      </q-header>

      <q-drawer
        v-model="rightDrawerOpen"
        show-if-above
        side="right"
        bordered
        :width="drawerWidth"
      >
        <div class="row fit">
          <div
            v-touch-pan.horizontal.prevent.mouse.preserveCursor="handlePan"
            style="width: 6px; cursor: col-resize"
            class="bg-primary"
          ></div>
          <q-scroll-area class="fit col bg-grey-4">
            <inline-comment :user="currentUser" />
            <div class="q-ml-md q-mb-md">
              <inline-comment-reply :user="currentUser" />
              <inline-comment-reply :user="currentUser" />
            </div>
            <div class="row justify-center q-pa-md">
              <q-btn color="dark" icon="arrow_upward">Scroll to Top</q-btn>
            </div>
          </q-scroll-area>
        </div>
      </q-drawer>

      <q-page-container>
        <submission-content />
        <q-separator class="page-seperator" />
        <section class="comments">
          <div class="comments-wrapper">
            <h3 class="text-h1">Overall Comments</h3>
            <q-card>
              <q-card-section>
                <div
                  class="row items-start justify-between content-stretch no-wrap"
                >
                  <div class="col-grow content-between">
                    <div class="text-h4">Vitae Congue</div>
                    <div>
                      <small>February 17th, 2021 at 6:35pm</small>
                    </div>
                  </div>

                  <div class="col-auto">
                    <q-btn
                      color="grey-7"
                      round
                      flat
                      icon="more_vert"
                      aria-label="Comment Menu"
                    >
                      <q-menu cover auto-close>
                        <q-list>
                          <q-item clickable>
                            <q-item-section>Remove Card</q-item-section>
                          </q-item>
                          <q-item clickable>
                            <q-item-section>Send Feedback</q-item-section>
                          </q-item>
                          <q-item clickable>
                            <q-item-section>Share</q-item-section>
                          </q-item>
                        </q-list>
                      </q-menu>
                    </q-btn>
                  </div>
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section class="q-pb-none">
                <p>
                  Vitae semper quis lectus nulla at volutpat. Eleifend quam
                  adipiscing vitae proin sagittis. Tellus molestie nunc non
                  blandit massa. Odio tempor orci dapibus ultrices in.
                  Condimentum id venenatis a condimentum vitae sapien
                  pellentesque habitant. Auctor augue mauris augue neque gravida
                  in. Etiam sit amet nisl purus in. Fringilla ut morbi tincidunt
                  augue. Morbi tincidunt ornare massa eget egestas purus viverra
                  accumsan. Sed odio morbi quis commodo. Velit euismod in
                  pellentesque massa. Massa massa ultricies mi quis hendrerit
                  dolor magna eget.
                </p>
                <p>
                  Sed sed risus pretium quam vulputate. Amet mauris commodo quis
                  imperdiet massa tincidunt nunc. Consequat mauris nunc congue
                  nisi vitae suscipit tellus mauris a. Non nisi est sit amet
                  facilisis magna. Turpis massa tincidunt dui ut ornare lectus.
                  Vel risus commodo viverra maecenas accumsan lacus vel
                  facilisis. Mauris cursus mattis molestie a iaculis at. Aenean
                  sed adipiscing diam donec. Nisl nunc mi ipsum faucibus. Sed
                  elementum tempus egestas sed sed risus. Risus pretium quam
                  vulputate dignissim suspendisse in. Ut faucibus pulvinar
                  elementum integer. Volutpat ac tincidunt vitae semper quis.
                  Cras semper auctor neque vitae tempus. Malesuada bibendum arcu
                  vitae elementum curabitur vitae nunc sed. Tortor vitae purus
                  faucibus ornare suspendisse sed. Turpis tincidunt id aliquet
                  risus feugiat. Mauris augue neque gravida in fermentum et
                  sollicitudin ac orci. In pellentesque massa placerat duis
                  ultricies lacus sed.
                </p>
              </q-card-section>
              <q-card-actions class="q-pa-md">
                <q-btn bordered color="primary">Reply</q-btn>
              </q-card-actions>
            </q-card>
            <div class="q-mx-md q-mb-md">
              <q-separator />
              <q-card flat square class="bg-grey-1">
                <q-card-section>
                  <div class="row items-start no-wrap">
                    <div class="col">
                      <div class="text-subtitle1">Egestas</div>
                      <div>
                        <small>
                          <q-icon size="sm" name="subdirectory_arrow_right" />
                          <span>Reply to Magna Fringilla</span>
                        </small>
                      </div>
                      <small> February 18th, 2021 at 6:35pm</small>
                    </div>

                    <div class="col-auto">
                      <q-btn
                        color="grey-7"
                        round
                        flat
                        icon="more_vert"
                        aria-label="Reply Comment Menu"
                      >
                        <q-menu cover auto-close>
                          <q-list>
                            <q-item clickable>
                              <q-item-section>Remove Card</q-item-section>
                            </q-item>
                            <q-item clickable>
                              <q-item-section>Send Feedback</q-item-section>
                            </q-item>
                            <q-item clickable>
                              <q-item-section>Share</q-item-section>
                            </q-item>
                          </q-list>
                        </q-menu>
                      </q-btn>
                    </div>
                  </div>
                </q-card-section>

                <q-card-section class="q-py-none">
                  <p>
                    Sagittis eu volutpat odio facilisis. Vitae congue eu
                    consequat ac. Cursus sit amet dictum sit amet. Nibh tellus
                    molestie nunc non blandit massa enim. Et tortor consequat id
                    porta nibh venenatis. Dictum at tempor commodo ullamcorper.
                    Placerat orci nulla pellentesque dignissim. Rhoncus dolor
                    purus non enim praesent elementum facilisis.
                  </p>
                </q-card-section>

                <q-card-actions class="q-pa-md">
                  <q-btn bordered color="primary">Reply</q-btn>
                </q-card-actions>

                <q-separator />
              </q-card>
              <q-card flat square class="bg-grey-1">
                <q-card-section>
                  <div class="row items-start no-wrap">
                    <div class="col">
                      <div class="text-subtitle1">Nibh Mauris</div>
                      <div>
                        <small>
                          <q-icon size="sm" name="subdirectory_arrow_right" />
                          <span>Reply to Egestas</span>
                        </small>
                      </div>
                      <small> February 18th, 2021 at 6:35pm</small>
                    </div>

                    <div class="col-auto">
                      <q-btn
                        color="grey-7"
                        round
                        flat
                        icon="more_vert"
                        aria-label="Reply Comment Menu"
                      >
                        <q-menu cover auto-close>
                          <q-list>
                            <q-item clickable>
                              <q-item-section>Remove Card</q-item-section>
                            </q-item>
                            <q-item clickable>
                              <q-item-section>Send Feedback</q-item-section>
                            </q-item>
                            <q-item clickable>
                              <q-item-section>Share</q-item-section>
                            </q-item>
                          </q-list>
                        </q-menu>
                      </q-btn>
                    </div>
                  </div>
                </q-card-section>

                <q-card-section class="q-py-none">
                  <p>Dictum at tempor commodo.</p>
                </q-card-section>

                <q-card-actions class="q-pa-md">
                  <q-btn bordered color="primary">Reply</q-btn>
                </q-card-actions>

                <q-separator />
              </q-card>
            </div>

            <q-card class="q-mb-md">
              <q-card-section>
                <div
                  class="row items-start justify-between content-stretch no-wrap"
                >
                  <div class="col-grow content-between">
                    <div class="text-h4">Amet Nisl Purus</div>
                    <div>
                      <small>February 17th, 2021 at 6:35pm</small>
                    </div>
                  </div>

                  <div class="col-auto">
                    <q-btn
                      color="grey-7"
                      round
                      flat
                      icon="more_vert"
                      aria-label="Comment Menu"
                    >
                      <q-menu cover auto-close>
                        <q-list>
                          <q-item clickable>
                            <q-item-section>Remove Card</q-item-section>
                          </q-item>
                          <q-item clickable>
                            <q-item-section>Send Feedback</q-item-section>
                          </q-item>
                          <q-item clickable>
                            <q-item-section>Share</q-item-section>
                          </q-item>
                        </q-list>
                      </q-menu>
                    </q-btn>
                  </div>
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section class="q-pb-none">
                <p>
                  Vitae semper quis lectus nulla at volutpat. Eleifend quam
                  adipiscing vitae proin sagittis. Tellus molestie nunc non
                  blandit massa. Odio tempor orci dapibus ultrices in.
                  Condimentum id venenatis a condimentum vitae sapien
                  pellentesque habitant. Auctor augue mauris augue neque gravida
                  in. Etiam sit amet nisl purus in. Fringilla ut morbi tincidunt
                  augue. Morbi tincidunt ornare massa eget egestas purus viverra
                  accumsan. Sed odio morbi quis commodo. Velit euismod in
                  pellentesque massa. Massa massa ultricies mi quis hendrerit
                  dolor magna eget.
                </p>
                <p>
                  Sed sed risus pretium quam vulputate. Amet mauris commodo quis
                  imperdiet massa tincidunt nunc. Consequat mauris nunc congue
                  nisi vitae suscipit tellus mauris a. Non nisi est sit amet
                  facilisis magna. Turpis massa tincidunt dui ut ornare lectus.
                  Vel risus commodo viverra maecenas accumsan lacus vel
                  facilisis. Mauris cursus mattis molestie a iaculis at. Aenean
                  sed adipiscing diam donec. Nisl nunc mi ipsum faucibus. Sed
                  elementum tempus egestas sed sed risus. Risus pretium quam
                  vulputate dignissim suspendisse in. Ut faucibus pulvinar
                  elementum integer. Volutpat ac tincidunt vitae semper quis.
                  Cras semper auctor neque vitae tempus. Malesuada bibendum arcu
                  vitae elementum curabitur vitae nunc sed. Tortor vitae purus
                  faucibus ornare suspendisse sed. Turpis tincidunt id aliquet
                  risus feugiat. Mauris augue neque gravida in fermentum et
                  sollicitudin ac orci. In pellentesque massa placerat duis
                  ultricies lacus sed.
                </p>
              </q-card-section>
              <q-card-actions class="q-pa-md">
                <q-btn bordered color="primary">Reply</q-btn>
              </q-card-actions>
            </q-card>
          </div>
        </section>
      </q-page-container>
    </q-layout>

    <div class="row q-col-gutter-lg q-pa-lg"></div>
  </article>
</template>

<script setup>
import InlineComment from "src/components/atoms/InlineComment.vue"
import InlineCommentReply from "src/components/atoms/InlineCommentReply.vue"
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import { ref } from "vue"
import { GET_SUBMISSION } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
import { useCurrentUser } from "src/use/user"
const { currentUser } = useCurrentUser()
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const submission = useResult(useQuery(GET_SUBMISSION, { id: props.id }).result)
const rightDrawerOpen = ref(false)
function toggleRightDrawer() {
  rightDrawerOpen.value = !rightDrawerOpen.value
}
const drawerWidth = ref(400)
let originalWidth
let originalLeft
function handlePan({ ...newInfo }) {
  if (newInfo.isFirst) {
    originalWidth = drawerWidth.value
    originalLeft = newInfo.position.left
  } else {
    const newDelta = newInfo.position.left - originalLeft
    const newWidth = Math.max(200, Math.min(800, originalWidth - newDelta))
    drawerWidth.value = newWidth
  }
}
</script>

<style lang="sass" scoped>
.inline-comments
  background-color: #000
.comments
  background-color: #efefef

.comments-wrapper,
.submission-content
  max-width: 700px
  margin: 0 auto
  padding: 10px 60px 60px

.submission-content
  counter-reset: paragraph_counter
  font-size: 16px

.submission-content p
  position: relative

.submission-content p:before
  color: #555
  content: "¶ " counter(paragraph_counter)
  counter-increment: paragraph_counter
  display: block
  font-family: Helvetica, Arial, san-serif
  font-size: 1em
  margin-right: 10px
  min-width: 50px
  position: absolute
  right: 100%
  text-align: right
  top: 0
  white-space: nowrap

.highlight
  color: #000
  background-color: #bbe2e8

.tag__value
  background: #orange

.page-seperator
  height: 3px
  background-color: #888
</style>
