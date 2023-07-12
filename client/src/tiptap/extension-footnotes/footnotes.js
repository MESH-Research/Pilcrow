import { Mark } from '@tiptap/core'

import { clickHandler } from './helpers/clickHandler'


export const Footnotes = Mark.create({
  name: 'footnotes',

  priority: 10000,

  keepOnSplit: false,


  addAttributes() {
    return {
      href: {
        default: null,
      },
      role: {
        default: null,
      },
      id: {
        default: null
      }
    }
  },

  parseHTML() {
    return [{ tag: 'a[role=doc-noteref]' }, { tag: 'a[role=doc-backlink]' }]
  },

  renderHTML({ HTMLAttributes }) {
    return ['a', HTMLAttributes, 0]
  },

  addProseMirrorPlugins() {

    return [
      clickHandler({
        type: this.type,
      })
    ]
  },
})
