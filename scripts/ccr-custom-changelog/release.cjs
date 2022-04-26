'use strict';
const conventionalChangelog = require('conventional-changelog-conventionalcommits');
const Q = require('q');

const config = {
	types: [
		{ type: "feat", section: "Features" },
		{ type: "fix", section: "Bug Fixes" },
		{ type: "perf", section: "Performance Improvements", hidden: false },
		{ type: "test", section: "Test Suite", hidden: false },
		{ type: "build", section: "Build System", hidden: false },
		{ type: "docs", section: "Documentation", hidden: false },
		{ type: "style", section: "Other", hidden: false },
		{ type: "refactor", section: "Other", hidden: false },
		{ type: "ci", section: "Other", hidden: false },
		{ type: "chore", section: "Other", hidden: false },
	]
};
/**
 * @type {Promise<Object>} preset with `parserOpts` and `writerOpts`.
 */
module.exports = conventionalChangelog(config).then(preset => {
		const oTFunc = preset.writerOpts.transform;
		preset.writerOpts.transform = (commit, context) => {
			commit.oType = commit.type;
			commit = oTFunc(commit, context);
			if (commit && commit.type === 'Other') {
				commit.scope = `${commit.oType}${commit.scope ? `(${commit.scope})` : ''}`;
			}
			return commit;
		};
		preset.writerOpts.commitGroupsSort = (a, b) => {
			const commitGroupOrder = ['Build System', "Test Suite", "Documentation", 'Reverts', 'Performance Improvements', 'Bug Fixes', 'Features']
			const gRankA = commitGroupOrder.indexOf(a.title)
			const gRankB = commitGroupOrder.indexOf(b.title)
			if (gRankA >= gRankB) {
				return -1
			} else {
				return 1
			}
		};
		return preset;
	});
