import { Extension } from "@tiptap/core"
import { Blockquote } from "@tiptap/extension-blockquote"
import { Bold } from "@tiptap/extension-bold"
import { BulletList } from "@tiptap/extension-bullet-list"
import { Code } from "@tiptap/extension-code"
import { CodeBlock } from "@tiptap/extension-code-block"
import { Document } from "@tiptap/extension-document"
import { Dropcursor } from "@tiptap/extension-dropcursor"
import { Gapcursor } from "@tiptap/extension-gapcursor"
import { HardBreak } from "@tiptap/extension-hard-break"
import { Heading } from "@tiptap/extension-heading"
import { Highlight } from "@tiptap/extension-highlight"
import { History } from "@tiptap/extension-history"
import { HorizontalRule } from "@tiptap/extension-horizontal-rule"
import { Italic } from "@tiptap/extension-italic"
import { Link } from "@tiptap/extension-link"
import { ListItem } from "@tiptap/extension-list-item"
import { OrderedList } from "@tiptap/extension-ordered-list"
import { Paragraph } from "@tiptap/extension-paragraph"
import { Strike } from "@tiptap/extension-strike"
import { Text } from "@tiptap/extension-text"
import { Annotation } from "../extension-annotation"
import { Footnotes } from "../extension-footnotes"

export const SubmissionContentKit = Extension.create({
  name: "submissionContentKit",

  addExtensions() {
    const extensions = []

    extensions.push(Blockquote)

    extensions.push(Bold)

    extensions.push(BulletList)

    extensions.push(Code)

    extensions.push(CodeBlock)

    extensions.push(Document)

    extensions.push(Dropcursor)

    extensions.push(Gapcursor)

    extensions.push(HardBreak)

    extensions.push(Heading)

    extensions.push(History)

    extensions.push(HorizontalRule)

    extensions.push(Italic)

    extensions.push(
      ListItem.extend({
        addAttributes() {
          return {
            id: {
              default: null
            },
            role: {
              default: null
            }
          }
        }
      })
    )

    extensions.push(OrderedList)

    extensions.push(Paragraph)

    extensions.push(Strike)

    extensions.push(Footnotes)
    extensions.push(Highlight)

    extensions.push(Text)
    extensions.push(Link)
    extensions.push(Annotation.configure(this.options?.annotation))

    return extensions
  }
})
