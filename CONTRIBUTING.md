# Contributing

Dank voor je bijdrage! Deze repository bevat een productiegerichte, frameworkloze webapp.

## Eerst: maak een Issue
Bij voorkeur start je met een Issue (Bug/Feature/Question) voordat je een grote wijziging maakt.

## Belangrijkste regels
- **Gebruik altijd paden vanaf htdocs** in docs en issues (bv. `/admin/data.php`).
- **Geen secrets** in commits of issues (geen wachtwoorden, tokens, database credentials).
- **Houd de kritieke contracten intact** (zie `Change-Safely.md`).
- Update **documentatie** als je gedrag wijzigt.

## PR checklist
- [ ] Smoke tests uitgevoerd (zie `Change-Safely.md`)
- [ ] Docs bijgewerkt (wiki en/of README)
- [ ] Geen secrets toegevoegd
- [ ] UI/UX impact beschreven in PR

## Code stijl
- Kleine, begrijpelijke changes.
- Liefst geen grote refactors zonder voorafgaand issue/plan.
