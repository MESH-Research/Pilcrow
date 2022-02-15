<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.submissions', 2)"
          to="/submissions"
        />
        <q-breadcrumbs-el
          :label="$t('submissions.details_heading')"
          :to="{
            name: 'submission_details',
            params: { id: props.id },
          }"
        />
        <q-breadcrumbs-el :label="$t('submissions.view_heading')" />
      </q-breadcrumbs>
    </nav>
    <h2 class="q-pl-lg">{{ submission.title }}</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <article class="col-sm-9">
        <h1>Submission Title</h1>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
          eiusmod tempor incididunt ut labore et dolore magna aliqua. Commodo
          ullamcorper a lacus vestibulum sed arcu non odio. Nisl nunc mi ipsum
          faucibus. Sit amet justo donec enim diam vulputate ut. Eget lorem
          dolor sed viverra ipsum nunc. Ut tortor pretium viverra suspendisse
          potenti nullam ac tortor vitae. Et sollicitudin ac orci phasellus
          egestas tellus rutrum tellus. Et egestas quis ipsum suspendisse
          ultrices gravida. In est ante in nibh mauris cursus mattis. Amet purus
          gravida quis blandit turpis cursus. Auctor neque vitae tempus quam
          pellentesque nec nam aliquam sem. At consectetur lorem donec massa
          sapien faucibus. Et ultrices neque ornare aenean. Hac habitasse platea
          dictumst vestibulum rhoncus est pellentesque elit. Risus quis varius
          quam quisque id diam vel quam elementum. Blandit massa enim nec dui.
          Dui ut ornare lectus sit amet est placerat in egestas. Sit amet
          commodo nulla facilisi nullam vehicula ipsum.
        </p>
        <p>
          Dui id ornare arcu odio ut sem. Est ullamcorper eget nulla facilisi
          etiam dignissim diam quis. In nibh mauris cursus mattis molestie. Arcu
          dictum varius duis at consectetur lorem donec massa. Ultricies
          tristique nulla aliquet enim tortor. In egestas erat imperdiet sed
          euismod nisi porta lorem. Ut aliquam purus sit amet luctus venenatis.
          Sagittis eu volutpat odio facilisis. Vitae congue eu consequat ac.
          Cursus sit amet dictum sit amet. Nibh tellus molestie nunc non blandit
          massa enim. Et tortor consequat id porta nibh venenatis. Dictum at
          tempor commodo ullamcorper. Placerat orci nulla pellentesque
          dignissim. Rhoncus dolor purus non enim praesent elementum facilisis.
        </p>
        <h2>Subheading</h2>
        <p>
          Justo laoreet sit amet cursus sit. Ultrices neque ornare aenean
          euismod. Eget aliquet nibh praesent tristique magna sit. Aliquam nulla
          facilisi cras fermentum odio eu feugiat. Enim praesent elementum
          facilisis leo vel fringilla est ullamcorper. Arcu ac tortor dignissim
          convallis aenean et tortor at risus. Tincidunt augue interdum velit
          euismod in pellentesque massa placerat. Nisl nunc mi ipsum faucibus.
          Eu feugiat pretium nibh ipsum. Donec et odio pellentesque diam
          volutpat. Nunc sed velit dignissim sodales ut. Venenatis urna cursus
          eget nunc scelerisque viverra mauris. Sem viverra aliquet eget sit
          amet tellus. Magna fringilla urna porttitor rhoncus dolor purus non
          enim. In nisl nisi scelerisque eu ultrices. Tempor commodo ullamcorper
          a lacus vestibulum. Nisl nisi scelerisque eu ultrices vitae auctor eu.
          Urna id volutpat lacus laoreet non curabitur. Dolor magna eget est
          lorem ipsum dolor. Mauris vitae ultricies leo integer malesuada nunc
          vel risus commodo.
        </p>
        <p>
          Odio euismod lacinia at quis risus sed vulputate odio. Aliquam
          eleifend mi in nulla. Ornare arcu odio ut sem nulla pharetra diam sit
          amet. Nulla pharetra diam sit amet. Faucibus ornare suspendisse sed
          nisi lacus sed. Commodo quis imperdiet massa tincidunt nunc pulvinar
          sapien. Egestas tellus rutrum tellus pellentesque eu tincidunt tortor.
          Pellentesque elit eget gravida cum sociis natoque. Ut sem nulla
          pharetra diam sit amet. Sed lectus vestibulum mattis ullamcorper. Sit
          amet nisl purus in mollis nunc sed id semper. Non tellus orci ac
          auctor. In egestas erat imperdiet sed euismod. Blandit aliquam etiam
          erat velit scelerisque in dictum. Euismod quis viverra nibh cras
          pulvinar mattis nunc sed blandit. Risus feugiat in ante metus dictum
          at tempor. Facilisi nullam vehicula ipsum a arcu cursus vitae congue
          mauris. Sit amet facilisis magna etiam tempor orci eu lobortis. At
          quis risus sed vulputate odio ut enim blandit volutpat. Tempor id eu
          nisl nunc mi. Malesuada nunc vel risus commodo viverra maecenas
          accumsan lacus. Porttitor leo a diam sollicitudin tempor. Blandit
          massa enim nec dui nunc mattis enim. Elementum nisi quis eleifend quam
          adipiscing vitae proin sagittis. Placerat orci nulla pellentesque
          dignissim. Condimentum id venenatis a condimentum vitae sapien
          pellentesque.
        </p>
      </article>
    </div>
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const submission = useResult(useQuery(GET_SUBMISSION, { id: props.id }).result)
</script>
