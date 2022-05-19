import { watch } from "vue"
import { AnnotationPluginKey, AnnotationPlugin } from "./AnnotationPlugin"
import { Extension } from "@tiptap/vue-3"
export default Extension.create({
  name: "annotation",

  priority: 1000,

  defaultOptions: {
    HTMLAttributes: {
      class: "annotation",
    },
    onUpdate: (decorations) => decorations,
  },

  onCreate() {
    const updateAnnotations = (annotations) => {
      console.log(`annotations updated`)

      const transaction = this.editor.state.tr.setMeta(AnnotationPluginKey, {
        type: "createDecorations",
        annotations,
      })

      this.editor.view.dispatch(transaction)
    }
    watch(this.options.annotations, updateAnnotations)
    updateAnnotations(this.options.annotations.value)
  },

  addCommands() {
    return {
      addAnnotation:
        (data) =>
        ({ dispatch, state }) => {
          const { selection } = state

          if (selection.empty) {
            return false
          }

          if (dispatch && data) {
            state.tr.setMeta(AnnotationPluginKey, {
              type: "addAnnotation",
              from: selection.from,
              to: selection.to,
              data,
            })
          }

          return true
        },
      updateAnnotation:
        (id, data) =>
        ({ dispatch, state }) => {
          if (dispatch) {
            state.tr.setMeta(AnnotationPluginKey, {
              type: "updateAnnotation",
              id,
              data,
            })
          }

          return true
        },
      deleteAnnotation:
        (id) =>
        ({ dispatch, state }) => {
          if (dispatch) {
            state.tr.setMeta(AnnotationPluginKey, {
              type: "deleteAnnotation",
              id,
            })
          }

          return true
        },
    }
  },

  addProseMirrorPlugins() {
    return [
      AnnotationPlugin({
        HTMLAttributes: this.options.HTMLAttributes,
        onUpdate: this.options.onUpdate,
        instance: this.options.instance,
      }),
    ]
  },
})
