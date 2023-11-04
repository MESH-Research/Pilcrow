import formatContributionType from 'all-contributors-cli/dist/generate/format-contribution-type'
import fs from 'node:fs'

const options = {
  repoType: 'github',
  projectOwner: 'mesh-research',
  projectName: 'pilcrow',

}

function formatType(type, contributor) {
  return formatContributionType(options, contributor, type)
}
export default {
  watch: ['../../.all-contributorsrc'],
  load(watchedFiles) {
    // watchedFiles will be an array of absolute paths of the matched files.
    // generate an array of blog post metadata that can be used to render
    // a list in the theme layout
    const options = JSON.parse(fs.readFileSync(watchedFiles[0], 'utf-8'))
    return options.contributors.map((contributor) => {
      return {
        ...contributor,
        desc: contributor.contributions?.map((type) => formatType(type, contributor)).join("&nbsp;") ?? '',
        avatar: contributor.avatar_url ?? `https://github.com/${contributor.login}.png`,
      }
    })
  }
}