import { describe, expect, it, vi, beforeEach, afterEach } from "vitest"
import {
  buildRorExportHtml,
  buildRorExportBlob,
  buildRorZipBlob
} from "./recordOfReviewExport"

function recordEl(inner: string, className = "ror"): HTMLElement {
  const el = document.createElement("div")
  el.className = className
  el.innerHTML = inner
  return el
}

function okFetchReturning(bytes: string) {
  return vi.fn(async () => ({
    ok: true,
    blob: async () => new Blob([bytes], { type: "image/png" })
  }))
}

describe("buildRorExportHtml", () => {
  beforeEach(() => {
    vi.stubGlobal("fetch", okFetchReturning("img-bytes"))
  })
  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it("emits a full HTML document with the title and exported styles", async () => {
    const html = await buildRorExportHtml([recordEl("<p>body</p>")], "My Title")
    expect(html.startsWith("<!DOCTYPE html>")).toBe(true)
    expect(html).toContain('lang="en"')
    expect(html).toContain('charset="utf-8"')
    // page-break rule the builder appends after the imported CSS
    expect(html).toContain(".ror + .ror")
  })

  it("appends each record's root element to the document body", async () => {
    const html = await buildRorExportHtml(
      [recordEl("<p>first</p>"), recordEl("<p>second</p>")],
      "Two"
    )
    expect(html).toContain("first")
    expect(html).toContain("second")
    // both .ror wrappers land in the body
    expect(html.match(/class="ror"/g)).toHaveLength(2)
  })

  it("inlines relative image sources as data URIs", async () => {
    const html = await buildRorExportHtml(
      [recordEl('<img src="/avatar.png">')],
      "Img"
    )
    expect(html).toContain("data:image/png")
    expect(html).not.toContain('src="/avatar.png"')
    expect(fetch).toHaveBeenCalledTimes(1)
  })

  it("leaves data-URI images untouched and does not fetch them", async () => {
    const html = await buildRorExportHtml(
      [recordEl('<img src="data:image/png;base64,AAAA">')],
      "Data"
    )
    expect(html).toContain("data:image/png;base64,AAAA")
    expect(fetch).not.toHaveBeenCalled()
  })

  it("keeps the original src when the image fetch fails", async () => {
    vi.stubGlobal(
      "fetch",
      vi.fn(async () => ({ ok: false, blob: async () => new Blob([]) }))
    )
    const html = await buildRorExportHtml(
      [recordEl('<img src="/broken-unique.png">')],
      "Broken"
    )
    expect(html).toContain('src="/broken-unique.png"')
    expect(html).not.toContain("data:image/png")
  })

  it("absolutizes relative links but leaves http and mailto links alone", async () => {
    const html = await buildRorExportHtml(
      [
        recordEl(
          '<a href="/foo">rel</a>' +
            '<a href="http://ext.example/x">abs</a>' +
            '<a href="mailto:a@b.com">mail</a>'
        )
      ],
      "Links"
    )
    expect(html).toContain(`href="${window.location.origin}/foo"`)
    expect(html).toContain('href="http://ext.example/x"')
    expect(html).toContain('href="mailto:a@b.com"')
  })

  it("caches a fetched image so a repeated source hits the network once", async () => {
    const url = "/cached-once.png"
    await buildRorExportHtml([recordEl(`<img src="${url}">`)], "A")
    await buildRorExportHtml([recordEl(`<img src="${url}">`)], "B")
    expect(fetch).toHaveBeenCalledTimes(1)
  })
})

describe("buildRorExportBlob", () => {
  it("wraps the HTML string in a text/html Blob", async () => {
    const blob = buildRorExportBlob("<!DOCTYPE html><html></html>")
    expect(blob).toBeInstanceOf(Blob)
    expect(blob.type).toBe("text/html")
    expect(await blob.text()).toContain("<!DOCTYPE html>")
  })
})

describe("buildRorZipBlob", () => {
  beforeEach(() => {
    vi.stubGlobal("fetch", okFetchReturning("img-bytes"))
  })
  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it("produces a zip Blob containing an HTML file per entry", async () => {
    const blob = await buildRorZipBlob([
      { filename: "alice.html", element: recordEl("<p>alice</p>"), title: "A" },
      { filename: "bob.html", element: recordEl("<p>bob</p>"), title: "B" }
    ])
    expect(blob).toBeInstanceOf(Blob)

    const { default: JSZip } = await import("jszip")
    const zip = await JSZip.loadAsync(blob)
    expect(zip.file("alice.html")).toBeTruthy()
    expect(zip.file("bob.html")).toBeTruthy()
    const alice = await zip.file("alice.html")!.async("string")
    expect(alice).toContain("<!DOCTYPE html>")
    expect(alice).toContain("alice")
  })
})
