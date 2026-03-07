import { Plugin, PluginKey } from "@tiptap/pm/state"
//import { AnnotationState } from './AnnotationState'
import { Decoration, DecorationSet } from "@tiptap/pm/view"
import { defineEmits } from "vue"

export const AnnotationPluginKey = new PluginKey("annotation")

function getCommaSepList(a) {
  let list = a.context_ids[a.context.id] ?? []
  let ret = ""
  list.map((id, index) => {
    if (index > 0) {
      ret += `, `
    }
    ret += `#${id}`
  })
  return ret
}

function getDecorations(doc, annotations) {
  const decorations = annotations
    .map((a) => [
      Decoration.inline(a.from, a.to, {
        class: `comment-highlight ${a.active ? "active" : ""}`,
        id: `comment-highlight-${a.context.id}`,
        "data-context-id": a.context.id,
        "data-context-id-list": getCommaSepList(a),
        "data-cy": "comment-highlight",
        dataset: { comment: a.context.id },
        style: "cursor: pointer"
      }),
      Decoration.widget(a.from, commentWidget(a))
    ])
    .reduce((a, c) => [...a, ...c], [])

  return decorations.length ? DecorationSet.create(doc, decorations) : null
}
function commentWidget({ click, context }) {
  let icon = document.createElement("i")
  icon.className = "q-icon material-icons no-pointer-events"
  icon.innerText = "chat_bubble"
  let button = document.createElement("button")
  button.className = "comment-widget no-border transparent"
  button.dataset.comment = context.id
  button.dataset.cy = "comment-widget"
  button.onclick = (...args) => click(context, ...args)
  button.appendChild(icon)
  return button
}

const emit = defineEmits(["editorReady"])

export const AnnotationPlugin = () =>
  new Plugin({
    key: AnnotationPluginKey,

    state: {
      init(_, { doc }) {
        return getDecorations(doc, [])
      },
      apply(transaction, state) {
        const action = transaction.getMeta(AnnotationPluginKey)
        ;() => {
          emit("editorReady")
        }
        if (action) {
          return getDecorations(transaction.doc, action.annotations)
        }
        return state
      }
    },

    props: {
      decorations(state) {
        return this.getState(state)
      }
    }
  })
