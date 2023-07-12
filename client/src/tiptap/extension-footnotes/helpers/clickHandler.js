import { getAttributes } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { scroll } from 'quasar'

const { getScrollTarget, setVerticalScrollPosition } = scroll

const getOffsetTop = function (element) {
  if (!element) return 0
  return getOffsetTop(element.offsetParent) + element.offsetTop
}

export function clickHandler(options) {
  return new Plugin({
    key: new PluginKey('handleNoteLinkClick'),
    props: {
      handleClick: (view, _, event) => {
        if (event.button !== 0) {
          return false
        }

        const attrs = getAttributes(view.state, options.type.name)
        const link = (event.target)?.closest('a')

        const href = link?.href ?? attrs.href
        if (href.indexOf('#') === -1) {
          return false;
        }
        const targetEl = document.getElementById(href.split('#')[1])
        const scrollTarget = getScrollTarget(link)
        console.log(targetEl, scrollTarget, href.split('#'))
        const offsetTop = getOffsetTop(targetEl)
        if (targetEl && scrollTarget) {
          setVerticalScrollPosition(scrollTarget, offsetTop - 195, 250)
        }

        return true
      },
    },
  })
}
