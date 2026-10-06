---
title: omnibase/notary
order: 1
---

# omnibase/notary

The notarial regime on omnibase/office. What a regime brings: its mandatory mentions and its
safeguards, coded and tested; its own pages; its settings. The rules and their sources are in
[Rules and sources](rules.md).

> Compliance built into the code does not replace the chamber's control.

## Installation

```sh
composer require omnibase/notary          # brings omnibase/office
```

```php
// config/bundles.php
Base\Office\OfficeBundle::class => ['all' => true],
Base\Notary\NotaryBundle::class => ['all' => true],
```

```yaml
# config/routes.yaml - after omnibase/office's
notary_controller:
    resource: "@NotaryBundle/src/Controller/Client"
    type: attribute
notary_admin_controller:
    resource: "@NotaryBundle/src/Controller/Admin"
    type: attribute
```

```yaml
# config/packages/security.yaml
security:
    role_hierarchy:
        ROLE_NOTARY: [ROLE_STAFF]
        ROLE_CLERK:  [ROLE_STAFF]
        ROLE_STAFF:  [ROLE_USER]
        ROLE_ADMIN:  [ROLE_STAFF]
```

The roles are given by omnibase's groups (`Base\Entity\User\Group`): a group "Notaires" whose roles
are `[ROLE_NOTARY]`, a group "Clercs" with `[ROLE_CLERK]`, a group "Accueil" with `[ROLE_STAFF]`.

Then a migration: two tables, `notary_area` and `notary_area_member`.

In the layout, after the office's stylesheet:

```twig
<link rel="stylesheet" href="{{ asset('bundles/notary/css/notary.css') }}">
```

## Configuration

```yaml
# config/packages/notary.yaml - every key has a default
notary:
    roles: { notary: ROLE_NOTARY, clerk: ROLE_CLERK, staff: ROLE_STAFF }
    email_suffix: notaires.fr              # règlement professionnel, art. 16.3
    mediator:                              # art. 25.1: printed on /mediation and in the legal notice
        name: 'Médiateur de la consommation du notariat'
        address: '60 boulevard de La Tour-Maubourg, 75007 Paris'
        url: 'https://mediateur-notariat.notaires.fr'
    tariff:
        code_url: 'https://www.legifrance.gouv.fr/codes/texte_lc/LEGITEXT000005634379'   # code de commerce
        order: 'arrêté du 25 février 2026'                                                # the order in force
        order_url: 'https://www.legifrance.gouv.fr/eli/arrete/2026/2/25/ECOC2604872A/jo/texte'
        until: '2028-02-29'                # past it, the back office asks for the new order
        info_url: 'https://www.notaires.fr/fr/profession-notaire/le-tarif-du-notaire-emoluments-et-honoraires'
    wording: []                            # expressions added to the built-in list
```

The bundle sets three of omnibase/office's options (`prepend`): the warning above the contact form
(`@notary.contact.notice`: nothing confidential here), the menu of the client's space, and where
the booking e-mails send the client (`notary_space`).

## Settings (back office, Réglages)

What only the office knows, kept by omnibase's settings and read by `Base\Notary\Service\Record`:

| Setting | |
|---|---|
| `notary.office.holder` | the structure that holds the office (name and form) - the legal notice's publisher |
| `notary.office.crpcen` | the office's CRPCEN number |
| `notary.chamber.name` | the chamber the office depends on |
| `notary.chamber.declared_at` | the day the site was declared to it (`YYYY-MM-DD`) |
| `notary.brand.applied` | `oui` once the profession's graphic charter and brand block are applied |
| `notary.tariff.discounts` | the discounts practised, by category of deeds and band - or "Aucune remise" |
| `notary.tariff.fees` | the fees for what the tariff does not cover |

## Pages

| Route | Path | |
|---|---|---|
| `notary_areas`, `notary_area` | `/domaines`, `/domaines/{slug}` | the fields of practice (`Area`: summary, text, deeds, who follows it) |
| `notary_tariff` | `/tarifs` | what the costs are made of, the official text, the office's discounts and fees, no payment here |
| `notary_mediation` | `/mediation` | the office first, then the consumer mediator of the notariat and its coordinates |
| `notary_request` | `/rendez-vous/demande` | a first appointment asked without an account (see below) |
| `notary_space` | `/espace` | the client's space: appointments, documents |

Templates extend omnibase/office's layout (`office.templates.layout`) and are overridden the usual
way (`templates/bundles/NotaryBundle/client/...`). Twig: `notary_record()`, `notary_areas(member)`,
`notary_mediator()`, `notary_tariff()`.

### A first appointment without an account

omnibase/office's booking pages ask to sign in, and an office's clients are invited, not
registered: someone new could not ask. `/rendez-vous/demande` is a Symfony form
(`AppointmentRequestType`: the type - those in `request` mode open to everyone -, a member if they
know, name, e-mail, phone, a few words, omnibase's `PrivacyType` with its box) that calls
`Booker::request()` with no account: an appointment of the office with status `requested`, the
words encrypted like every reason, the office's "request received" e-mail sent. When the office
later invites that address, omnibase/office attaches the request to the new account.

## Who reads a document

`Base\Notary\Security\OfficeAudience` (an `AudienceResolverInterface` of omnibase/office's vault),
beyond the client and whoever sent the document:

| | sees it exists | reads it |
|---|---|---|
| a notary, a clerk (`ROLE_NOTARY`, `ROLE_CLERK`) | yes | yes |
| the rest of the staff (`ROLE_STAFF`) | yes | only what is not marked confidential |
| anyone else, the site's administrators included | no | no |

## Back office (with omnibase/admin)

The CRUD of the fields of practice (`Base\Notary\Entity\Area`), the settings section, and seven
checks in omnibase/office's `office_compliance` widget.

## Demonstration accounts

In glitchr/omnibase's `demo` environment (its `docs/20-architecture/demo.md`) the sign-in page offers
one button for each role of an office. `Base\Notary\Demo\NotaryDemoAccounts` declares them:

| Identifier | Role | |
|---|---|---|
| `notaire` | group "Notaires" (`ROLE_NOTARY`) | the agenda, the requests to settle, the clients' documents: reads them, sends some |
| `clerc` | group "Clercs" (`ROLE_CLERK`) | the same files, read as the notary reads them |
| `accueil` | group "Accueil" (`ROLE_STAFF`) | the agenda, the requests, the site's messages; sees that a document exists, reads nothing confidential |
| `etude` | `ROLE_ADMIN` | the office's administration: team, fields of practice, settings, access log, compliance |
| `client` | group "Clients" | the client's space: an appointment requested, a draft deed in the vault, a document deposited |

The password is the identifier. The fixtures take the accounts from omnibase's factory and attach
what makes them worth signing in as - a member of the team and an agenda, a request and documents:

```php
public function __construct(private readonly \Base\Demo\DemoAccountFactory $accounts) {}

$notaire = $this->accounts->account('notaire', $manager);   // created with its group, or the database's
```

An office without one of these roles leaves it out (`base.demo.exclude: [accueil]`); a site that
renames one declares the same identifier in its own provider. The labels are
`demo.<identifier>.label` and `.description` in the `notary` domain.

## More

[Rules and sources](rules.md)
