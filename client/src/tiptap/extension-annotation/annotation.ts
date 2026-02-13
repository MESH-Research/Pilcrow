import { Extension } from "@tiptap/vue-3"
import type { Ref } from "vue"
import { watch } from "vue"
import type { DecorationSet } from "@tiptap/pm/view"
import { AnnotationPlugin, AnnotationPluginKey } from "./plugin"
import type { AnnotationData } from "./plugin"

declare module "@tiptap/core" {
  interface Commands<ReturnType> {
    annotation: {
      addAnnotation: (data: Record<string, unknown>) => ReturnType
      updateAnnotation: (
        id: string,
        data: Record<string, unknown>
      ) => ReturnType
      deleteAnnotation: (id: string) => ReturnType
    }
  }
}

interface AnnotationOptions {
  HTMLAttributes: Record<string, string | undefined>
  onUpdate: (decorations: DecorationSet | null) => void
  annotations?: Ref<AnnotationData[]>
  instance?: string
}

export const Annotation = Extension.create<AnnotationOptions>({
  name: "annotation",

  priority: 1000,

  addOptions() {
    return {
      HTMLAttributes: {
        class: "annotation"
      },
      onUpdate: (decorations) => decorations
    }
  },

  onCreate() {
    const updateAnnotations = (annotations: AnnotationData[]) => {
      const transaction = this.editor.state.tr.setMeta(AnnotationPluginKey, {
        type: "createDecorations",
        annotations
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
              data
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
              data
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
              id
            })
          }

          return true
        }
    }
  },

  addProseMirrorPlugins() {
    return [
      AnnotationPlugin({
        HTMLAttributes: this.options.HTMLAttributes,
        onUpdate: this.options.onUpdate,
        instance: this.options.instance
      })
    ]
  }
})
