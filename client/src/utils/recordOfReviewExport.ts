import exportCss from "src/components/ror/recordOfReview.export.css?raw"

const imageDataUriCache = new Map<string, Promise<string>>()

function fetchAsDataUri(url: string): Promise<string> {
  let cached = imageDataUriCache.get(url)
  if (!cached) {
    cached = fetch(url, { credentials: "same-origin" })
      .then((r) => {
        if (!r.ok) throw new Error(`fetch failed: ${url}`)
        return r.blob()
      })
      .then(
        (blob) =>
          new Promise<string>((resolve, reject) => {
            const reader = new FileReader()
            reader.onload = () => resolve(reader.result as string)
            reader.onerror = () => reject(reader.error)
            reader.readAsDataURL(blob)
          })
      )
    imageDataUriCache.set(url, cached)
  }
  return cached
}

async function inlineImages(root: ParentNode) {
  const imgs = Array.from(root.querySelectorAll("img"))
  await Promise.all(
    imgs.map(async (img) => {
      const src = img.getAttribute("src")
      if (!src || src.startsWith("data:")) return
      try {
        const absolute = new URL(src, window.location.origin).toString()
        img.setAttribute("src", await fetchAsDataUri(absolute))
      } catch {
        /* leave the original src; the export will degrade rather than fail */
      }
    })
  )
}

function absolutizeLinks(root: ParentNode) {
  root.querySelectorAll("a[href]").forEach((a) => {
    const href = a.getAttribute("href")
    if (!href) return
    if (href.startsWith("http") || href.startsWith("mailto:")) return
    a.setAttribute("href", new URL(href, window.location.origin).toString())
  })
}

export async function buildRorExportHtml(
  records: HTMLElement[],
  title: string
): Promise<string> {
  const doc = document.implementation.createHTMLDocument(title)
  doc.documentElement.lang = document.documentElement.lang || "en"
  const meta = doc.createElement("meta")
  meta.setAttribute("charset", "utf-8")
  doc.head.appendChild(meta)
  const style = doc.createElement("style")
  style.textContent = `${exportCss}\n.ror__document + .ror__document { page-break-before: always; margin-top: 3rem; }`
  doc.head.appendChild(style)
  // Page breaks between records are handled by the appended CSS, so each
  // record just needs its root element moved into the export body.
  records.forEach((el) => {
    const wrapper = doc.createElement("div")
    wrapper.innerHTML = el.outerHTML
    const ror = wrapper.firstElementChild
    /* v8 ignore next -- ror is never null: an element's outerHTML always has a root child */
    if (ror) doc.body.appendChild(ror)
  })
  await inlineImages(doc.body)
  absolutizeLinks(doc.body)
  return `<!DOCTYPE html>\n${doc.documentElement.outerHTML}`
}

export function buildRorExportBlob(html: string): Blob {
  return new Blob([html], { type: "text/html" })
}

export type RorZipEntry = {
  filename: string
  element: HTMLElement
  title: string
}

export async function buildRorZipBlob(entries: RorZipEntry[]): Promise<Blob> {
  const { default: JSZip } = await import("jszip")
  const zip = new JSZip()
  for (const entry of entries) {
    const html = await buildRorExportHtml([entry.element], entry.title)
    zip.file(entry.filename, html)
  }
  return zip.generateAsync({ type: "blob" })
}
