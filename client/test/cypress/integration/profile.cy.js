/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

describe("Profile", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/account/profile")
  })

  it("can update the name field", () => {
    cy.dataCy("update_user_name").clear().type("Updated User")
    cy.dataCy("button_save").click()
    cy.reload()
    cy.dataCy("avatar_name").contains("Updated User")
  })

  it("can update the username field", () => {
    cy.dataCy("update_user_username").clear().type("updatedUser")
    cy.dataCy("button_save").click()
    cy.reload()
    cy.dataCy("avatar_username").contains("updatedUser")
  })

  it("can update position title", () => {
    const value = "Updated position Title"
    cy.dataCy("position_title").type(value)
    cy.dataCy("button_save").click()
    cy.dataCy("button_saved").contains("Saved")
    cy.reload()
    cy.dataCy("position_title").should("have.value", value)
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
    const site1 = "https://pilcrow.lndo.site"
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
