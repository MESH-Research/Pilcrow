export function a11yLogViolations(violations) {

    function wrapTarget(target) {
        return `${target}`.split("> ").map((e) => ({target: e}))
    }
    cy.task(
        'log',
        `${violations.length} accessibility violation${violations.length === 1 ? '' : 's'
        } ${violations.length === 1 ? 'was' : 'were'} detected`
    )
    // pluck specific keys to keep the table readable
    const violationData = violations.map(
        ({ id, impact, description, nodes }) => ({
            id,
            impact,
            description,
            nodes: nodes.length
        })
    )

    cy.task('table', violationData)

    const violationNodes = violations.map(
        ({ id, nodes }) => nodes.map(({ target }) => {
            const targetParts = wrapTarget(target)
            return [{ id, target: targetParts[0].target }, ...targetParts.slice(1) ]
        }).reduce((acc, e) => acc.concat(e, [{id: '====================', target: '-----------------------------------------------'}]), [])
    ).reduce((acc, e) => acc.concat(e), [])

    cy.task('table', violationNodes)
}