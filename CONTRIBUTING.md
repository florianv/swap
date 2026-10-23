# Contributing to Swap

Thanks for helping. Swap is maintained by volunteers, so the most useful contribution is one a maintainer can review quickly: small, tested, and explained.

Swap is a thin layer over [Exchanger](https://github.com/florianv/exchanger), which holds the provider implementations. A bug in how a provider calls its API or parses its response belongs in Exchanger. Swap covers the `Builder`, the `Swap` facade, the service factory and the documentation.

## Before you start

For anything larger than a bug fix, open an issue first and describe what you want to change and why. This avoids work on something that won't be merged.

When you report a bug, include the Swap and Exchanger versions, the PHP version, the providers you configured, the code that fails, and the full exception message.

## Development setup

```bash
composer install
composer test      # PHPUnit
composer psalm     # static analysis
composer cs:check  # coding style; composer cs:fix applies it
```

CI runs the tests on PHP 8.2 to 8.5, plus Psalm, the coding style check and a backward compatibility check. A pull request is ready for review when they all pass.

## Pull requests

- Keep one change per pull request. Unrelated refactoring makes review slower and is better sent separately.
- Add or update tests for every behaviour you change.
- Explain the problem and the fix in the description, in a few sentences. Link the issue if there is one.
- Don't break the public API in a minor release. If a change can't be made without a break, say so and we'll plan it for the next major version.

## Adding a provider

Providers live in Exchanger. Add the provider there first, following its [contributing guide](https://github.com/florianv/exchanger/blob/master/CONTRIBUTING.md#adding-a-provider). Swap picks up every service in Exchanger's registry automatically, so once a release of Exchanger includes it, Swap only needs a documentation update: the provider tables in `README.md` and `doc/readme.md`, plus a version bump of `florianv/exchanger` in `composer.json` if the provider needs it.

## AI-assisted contributions

You may use AI tools. A human must stay in the loop and own the result, because every pull request, issue and comment costs a maintainer's time to read.

**Disclose it.** If AI wrote a significant part of a pull request, issue or comment, say so in its description and name the tool. Add a trailer to commits that contain AI-generated code:

```
Assisted-by: <tool and model>
```

Autocomplete of a few lines doesn't need disclosure.

**Review it yourself first.** Before you submit, you must have:

- read and understood every changed line, and be able to explain it without asking the AI again;
- run `composer test`, `composer psalm` and `composer cs:check` locally;
- checked every claim about a provider or about Exchanger against its documentation or code. AI tools invent options, identifiers and behaviour;
- removed anything you can't justify: unrelated changes, speculative improvements, filler text.

**Keep it short.** Write issues and comments in your own words, or edit AI output down to what matters. A long generated report that nobody checked is not a contribution.

**Answer as a human.** Reply to review comments yourself. Don't paste a reviewer's question into an AI and post the answer unchecked.

Maintainers may close contributions that don't follow this section without a detailed review, including pull requests and issues opened autonomously by AI agents. Maintainers follow the same rules: automated reviews in this repository are marked with 🤖.

## Security issues

Please don't report security problems in a public issue. Email the maintainer at florian@voutzinos.com instead, with enough detail to reproduce the problem.
