/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"

describe("Profile", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@ccrproject.dev" })
    cy.visit("/account/metadata")
  })

  it("can update professional title", () => {
    const value = "Updated Professional Title"
    cy.dataCy("professional_title").type(value)
    cy.dataCy("button_save").click()
    cy.dataCy("button_saved").contains("Saved")
    cy.reload()
    cy.dataCy("professional_title").should("have.value", value)
  })

  it("can update facebook username", () => {
    const value = "facebookusername"
    cy.dataCy("facebook").type(value)
    cy.dataCy("button_save").click()
    cy.dataCy("button_saved").contains("Saved")
    cy.reload()
    cy.dataCy("facebook").should("have.value", value)
  })

  it("can add websites to the websites editable list and re-order them via clickable arrows", () => {
    const site1 = "https://ccr.lndo.site"
    const site2 = "https://yahoo.com"
    cy.dataCy("websites_list_control")
      .within(() => {
        cy.dataCy("input_field").type(site1 + "{enter}")
        cy.dataCy("input_field").type(site2 + "{enter}")
        cy.dataCy("arrow_upward_1").click()
      })
    cy.dataCy("button_save").click()
    cy.dataCy("button_saved").contains("Saved")
    cy.dataCy("websites_list_control")
      .within(() => {
        cy.dataCy("edit_btn_0").click()
        cy.dataCy("edit_input_0").should("have.value", site2)
      })
  })

  it("can add interest keywords", () => {
    cy.dataCy("interest_keywords_control")
      .within(() => {
        const word1 = "manuscript"
        const word2 = "video"
        cy.dataCy("input_field").type(word1 + "{enter}")
        cy.dataCy("input_field").type(word2 + "{enter}")
        cy.dataCy("tag_list").contains(word1)
        cy.dataCy("tag_list").contains(word2)
      })
  })
})
