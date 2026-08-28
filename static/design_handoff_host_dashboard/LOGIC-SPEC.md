# Business logic spec — Vietstays Host Dashboard

Read this together with `README.md` (visual spec). This document answers the five things
the backend/frontend implementation needs and that a rendered UI does not show:
apartment naming, price simulation, registration step rules, component structure, state.

---

## 0. First: what the HTML file actually is

It is **not** a compiled/bundled build, and there is no hidden minified source behind it.
It is a **design prototype written by hand as readable source**, in one file, with two parts:

```
Host Dashboard.dc.html
├── <x-dc> …markup… </x-dc>        ~lines 1–7400    the UI (plain HTML + inline styles)
└── <script> class Component { } </script>   ~lines 7400–14800   all the logic, plain readable JS
```

So the equivalent of `src/` is: **the `<script>` block**. Everything listed below is
literally in there, unminified, with the original names. Search the file for the
identifiers quoted in this document and you land on the real implementation.

Structure mapping to a conventional project:

| Conventional | In the prototype |
|---|---|
| `pages/` | `state.mainSection` / `state.view2` switch blocks in the markup (`<sc-if>`) |
| `components/` | markup sections in `<x-dc>`; not extracted into files by design |
| `hooks/`, `services/` | methods on `class Component` (`pmSuggest`, `computeSummaryForBase`, `saveChanges`, …) |
| `state store` | one React-style `this.state` object on `class Component` (section 5 below) |
| constants / config | top of the `<script>`: `PM_BASE`, `PM_DISTRICT_INDEX`, `DISTRICT_BUILDINGS`, `HOST_AGENTS`, … |

The prototype's job is to define behaviour precisely; the target app should re-implement it in
its own framework and file structure. Nothing here needs to be ported verbatim.

---

## 1. Apartment name generator

**Rule (implementation: variable `aptGeneratedName`)**

```
generatedName = [ buildingShortName, feature, district, apartmentType ]
                  .filter(nonEmpty)
                  .join(" - ")
```

- `buildingShortName` — the building's **short name** (`short`), set by superadmin when the
  building is created (`DISTRICT_BUILDINGS`: `{full: "Zenity Residents", short: "Zenity"}`).
  Never the full name.
- `feature` — host-typed free text ("Særpreg"), **max 20 chars**, optional.
- `district` — district code as stored (`D1`, `D7`, `Bình Thạnh`).
- `apartmentType` — `Studio | 1BR | 2BR | 3BR | 4BR | 5BR`.

Example: `Zenity - City View & Pool - D1 - 2BR`

**Override**: the field is read-only until the host clicks it (`addAptNameEditing`).
Effective name = `customName || generatedName`. If the host edits it back to exactly the
generated string, `customName` is cleared so the name resumes auto-following the form
(`onAptNameBlur`). Name editing is blocked until a building is chosen (`aptNameEditable`).

**The name is a structured key, not just a label.** `parseApt()` splits it back apart on
`" - "` into `{building, feature, district, br}`. Keep the four parts as real columns in the
API and derive the display name — do not parse strings server-side.

### 1.1 Match code (short apartment code, shown as monospace chip)

`matchCode(name)` → e.g. `ZCL102`:

```
buildingCode = first letter of building, uppercased            → "Z"
featureCode  = first + last letter of feature, uppercased,
               or "XX" when feature is empty                   → "CL"
districtCode = digits only if /^D\d+$/  ("D1" → "1"),
               else initials of each word ("Bình Thạnh" → "BT") → "1"
brNumber     = first number in the type, zero-padded to 2       → "02"
code = buildingCode + featureCode + districtCode + brNumber
```

### 1.2 Apartment id on save

`id = matchCode(effectiveName)`; on collision append `-{n}` where n = existing ids with that
prefix + 1 (`onAptSubmit`). Server should own uniqueness; use the same shape.

---

## 2. Price simulator

Two layers: a **superadmin price matrix** (the source of truth) and a **per-apartment
suggestion** derived from it.

### 2.1 Matrix defaults (`pmDefaultPrice`)

```
defaultPrice(district, buildingFullName, typeKey) =
    round50k( PM_BASE[typeKey] × PM_DISTRICT_INDEX[district] × buildingFactor(buildingFullName) )
```

`PM_BASE` (VND/night):

