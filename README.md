# Rekentool Bewoner

De **Rekentool Bewoner** is een webapplicatie waarmee bewoners inzicht krijgen in hun energiegebruik en -opwek, en waarbij de resultaten (optioneel) worden opgeslagen voor analyse.

Deze repository bevat de **productiecode** + verwijzingen naar de **GitHub Wiki** voor beheer, documentatie en doorontwikkeling.

---

## Wat doet deze applicatie?

- Client-side berekening van energieverbruik en -opwek
- Directe feedback aan de gebruiker (zonder wachttijd)
- Asynchrone opslag van inzendingen
- Beheeromgeving voor:
  - Contentbeheer
  - Inzendingen bekijken / exporteren
  - Gebruikers en rollen (RBAC)

Belangrijke ontwerpkeuze: **berekenen en opslaan zijn losgekoppeld**  
→ de tool blijft bruikbaar, ook als opslag faalt.

---

##  Voor wie is deze repo?

### Developers / Technisch beheer
- onderhoud en bugfixes
- doorontwikkeling
- deployment & configuratie

### Admin / Beheer
- content aanpassen
- data exporteren
- gebruikers beheren

### Management / Product
- scope & risico’s begrijpen
- continuïteit bewaken

---

## Documentatie (GitHub Wiki)

 **Alle technische en beheerdocumentatie staat in de Wiki**

Startpunten:
- **Home** – overzicht per doelgroep
- **Admin Guide** – dagelijkse beheerhandelingen
- **Runbooks** – veelvoorkomende problemen & procedures
- **Architecture** – ontwerpkeuzes en technische context
- **Change Safely** – wijzigingen doorvoeren zonder breken

➡️ Ga naar: **Wiki tab in deze repository**

> Padconventie: in alle documentatie worden paden vermeld **vanaf htdocs**,  
> bijv. `/admin/data.php`.

---

##  Issues: bugs, vragen en verbeteringen

Gebruik **GitHub Issues** voor alles wat afwijkt van het verwachte gedrag, of voor ideeën en vragen.

### Maak een issue als je:
- een bug ziet
- een vraag hebt over gedrag
- een verbetering wilt voorstellen

We hebben Issue Templates voor:
- **Bug report**
- **Feature request**
- **Question**

 Voeg bij voorkeur toe:
- het betrokken pad (bv. `/admin/users.php`)
- stappen om te reproduceren
- verwacht vs. feitelijk gedrag
- logs of screenshots (**zonder secrets**)

> Twijfel je of iets “wilt of moet”?  
> Maak een issue – liever te vroeg dan te laat.

---

##  Bijdragen (Contributing)

Zie **`CONTRIBUTING.md`** voor:
- spelregels voor bijdragen
- PR-checklist
- afspraken over documentatie en contracts

**Belangrijkste principes:**
- kritieke contracten niet stil breken
- eerst een issue bij grotere wijzigingen
- documentatie bijwerken als gedrag wijzigt

---

##  Security

- Deel **geen secrets** in issues, commits of screenshots
- Security‑gevoelige zaken? Volg de interne meldroute

---

##  Status

- Actief in gebruik
- Documentatie onderhouden in Wiki
- Doorontwikkeling issue‑gedreven

---

##  Quick links

- Wiki → zie Wiki tab
- Issues → zie Issues tab
- Contributing → `CONTRIBUTING.md`

---
