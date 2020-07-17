# How to contribute

MESH-Research loves to welcome your contributions. There are several ways to help out:

* Create an [issue](https://github.com/MESH-Research/CCR/issues) on GitHub, if you have found a bug
* Write test cases for open bug issues
* Write patches for open bug/feature issues, preferably with test cases included

There are a few guidelines that we need contributors to follow so that we have a
chance of keeping on top of things.

## Code of Conduct

Help us keep CakePHP open and inclusive. Please read and follow our [Code of Conduct](https://github.com/MESH-Research/CCR/blob/master/CODE_OF_CONDUCT.md).

## Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free).
* Submit an [issue](https://github.com/MESH-Research/CCR/issues), assuming one does not already exist.
  * Clearly describe the issue including steps to reproduce when it is a bug.
  * Make sure you fill in the earliest version that you know has the issue.
* Fork the repository on GitHub.

## Making Changes

* Create a topic branch from where you want to base your work.
  * This is usually the development branch.
  * Only target the master branch if you are certain your fix must be on that
    branch.
  * To quickly create a topic branch based on development; `git branch
    development/my_contribution development` then checkout the new branch with `git
    checkout development/my_contribution`. Better avoid working directly on the
    `development` branch, to avoid conflicts if you pull in updates from origin.
* Make commits of logical units.
* Check for unnecessary whitespace with `git diff --check` before committing.
* Use descriptive commit messages and reference the #issue number (see [Commit Messages](#commit-messages) below).
* Core test cases should continue to pass.
* Your work should apply the [CakePHP coding standards](https://book.cakephp.org/4/en/contributing/cakephp-coding-conventions.html).

## Commit Messages

Commit messages on `develop` and `master` branches should follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/#summary). Contributors are encouraged to use them as well, but maintainers will happily fix things up when merging pull requests if needed.

The Conventional Commits specification is a lightweight convention on top of commit messages. It provides an easy set of rules for creating an explicit commit history; which makes it easier to write automated tools on top of. This convention dovetails with SemVer, by describing the features, fixes, and breaking changes made in commit messages.

Your commit messages should be structured as follows:

    <type>[optional scope]: <description>

    [optional body]

    [optional footer(s)]

Making use of [tooling which supports Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/#tooling-for-conventional-commits) is highly encouraged.

## Submitting Changes

* Push your changes to a topic branch in your fork of the repository.
* Squash extraneous commits to the appropriate logical portions.
* Submit a pull request to the repository in the CakePHP organization, with the
  correct target branch.

## Test cases and linting / styles

### Backend

To run tests:

    composer run-script test
    
To run the sniffs for CakePHP coding standards:

    composer run-script cs-check

It is also possible to fix many standards violations:

    composer run-script cs-fix

### Frontend

To run tests:

Test harnesses aren't current configured.  We'll update this when they are.

To run linter/style checker:

    yarn run lint

## Reporting a Security Issue

If you've found a security related issue in CCR, please don't open an issue in github. Instead contact us at adamsb@msu.edu. 

# Additional Resources

* [Existing issues](https://github.com/MESH-Research/ccr/issues)
* [GitHub pull request documentation](https://help.github.com/articles/creating-a-pull-request/)