| typeKey | base |
|---|---|
| Studio | 900 000 |
| 1BR | 1 150 000 |
| 2BR+1WC | 1 500 000 |
| 2BR+2WC | 1 900 000 |
| 3BR+2WC | 2 200 000 |
| 4BR+2WC | 3 300 000 |
| 4BR+3WC | 3 600 000 |
| 5BR+3WC | 4 400 000 |

`PM_DISTRICT_INDEX`: D1 1.15 · D2 1.05 · D3 1.05 · D4 0.90 · D5 0.88 · D7 1.00 · D10 0.90 ·
Bình Thạnh 0.95 · Phú Nhuận 0.92 · Tân Bình 0.88 · Thủ Đức 0.90 · Gò Vấp 0.85 · unknown 0.85.

`buildingFactor` in the prototype is a deterministic hash of the building name
(`0.96 + (hash % 7)/58`, i.e. ~0.96–1.06) **purely to make demo data look plausible**.
👉 In production this must be a **stored per-building factor** (or a stored absolute price
per building × type cell) that superadmin edits — do not port the hash.

`round50k` = round to nearest 50 000 VND.

**Type key** (`pmKey(type, wc)`): `Studio` and `1BR` have no WC variant. Otherwise
`"{type}+{wc}WC"`, where `wc` falls back to `PM_DEFAULT_WC` = Studio 1 · 1BR 1 · 2BR 1 ·
3BR 2 · 4BR 2 · 5BR 3, and finally to the first existing key for that type.

### 2.2 Superadmin overrides

`state.priceMatrix` is keyed `"{district}|{buildingFullName}"` → `{ [typeKey]: number }`.
A cell that exists is an override; the UI marks overridden cells green/bold
(`pmCell().edited`). Reading a cell: override ?? computed default. Persist overrides only —
never persist computed defaults.

API shape suggestion:
`GET/PUT /price-matrix?district=D1` → `[{ buildingId, cells: { "2BR+2WC": 1900000, … } }]`.

### 2.3 Suggested price for a new apartment (`pmSuggest`)

```
suggested = round10k( matrixCell(district, building, pmKey(type, wc)) × STANDARD_FACTOR[standard] )
STANDARD_FACTOR = { Standard: 0.90, Superior: 1.00, Premium: 1.10 }
```

Set on the apartment at save time as `price`, with `priceModel: 'fixed'`. It is a
**suggestion** — the host may change it freely afterwards, and later matrix changes do not
retroactively overwrite an apartment's price (recompute only on explicit request).

### 2.4 Booking price simulation (new-booking modal, `priceCalc`)

```
gross      = nights × dailyRate
discountPct: if discount on →
               host agent selected  → agent.pct         (also sets agentCommissionPct)
               else ambassador code → ambassadorCode.pct
               else manual percent  → typed value
             else 0
total(guest pays) = round(gross × (1 − discountPct/100))
agentCommission   = round(total × agentCommissionPct/100)      // only when a host agent is set
netToHost         = total − agentCommission
```

Manual bookings additionally show the 5 % platform-fee footnote.

### 2.5 Booking detail / order summary (`computeSummaryForBase`, booking-detail block)

```
gross        = room total (nights × rate)
discount     = discountPct > 0 ? round(gross × discountPct/100) : 0
guestPays    = gross − discount + cleaningFee
platformPct  = bookingType ∈ {vietstays, manuell} ? 5 : 0     // external channels: 0
platformFee  = round(guestPays × platformPct/100)
agentCommission = when agentType == 'Host Agent' → per agent record
net          = guestPays − platformFee − agentCommission
```

All amounts VND, integers, `nb-NO` thousand separators, currency suffix as a separate
i18n key. Percentages are configured values (`PLATFORM_PCT`), not literals — expose them
from config/API.

---

## 3. Registration flow rules (add new apartment)

Steps, in order (`AptTabOrder`): `lokasjon → type → bilder → fasiliteter → bekreft`
(Location → Type & name → Photos → Facilities → Presentation).

**Navigation**
- Forward only via Next; you may click back to any **visited** step
  (`addAptMaxTabIdx` = highest step reached; later tabs are not clickable).
- Two steps have an internal sub-step that Next/Back traverses before leaving the step:
  - Photos: `add → building` (apartment photos, then building photos). Leaving the building
    sub-step marks the newly added building images as "just added" for 3 s (highlight).
  - Facilities: `apt → building`.

