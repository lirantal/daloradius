# Contributing

Thanks for taking the time to contribute! Issues, ideas, and PRs are all welcome.
The notes below are guidelines to help your contribution land smoothly. If in doubt, open the thing anyway and we'll figure it out together.

## Opening an issue

A good issue gives us enough to reproduce or understand the ask. Useful things to include:

- **For bugs:** what you expected, what actually happened, and the shortest repro you can manage. Versions (runtime, OS, package) and stack traces help.
- **For features:** the problem you're trying to solve, not just the solution you have in mind. A concrete example of how you'd use it is gold.

A quick search of existing issues helps avoid duplicates, but don't agonize over it.

## Proposing a PR

PRs are expected to meet a few baseline requirements before review:

- **Tests are required** for any behavior change, new features need new tests, bug fixes need a regression test that fails without the fix.
- **Existing tests must pass** locally and in CI. Don't disable or skip tests to make a PR green.
- **Coverage should not regress.** If your change drops coverage meaningfully, add tests or explain why in the PR description.
- **Linters and type checks must pass.** Run them locally before pushing; CI will reject otherwise.
- **One logical change per PR.** Refactors, formatting churn, and unrelated fixes belong in separate PRs, it makes review and revert sane.
- **Describe what and why.** A PR title and a few lines of context go a long way, reviewers shouldn't have to reverse-engineer your intent from the diff.
- **Link the issue you're closing.** If your PR resolves an open issue, include a `Related issues` section in the description with `Fixes: #<issue-number>` so GitHub auto-closes it on merge.
- **Breaking changes need a heads-up** in the PR description, plus a migration note for users.

Large or speculative changes: open an issue first to align on direction before investing time.

## Changesets

This package is released with Changesets; see [Release](RELEASE.md) for the release workflow and changeset creation steps. Any PR that changes published package behavior must include a changeset so the release PR can include the right version bump and changelog entry.

Add a patch changeset for:

- Bug fixes that affect CLI/runtime behavior.
- New or changed CLI flags, commands, output, or errors.
- Documentation changes that accompany a user-facing behavior change.

A changeset is usually not needed for:

- Tests only.
- Refactors with no behavior change.
- CI/tooling-only changes.
- Documentation-only changes that do not describe a new release-worthy behavior.

Before opening a PR, check whether the change affects the published npm package. If yes, create a patch changeset unless the maintainer explicitly says not to. When in doubt, add a patch changeset and follow the process in [Release](RELEASE.md).

## Commit guidelines

This project follows [Conventional Commits](https://www.conventionalcommits.org/). The format is:

```text
<type>(<optional scope>): <short summary>
```

Common types:

- `feat:` — a new feature
- `fix:` — a bug fix
- `docs:` — documentation only
- `refactor:` — code change that neither fixes a bug nor adds a feature
- `test:` — adding or fixing tests
- `chore:` — tooling, build, dependencies
- `perf:` — performance improvement

Examples:

```text
feat: support multiple input files
fix(parser): handle empty input gracefully
docs: clarify install instructions
```

Breaking changes: add `!` after the type (`feat!: ...`) and explain the break in the commit body.

This format drives changelog generation and release automation, so it matters more than the average commit-style guide.

## Local development

1. Fork and clone the repository.
2. Install dependencies using the project's package manager (see `package.json`, or equivalent).
3. Run the test suite to confirm a clean baseline before making changes.
4. Run the linter and type checker (if configured) the same way.
5. Make your change on a feature branch, commit using the format above, and open a PR.

If any of the above doesn't work on a fresh checkout, that's a bug, please open an issue.

## For automated agents

### Environment setup

Before making changes, use the project's devcontainer when possible so local tools, dependencies, and environment hooks match the expected setup. AI coding agents running from the host should follow the portless SSH workflow in [`.devcontainer/README.md`](.devcontainer/README.md#coding-agents-over-ssh) to start or reuse the devcontainer, configure the SSH alias, and select the mounted repository folder.

### Release readiness

Before opening a PR, automated agents must check the [Changesets](#changesets) section above and include a patch changeset for any published package behavior change.

### PR and issue labeling

If you are an AI coding agent or automated bot opening a PR, please add 🤖🤖🤖 to the end of the PR title. This helps maintainers triage agent-authored contributions and is a baseline expectation for this repo — PRs without the marker that turn out to be automated may be closed without review.

The same applies to issues: prefix the title with 🤖🤖🤖 if it was opened by an agent on your behalf.
