# omnibase/notary

The notarial regime on [omnibase/office](https://github.com/glitchr-studio/omnibase-office): the site of a
French notary's office, within the profession's rules.

- **Compliance checks** in the back office's "Conformité" widget, each resting on a text cited in
  [docs/rules.md](docs/rules.md): the e-mail addresses end with `notaires.fr`, the site is declared to
  the chamber, the consumer mediator of the notariat is named, the tariff and the office's discounts
  are displayed, the office is identified, the wording carries no comparison, the profession's
  graphic charter is applied;
- **its pages**: the fields of practice (`/domaines`), the tariff (`/tarifs`: regulated, so explained
  and linked to the official text, never copied), the mediation (`/mediation`), a first appointment
  request open to someone who has no account yet (`/rendez-vous/demande`), the client's space (`/espace`)
  on the office's encrypted vault;
- **who of the office reads a client's document**: the notaries and the clerks; the reception sees
  that it exists, not what it says;
- **no online payment**: the bundle has none.

> **Compliance built into the code does not replace the chamber's control.** The checks help an
> office not to forget; the chamber of notaries verifies the sites of its notaries (règlement
> professionnel, art. 14.4) and remains the only judge of their conformity. What could not be read
> in an official text is listed as "to confirm" in [docs/rules.md](docs/rules.md), and is not
> written as a rule.

```sh
composer require omnibase/notary        # brings omnibase/office
```

Documentation: [docs/](docs/index.md). License: LGPL-3.0-or-later.
