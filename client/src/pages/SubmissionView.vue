<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <q-layout view="hHh lpR fFr" container style="min-height: 650px">
      <q-header reveal elevated class="bg-grey-9 text-white">
        <q-toolbar>
          <q-btn
            dense
            flat
            round
            icon="arrow_back_ios_new"
            class="q-mr-sm"
            :to="{
              name: 'submission_details',
              params: { id: props.id },
            }"
          />
          <q-toolbar-title>
            <h2 class="text-h4 q-ma-sm">
              {{ submission.title }}
            </h2>
          </q-toolbar-title>

          <q-btn
            dense
            flat
            round
            icon="question_answer"
            @click="toggleRightDrawer"
          />
        </q-toolbar>
      </q-header>

      <q-drawer v-model="rightDrawerOpen" show-if-above side="right" bordered>
        <q-card flat class="bg-grey-1">
          <q-card-section>
            <div class="row items-center no-wrap">
              <div class="col">
                <div class="text-subtitle1">John Doe</div>
                <small>February 17th, 2021 at 6:35pm</small>
              </div>

              <div class="col-auto">
                <q-btn color="grey-7" round flat icon="more_vert">
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

          <q-card-section>
            {{ lorem }}
          </q-card-section>

          <div class="q-pb-sm">
            <q-chip size="16px" icon="bookmark"> Relevance </q-chip>
            <q-chip size="16px" icon="bookmark"> Accessibility </q-chip>
            <q-chip size="16px" icon="bookmark"> Coherence </q-chip>
          </div>

          <q-card-actions>
            <q-btn bordered flat>Reply</q-btn>
          </q-card-actions>

          <q-separator />
        </q-card>
        <q-card flat class="bg-grey-1">
          <q-card-section>
            <div class="row items-center no-wrap">
              <div class="col">
                <div class="text-subtitle1">John Doe</div>
                <small>February 17th, 2021 at 6:35pm</small>
              </div>

              <div class="col-auto">
                <q-btn color="grey-7" round flat icon="more_vert">
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

          <q-card-section>
            {{ lorem2 }}
          </q-card-section>

          <div class="q-pb-sm">
            <q-chip size="16px" icon="bookmark"> Relevance </q-chip>
          </div>

          <q-card-actions>
            <q-btn bordered flat>Reply</q-btn>
          </q-card-actions>

          <q-separator />
        </q-card>
      </q-drawer>

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
            Blandit aliquam etiam erat velit scelerisque in dictum. Euismod quis
            viverra nibh cras pulvinar mattis nunc sed blandit. Risus feugiat in
            ante metus dictum at tempor. Facilisi nullam vehicula ipsum a arcu
            cursus vitae congue mauris. Sit amet facilisis magna etiam tempor
            orci eu lobortis. At quis risus sed vulputate odio ut enim blandit
            volutpat. Tempor id eu nisl nunc mi. Malesuada nunc vel risus
            commodo viverra maecenas accumsan lacus. Porttitor leo a diam
            sollicitudin tempor. Blandit massa enim nec dui nunc mattis enim.
            Elementum nisi quis eleifend quam adipiscing vitae proin sagittis.
            Placerat orci nulla pellentesque dignissim. Condimentum id venenatis
            a condimentum vitae sapien pellentesque.
          </p>
        </article>
      </q-page-container>
    </q-layout>

    <div class="row q-col-gutter-lg q-pa-lg"></div>
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
import { ref } from "vue"
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
const lorem = `At quis risus sed vulputate odio. Aliquam
            eleifend mi in nulla. Ornare arcu odio ut sem nulla pharetra diam
            sit amet. Nulla pharetra diam sit amet. Faucibus ornare suspendisse
            sed nisi lacus sed. Commodo quis imperdiet massa tincidunt nunc
            pulvinar sapien. Egestas tellus rutrum tellus pellentesque eu
            tincidunt tortor.`
const lorem2 = `Ut sem nulla pharetra diam sit amet. Sed lectus vestibulum mattis
            ullamcorper.`
</script>

<style scoped>
.submission-content {
  counter-reset: paragraph_counter;
  max-width: 700px;
  padding: 0 60px;
  font-size: 16px;
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
  position: absolute;
  right: 100%;
  text-align: right;
  top: 0;
  width: 50px;
}
.highlight {
  color: #000;
  background-color: #bbe2e8;
}
.tag__value {
  background: #orange;
}
</style>