**Validation (`aptStepValid`)**
| Step | Valid when |
|---|---|
| Location | `district` set **and** `building` set; if `building === '__new'` (host proposed a new building) also `addAptNewBuildingSent` — i.e. the proposal must be submitted for approval first |
| Type & name | `type` set |
| Photos, Facilities, Presentation | always valid (no hard requirement) |

Invalid Next → set `addAptError` with the step message and do not advance. The Next button
renders orange `#e0793a` when valid, sand `#f2ead9` + `not-allowed` when not. The error is
shown in the footer for all steps except the last.

**Save (`onAptSubmit`)**
1. Hard re-check: `district`, `building`, `type` all present (else error, stay open).
2. `name = customName || generatedName` (§1); `id = matchCode(name)` + collision suffix (§1.2).
3. `price = pmSuggest(district, building, type, standard, wc)` (§2.3), `priceModel: 'fixed'`.
4. `status: 'Draft'` → becomes *Pending approval* when the draft is saved, then *Active*.
5. Reset the whole modal form, close it, navigate to the new apartment's detail page.

**New building proposal**: hosts cannot create buildings. Choosing "propose new building"
captures name + address and sends it for superadmin approval; the apartment can proceed but
the building stays pending. Buildings (full name + **short name** used in apartment names +
district) are owned by superadmin/supervisor.

---

## 4. Other rules worth knowing before wiring APIs

- **Nights** = check-out − check-in (never stored independently).
- **Period types** and their colours are codes: `vietstays | manuell | airbnb | blokkering`.
- Dates in the prototype are Norwegian display strings parsed by `parseNbDate`; production
  should use ISO dates end-to-end and format via `Intl`.
- **Reject countdown** on incoming bookings: `formatCountdown(rejectDeadline)` → `"3t 20min"`,
  `"utløpt"` past deadline. Deadline is server-owned.
- Everything textual is Norwegian in the prototype; see README §12 for the i18n plan
  (nb/en/vi, locale on the account, lockable by superadmin).

---

## 5. State inventory

The prototype keeps one flat state object. Group it into stores/queries as fits the stack —
server data (apartments, bookings, buildings, price matrix, agents) belongs in a data layer;
the rest is UI state.

**Server data**: `apartments[]`, `bookings[]` (+ `ALL_BOOKINGS_REF`), `priceMatrix{}`,
buildings (`DISTRICT_BUILDINGS`), `HOST_AGENTS`, `AMBASSADOR_CODES`, guests, activity log.

**Navigation**: `mainSection`, `view2` (`list|calendar`), `selectedBookingId`, `aptDetailId`,
`aptActiveTab`, `presStep`, `lang`.

**Filters**: `dateRange`, `customFrom/To`, `filterDistrict`, `filterBuilding`,
`filterApartment`, `filterStatuses[]`, `outstandingOnly`, `filterCommission`, `search`,
`buildingSearch`, `teamSearch`.

**Calendar**: `calendarWindowStart`, `calTypeFilter`, `draggingId`, `movedBookings`,
`moveModal`, `moveNotify`.

**Add-apartment wizard**: `addAptOpen`, `addAptTab`, `addAptMaxTabIdx`, `addAptForm`
(`country, city, district, building, feature, type, wc, buildingNo, floor, unitId,
customName, standard, description, images[], buildingImages[], facilities[]`),
`addAptError`, `addAptNameEditing`, `addAptImgSubTab`, `addAptFacSubTab`,
`addAptSelectedImgId`, `addAptJustAddedIds`, `addAptNewBuildingName/Address/Sent`.

**Add-booking modal**: `addType` (`manual|block|external`), `addForm` (incl.
`discountOn, discountType, discountPercent, discountAgent, discountAmbassador`),
`addFormError`, guest suggestion state.

**Edit tracking**: `pendingChanges` (per booking), `orderHistory`, `orderEditMode`,
`commissionDirty`, `aptDirtyCount`, `aptBarVisible`, `isScrollingDetail`.

---

## 6. Suggested implementation order

1. Buildings + districts + **price matrix** (superadmin), since apartment naming and pricing
   both depend on them.
2. Apartment create wizard with the §1/§3 rules server-validated.
3. Apartment detail (availability, price & terms, presentation).
4. Bookings: list + filters, then detail with the §2.5 summary, then calendar + move.
5. i18n from day one (README §12).
