import { Plugin, PluginKey } from "prosemirror-state"
//import { AnnotationState } from './AnnotationState'
import { DecorationSet, Decoration } from "prosemirror-view"

export const AnnotationPluginKey = new PluginKey("annotation")

function getDecorations(doc, annotations) {
  const decorations = annotations
    .map((a) => [
      Decoration.inline(a.from, a.to, {
        class: `comment-highlight ${a.active ? "active" : ""}`,
        "data-context-id": a.context.id,
        dataset: { comment: a.context.id },
        style: "cursor: pointer",
        onclick:
          "console.log(this.getAttribute('data-context-id')",
      }),
      Decoration.widget(a.from, commentWidget(a)),
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
export const AnnotationPlugin = () =>
  new Plugin({
    key: AnnotationPluginKey,

    state: {
      init(_, { doc }) {
        return getDecorations(doc, [])
      },
      apply(transaction, state) {
        const action = transaction.getMeta(AnnotationPluginKey)
        if (action) {
          return getDecorations(transaction.doc, action.annotations)
        }
        return state
      },
    },

    props: {
      decorations(state) {
        return this.getState(state)
      },
    },
  })
