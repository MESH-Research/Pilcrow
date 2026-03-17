import { Plugin, PluginKey } from "@tiptap/pm/state"
import type { Node as ProsemirrorNode } from "@tiptap/pm/model"
import { Decoration, DecorationSet } from "@tiptap/pm/view"

export const AnnotationPluginKey = new PluginKey("annotation")

export interface AnnotationData {
  from: number
  to: number
  context: { id: string }
  active: boolean
  click: (context: { id: string }, event: MouseEvent) => void
}

export interface AnnotationPluginOptions {
  HTMLAttributes: Record<string, string | undefined>
  onUpdate: (decorations: DecorationSet | null) => void
  instance?: string
}

function getDecorations(doc: ProsemirrorNode, annotations: AnnotationData[]) {
  const decorations = annotations
    .map((a) => [
      Decoration.inline(a.from, a.to, {
        class: `comment-highlight ${a.active ? "active" : ""}`,
        id: `comment-highlight-${a.context.id}`,
        "data-context-id": a.context.id,
        "data-cy": "comment-highlight",
        "data-comment": a.context.id,
        style: "cursor: pointer"
      }),
      Decoration.widget(a.from, commentWidget(a))
    ])
    .reduce((a, c) => [...a, ...c], [])

  return decorations.length ? DecorationSet.create(doc, decorations) : null
}
function commentWidget({ click, context }: AnnotationData) {
  const icon = document.createElement("i")
  icon.className = "q-icon material-icons no-pointer-events"
  icon.innerText = "chat_bubble"
  const button = document.createElement("button")
  button.className = "comment-widget no-border transparent"
  button.dataset.comment = context.id
  button.dataset.cy = "comment-widget"
  button.onclick = (event) => click(context, event)
  button.appendChild(icon)
  return button
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
export const AnnotationPlugin = (_opts?: AnnotationPluginOptions) =>
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
      }
    },

    props: {
      decorations(state) {
        return this.getState(state)
      }
    }
  })
