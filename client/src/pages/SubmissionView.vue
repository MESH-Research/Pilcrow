<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <q-layout
      data-cy="submission_view_layout"
      view="hHh lpR fFr"
      container
      style="min-height: 650px"
    >
      <q-header reveal elevated class="bg-grey-9 text-white">
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

      <q-page-container>
        <article class="col-sm-9 submission-content">
          <h1>Et Sollicitudin Ac Orci</h1>
          <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
            eiusmod tempor incididunt ut labore et dolore magna aliqua. Commodo
            ullamcorper a lacus vestibulum sed arcu non odio. Nisl nunc mi ipsum
            faucibus. Sit amet justo donec enim diam vulputate ut. Eget lorem
            dolor sed viverra ipsum nunc. Ut tortor pretium viverra suspendisse
            potenti nullam ac tortor vitae.
            <span class="highlight"
              >Et sollicitudin ac orci phasellus egestas tellus rutrum tellus.
              Et egestas quis ipsum suspendisse ultrices gravida. In est ante in
              nibh mauris cursus mattis.</span
            >
            Amet purus gravida quis blandit turpis cursus. Auctor neque vitae
            tempus quam pellentesque nec nam aliquam sem. At consectetur lorem
            donec massa sapien faucibus. Et ultrices neque ornare aenean. Hac
            habitasse platea dictumst vestibulum rhoncus est pellentesque elit.
            Risus quis varius quam quisque id diam vel quam elementum. Blandit
            massa enim nec dui. Dui ut ornare lectus sit amet est placerat in
            egestas. Sit amet commodo nulla facilisi nullam vehicula ipsum.
          </p>
          <p>
            Dui id ornare arcu odio ut sem. Est ullamcorper eget nulla facilisi
            etiam dignissim diam quis. In nibh mauris cursus mattis molestie.
            Arcu dictum varius duis at consectetur lorem donec massa. Ultricies
            tristique nulla aliquet enim tortor.
          </p>
          <p>
            In egestas erat imperdiet sed euismod nisi porta lorem. Ut aliquam
            purus sit amet luctus venenatis. Sagittis eu volutpat odio
            facilisis. Vitae congue eu consequat ac. Cursus sit amet dictum sit
            amet.
          </p>
          <p>
            Nibh tellus molestie nunc non blandit massa enim. Et tortor
            consequat id porta nibh venenatis. Dictum at tempor commodo
            ullamcorper. Placerat orci nulla pellentesque dignissim. Rhoncus
            dolor purus non enim praesent elementum facilisis.
          </p>
          <h2>Venenatis urna</h2>
          <p>
            Justo laoreet sit amet cursus sit. Ultrices neque ornare aenean
            euismod. Eget aliquet nibh praesent tristique magna sit. Aliquam
            nulla facilisi cras fermentum odio eu feugiat. Enim praesent
            elementum facilisis leo vel fringilla est ullamcorper. Arcu ac
            tortor dignissim convallis aenean et tortor at risus. Tincidunt
            augue interdum velit euismod in pellentesque massa placerat. Nisl
            nunc mi ipsum faucibus. Eu feugiat pretium nibh ipsum. Donec et odio
            pellentesque diam volutpat. Nunc sed velit dignissim sodales ut.
            Venenatis urna cursus eget nunc scelerisque viverra mauris. Sem
            viverra aliquet eget sit amet tellus. Magna fringilla urna porttitor
            rhoncus dolor purus non enim. In nisl nisi scelerisque eu ultrices.
            Tempor commodo ullamcorper a lacus vestibulum. Nisl nisi scelerisque
            eu ultrices vitae auctor eu. Urna id volutpat
            <span class="highlight"
              >lacus laoreet non curabitur. Dolor magna eget est lorem ipsum
              dolor. Mauris vitae ultricies leo integer malesuada nunc vel risus
              commodo.</span
            >
          </p>
          <h3>Commodo quis</h3>
          <p>
            Odio euismod lacinia at quis risus sed vulputate odio. Aliquam
            eleifend mi in nulla. Ornare arcu odio ut sem nulla pharetra diam
            sit amet. Nulla pharetra diam sit amet. Faucibus ornare suspendisse
            sed nisi lacus sed. Commodo quis imperdiet massa tincidunt nunc
            pulvinar sapien. Egestas tellus rutrum tellus pellentesque eu
            tincidunt tortor. Pellentesque elit eget gravida cum sociis natoque.
            Ut sem nulla pharetra diam sit amet. Sed lectus vestibulum mattis
            ullamcorper. Sit amet nisl purus in mollis nunc sed id semper. Non
            tellus orci ac auctor. In egestas erat imperdiet sed euismod.
          </p>
          <h3>Facilisi nullam vehicula ipsum a arcu</h3>
          <p>
            Blandit aliquam etiam erat velit scelerisque in dictum. Euismod quis
            viverra nibh cras pulvinar mattis nunc sed blandit. Risus feugiat in
            ante metus dictum at tempor. Facilisi nullam vehicula ipsum a arcu
            cursus vitae congue mauris. Sit amet facilisis magna etiam tempor
          </p>
          <p>
            orci eu lobortis. At quis risus sed vulputate odio ut enim blandit
            volutpat. Tempor id eu nisl nunc mi. Malesuada nunc vel risus
            commodo viverra maecenas accumsan lacus. Porttitor leo a diam
            sollicitudin tempor. Blandit massa enim nec dui nunc mattis enim.
            Elementum nisi quis eleifend quam adipiscing vitae proin sagittis.
            Placerat orci nulla pellentesque dignissim. Condimentum id venenatis
            a condimentum vitae sapien pellentesque.
          </p>
        </article>
        <q-separator class="page-seperator" />
        <section class="comments">
          <div class="comments-wrapper">
            <h3 class="text-h1">Overall Comments</h3>
            <q-card class="q-mb-md">
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
            </q-card>

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
import { GET_SUBMISSION } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
// import { ref } from "vue"
// const right_drawer_ref = ""
// function right_drawer_ref(el) {
//   if (!el) {
//     return
//   }
//   console.log(el)
//   el.setAttribute("role", "region")
// }
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const submission = useResult(useQuery(GET_SUBMISSION, { id: props.id }).result)
// const rightDrawerOpen = ref(false)
// function toggleRightDrawer() {
//   rightDrawerOpen.value = !rightDrawerOpen.value
// }
// const drawerWidth = ref(520)
// let originalWidth
// let originalLeft
// function handlePan({ ...newInfo }) {
//   if (newInfo.isFirst) {
//     originalWidth = drawerWidth.value
//     originalLeft = newInfo.position.left
//   } else {
//     const newDelta = newInfo.position.left - originalLeft
//     const newWidth = Math.max(200, Math.min(800, originalWidth - newDelta))
//     drawerWidth.value = newWidth
//   }
// }
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
