
# Team Members
Pilcrow is a project of [MESH](https://meshresearch.net/) and [Public Philosophy Journal](https://www.publicphilosophyjournal.org) supported primarly through
financial support of the Andrew Mellon Foundation.


## MESH Research
[MESH](https://meshresearch.net/) is a collaborative effort of the [MSU College of Arts and Letters (CAL)](https://cal.msu.edu/) and the [MSU Libraries](https://library.msu.edu/) focused on the futures of digital scholarly publishing. MESH is a collaborative, agile, open-source team that supports the growth of both the developer and the community facilitation networks needed to enable 21st-century digital work in the areas of digital humanities and digital scholarly publishing.

<VPTeamMembers size="small" :members="coreMembers" />

### Extended Team
<VPTeamMembers size="small" :members="extendedMembers" />

### Emeriti
<VPTeamMembers size="small" :members="emeritiMembers" />

## Community
Pilcrow is deeply grateful for the support of our open source community in helping make Pilcrow a success.
<VPTeamMembers size="small" :members="community" />

## All-Contributors

Pilcrow aims to follow the [all-contributors spec](https://allcontributors.org).  We intend to recognize all contributions to the project.  Both the [GitHub app](https://allcontributors.org/docs/en/bot/usage) and [commandline client](https://allcontributors.org/docs/en/cli/usage) are available.  Contributions should be acknowledged as soon as practical and these tools help make it easier to do so!

<script setup>
import { computed } from 'vue'
import { VPTeamMembers } from 'vitepress/theme'
import { data } from '../api/contributors.data.ts'

const mapped = computed(() => data.map((m) => {
  return {
    ...m,
  }
}))

const coreMembers = computed(() => mapped.value.filter(m => m.group === 'core'))
const emeritiMembers = computed(() => mapped.value.filter(m => m.group === 'emeriti'))
const extendedMembers = computed(() => mapped.value.filter(m => m.group === 'extended'))
const community = computed(() => mapped.value.filter(m => (m.group ?? null) === null))
</script>
