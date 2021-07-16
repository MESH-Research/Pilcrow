<template>
  <q-page padding>
    <h2>{{ submission.title }}</h2>
    <p> {{ id }} </p>
    <div id="q-app">
      <div class="q-pa-md">
        <div class="q-gutter-md row">
          <q-select
            v-model="model"
            filled
            use-input
            hide-selected
            fill-input
            input-debounce="0"
            hint="Minimum 2 characters to trigger filtering"
            style="width: 250px; padding-bottom: 32px"
            :options="options"
            @filter="filterFn"
            @input-value="setModel"
          >
            <template #no-option>
              <q-item>
                <q-item-section class="text-grey">
                  No results
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { GET_SUBMISSION } from "src/graphql/queries";

const stringOptions = [
  'Google', 'Facebook', 'Twitter', 'Apple', 'Oracle'
]
export default {
  components: {
  },
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      submission: {
        title: null,
        publication: null,
        user: null,
      },
      model: null,
      options: stringOptions,
    }
  },
  methods: {
    filterFn (val, update, abort) {
      if (val.length < 2) {
        abort()
        return
      }
      update(() => {
        const needle = val.toLowerCase()
        this.options = stringOptions.filter(v => v.toLowerCase().indexOf(needle) > -1)
      })
    },
    setModel (val) {
      this.model = val
    }
  },
  apollo: {
    submission: {
      query: GET_SUBMISSION,
      variables () {
        return {
         id: this.id,
        }
      }
    }
  },
}
</script>
