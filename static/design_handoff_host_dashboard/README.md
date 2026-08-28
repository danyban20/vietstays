# Handoff: Vietstays Host Dashboard (property manager UI)

## About this package
The files here are **design references built in HTML** – a clickable prototype showing intended look and behaviour. It is **not** production code to copy. The job is to recreate the design in the project's own stack (React/Next, Vue, etc.) using its established patterns and component library. If no stack exists yet, pick one – everything below is framework-neutral.

> **Looking for the business logic?** See **`LOGIC-SPEC.md`** in this folder: apartment name
> generator, price simulator, registration step rules, state inventory — extracted with exact
> formulas and constants. It also explains where in the HTML source each rule lives.
>
> The HTML is **not** a bundled/compiled build. It is hand-written readable source: markup in
> the `<x-dc>` block, and all logic in one plain unminified `<script>` (`class Component`) from
> roughly line 7400 onwards. Every identifier quoted in `LOGIC-SPEC.md` is searchable in it.

**Fidelity: hi-fi.** Colours, typography, spacing and interactions are final. Match them closely.

**How to use the prototype:** open **`Host Dashboard (standalone).html`** in a browser (double-click) – it is fully self-contained and works offline. `Host Dashboard.dc.html` is the source version and needs `support.js` + `image-slot.js` in the same folder. Everything is interactive: sidebar navigation, bookings, apartments, calendar and all modals. Use it as the source of truth for anything not described here. It is designed for viewports ≥ 1400 px; below 1100 px the layout collapses to one column (see "Responsive behaviour").

**Language note:** the prototype UI copy is in Norwegian, since that is the product language for hosts. All labels are listed with English keys in section 12 (Localization) – build the UI with i18n keys from day one, not hardcoded strings.

---

## 1. Design tokens

### Colours
| Role | Hex |
|---|---|
| Primary dark green (sidebar, buttons, sticky bar) | `#12352b` |
| Accent orange (CTA, active tab, blocked period) | `#e0793a` |
| Accent sand/gold (alerts, "changes pending") | `#e0a458` |
| Sidebar secondary text | `#c9d6cd` / `#dfe6dd` |
| App background | `#faf7ee` |
| Sand panel background | `#f7f2e6` / `#f7f3e8` |
| Card / surface white | `#ffffff` |
| Light border | `#eee6d0` |
| Input border | `#ddd5bd` |
| Darker sand border | `#e8dcc0` |
| Faint divider | `#f2ead9` |
| Text primary | `#1c2b23` |
| Text secondary | `#5e6b62` / `#43503f` |
| Text muted | `#8a9187` |
| Muted uppercase label | `#9a9484` |
| Placeholder / neutral grey | `#c9c2ac`, `#b8b0a0` |
| Success green (text) | `#1f7a44`, bg `#e5f3ea`, border `#bfe3cd` |
| Warning orange (text) | `#b5651d`, bg `#fbead3`, border `#f0cf9e` |
| Negative amount / error | `#c4502a`, `#8a3a2a` |
| Draft status | bg `#f2ead9`, text `#8a6a3d` |
| Root app background (behind cards) | `#f2ead9` |
| Section wrapper card | `#fff` + border `#eee6d0`, radius 14 px |
| Nested card inside wrapper | `#faf7ef` + border `#f0e8d6`, radius 12 px |

**Entity identity colours** — every detail page is identified by one icon shape + colour. This is the primary "which kind of page am I on" signal:
| Entity | Icon shape | Colour | Rationale |
|---|---|---|---|
| Booking | rounded square, radius 13 px | `#e0793a` orange | converting, highest urgency |
| Host | circle (avatar) | `#e0793a` orange, tag `#c4602a` | converting, most essential actor |
| Management company | rounded square, radius 13 px | `#1f7a44` green (= verification-shield green) | passive/structural |
| Apartment | rounded square, radius 13 px | `#12352b` dark green | passive inventory |
| Customer | rounded square, radius 13 px | `#7a4b8a` purple | distinct third party |

**Period type colours (calendar cells + left edge of list cards):**
Vietstays booking `#12352b` · Blocked `#e0793a` · External `#c9c2ac`

### Typography
System font stack (as in the prototype). Scale in use:
| Use | Size / weight |
|---|---|
| Page title ("All apartments") | 26 px / 700 |
| Price display | 38 px / 700 (active price 26 px / 700) |
| Modal title | 22 px / 700 |
| Period title in detail card | 23 px / 700 |
| Card section heading ("Active / upcoming", "Stay", "Finance") | 18 px / 700 |
| Field value, important (guest, nights, check-in/out, cleaning, discount) | 19 px / 700 |
| Smaller card heading | 16 px / 700 |
| Body / input text | 14–14.5 px / 400–600 |
| Button text | 13.5–14.5 px / 600–700 |
| Field value, passive (channel, reference, registered by) | 14.5 px / 700 |
| Uppercase label, large group | 12.5 px / 700, letter-spacing .05em |
| Uppercase label, small | 11 px / 700, letter-spacing .06em |
| Micro label in finance box | 10.5 px / 700, letter-spacing .07em |

### Radius, shadow, spacing
- Radius: 8 px (buttons, inputs), 10 px (menus), 12 px (list cards, finance box), 14 px (panel cards, modals), 50 % (colour dots).
- Shadows: menu `0 12px 30px rgba(0,0,0,.15)`, modal overlay `rgba(0,0,0,.4)`, sticky bar `0 -4px 16px rgba(0,0,0,.15)`, toast `0 8px 20px rgba(0,0,0,.2)`.
- Left colour edge on period cards: `box-shadow: inset 10px 0 0 0 <type colour>`.
- Spacing rhythm: 4 / 6 / 8 / 10 / 12 / 14 / 16 / 18 / 20 / 22 / 24 / 26 / 28 / 36 px.
- Panel padding: 24 px 26 px. Modal padding: header 24 px 36 px 0, body 20 px 36 px, footer 16 px 36 px.
- Transitions: `opacity .3s ease`, `transform .3s ease`; booking-detail sticky bar `opacity .35s ease`.

---

## 2. Information architecture

Fixed left sidebar (dark `#12352b`, ~250 px) with groups:
- (untitled): Dashboard
- **Apartments & bookings**: My apartments (children: + Add new apartment, Prefilled apartment details, All apartments) · My bookings (children: + Add new, All bookings, Calendar)
- **My team**: Operations team, Management company, Host Agents, Ambassadors, Campaign
- **Finance & reports**: Finance, Reports
- **Communication & account**: Communication, Marketing, Host points / Exit, Settings

Active child item: background `#e0793a`, white text, weight 700. Inactive: `#c9d6cd`, weight 500.
A breadcrumb bar sits on a dark strip at the top of the content area.

Sections implemented in the prototype: **Bookings** (list, detail, calendar), **Apartments** (list, detail, new apartment), **Customers** (list, detail), and — under the superadmin role — **Management companies** (list, profile, admin view), **Hosts** (list, profile), **Buildings/price matrix**, **Host agents**.

Role switching happens from the user chip at the bottom of the sidebar ("Switch role"). Superadmin (Vietstays) sees the oversight sections; the host role sees its own portfolio. The sidebar starts at the very top of the window — no header above it.

---

## 3. Screens

### 3.0 Detail-page system (applies to ALL entity detail pages)

Booking, host, management company, apartment and customer detail pages share one skeleton. Recreate this as a single reusable shell:

**Header** (sticky, sits on the page beige — NOT on a white bar, and NOT full-bleed):
`padding: 20px 42px 15px 26px; display:flex; align-items:flex-start; gap:15px`
1. **Entity icon**, 52×52 px, `margin-top:1px`, colour + shape from the entity table above. SVG glyph inside is 31×31 px; hosts/customers show initials at 17 px/700 instead (or an uploaded photo when present).
2. **Title block** (`flex:1; min-width:0`):
   - Title: 25 px / 700, `letter-spacing:-.4px; line-height:1.15`, ellipsis on overflow.
   - Booking titles are two-part: `{reference} | {guest name}` — both at the same size/weight, separated by a `|` in `#cfc6ae`. The reference shrinks with ellipsis so the guest name is never pushed out.
   - Management company titles are preceded by a 21 px green verification shield when the company is a registered legal entity.
   - **Meta row** (`margin-top:4px`, gap 8 px, wraps): an entity pill + **at most two** further facts. Discipline matters here — three or four facts read as clutter.
     - Pill: 10.5 px / 700 uppercase, `letter-spacing:.05em`, radius 999 px, `padding:3px 10px`, white text on the entity colour. Content is `{ENTITY} · {ID}` (e.g. `MANAGEMENT COMPANY · F-004`, `HOST · H-001`) — folding the ID into the pill keeps the meta row short.
     - Remaining facts: 12.5 px, `#8a9187`, separated by a `·` in `#c9c2ac`.
3. **Back button** (`flex:none`, right-aligned, aligned to the right edge of the content column): chevron + "Back", `padding:8px 15px`, radius 8, border `#ddd5bd`, white fill, 12.5 px / 700. It must return the user to **wherever they came from**, not to a fixed list — these pages are reachable from several places.

**Tab strip** — directly under the header, on beige: `border-bottom:1px solid #e6dcc6; padding:8px 20px 0 26px`. Tabs are 14 px / 600, `padding:13px 16px`, active tab gets a 3 px bottom border in the entity colour and `#1c2b23` text; inactive `#8a9187`. Do **not** render tabs as buttons — they are navigation, and buttons would compete with the action rail.

Management company tab order: Overview · Bookings · Apartments (n) · Hosts (n) · Finance. Opens on Overview.

**Content column** — scrolls, inherits the page beige (no own background), `padding:16px 22px 24px`. Each tab body is wrapped in **one white section card**: `#fff`, border `#eee6d0`, radius 14 px, `padding:18px 20px 20px` (16px 18px 18px for table-heavy tabs). Cards *inside* that wrapper take the beige nested fill so they stay distinguishable; full-width tables inside the wrapper drop their own border to avoid frame-inside-frame.

**Right rail** — `flex: 0 0 336px` (292 px below 1360 px viewport width), runs full height from the top of the window, same beige as the content column with **no divider line**. Order: action group first, status second, then everything else. Cards use the nested-card treatment.

**Sticky bottom bar** — only on pages where editing is expected (booking edit, apartment edit, "Administer manager/host"). Dark bar, `box-shadow:0 -4px 16px rgba(0,0,0,.15)`, holding a context string on the left and, on the right, a change counter ("1 change pending" / "n changes pending" / "Everything saved"), an Undo button and a Save button. The Save button is orange `#e0793a` when dirty and a muted dark fill when clean. **Do not** put this bar on read-only pages such as a host profile — it implies pending work that isn't there.

**Activity log** — the overview tab shows the 5 most recent events with a "View all activity →" link that opens a right-hand drawer (520 px, max 82 %) over a `rgba(18,53,43,.34)` scrim. The drawer has a dark header, filter chips (All · Agreement · Host · Apartment · Booking · Finance) and rows carrying a colour dot, the event text, its category, timestamp and actor.


### 3.1 All bookings (list)
- Filter row: date range, district, building, apartment, status (multi), "outstanding only", commission, free-text search, reset.
- Table rows: guest, apartment, period, nights, amount, status pill, channel icon.
- Top-right action: **+ Add** dropdown (Manual booking / Block dates / External booking), 260 px wide, radius 10 px.
- List / calendar view switch.

### 3.2 Booking calendar
- Horizontal timeline: rows = apartments, columns = days, ~14-day window, prev/next/"Today" navigation, jump-to-date.
- Bookings render as blocks in their type colour; **drag & drop** moves a booking to another apartment/date → confirmation modal with guest notification choice (email / SMS / none).
- Type filter (all / Vietstays / external / blocked).

### 3.3 Booking detail / edit booking
- Apartment hero image, title, booking ID, created date.
- Key fields in a row: check-in, check-out, nights, guests (inline editable, pencil icon).
- **Order summary**: nightly rate × nights, cleaning, extra cleaning, discount, total, commission.
- Stay timeline + cleaning schedule.
- Right column: Actions (message guest, edit booking, cancel), Status, Next task, Guest information, Booking overview, Note, Access (door code, wifi – passive info).
- **Sticky bottom bar** (dark, pinned to the bottom of the scroll area): "N changes pending" in `#e0a458`, "Reset my changes", "Save", "Save and send to guest". The primary button turns orange `#e0793a` when changes exist, otherwise sand `#f2ead9`. The bar dims to 25 % opacity while scrolling and returns to 100 % ~150 ms after scrolling stops.

### 3.4 All apartments (list)
- One card per apartment: name, district · building, type · standard, price/night, status pill, completion percentage.
- Action: **+ Add new apartment** (opens the 5-step modal, see 3.7).

### 3.5 Apartment detail – shell
- Tab header: **Availability**, **Price & terms**, **Presentation**. Active tab: text `#12352b` + 3 px underline `#e0793a`; inactive `#8a9187`.
- Content scrolls in the middle column (padding 24 px 20 px 100 px).
- Right rail 350 px (`#f7f2e6`, left border `#eee6d0`): live status card (active check-in / blocked / vacant – colour per state), identity card (name, location, ID, status, completion with progress bar), registration checklist whose items jump to the matching step in Presentation.
- **Sticky bottom bar** (dark): "← All apartments", "All saved" / "N changes pending" in `#e0c896`, orange "Save". The bar is **hidden on open** (opacity 0, translateY 12 px, pointer-events none) and fades/slides in once scrollTop > 40 px.

### 3.6 Availability tab
Two-column grid: left `minmax(270px, 320px)`, right `1fr`, gap 20 px, cards stretch to equal height.

**Left card – "Active / upcoming"**
- Heading 18 px/700 with underline.
- Period cards (radius 12 px, 10 px inset left edge in the type colour): date range 19 px/700, subtitle (guest name or block reason) 14.5 px, chevron "›" right. Internal scrollbar when the list exceeds the card height.
- Hovering a card highlights that period in the calendar, dims other dates, and shows a dark tooltip with the type name at the cursor.
- Footer: "+ Add period" in `#e0793a`.

**Right card – calendar**
- Two months side by side (grid `repeat(2, minmax(0,1fr))`, gap 36 px; narrow screens `auto-fit minmax(230px,1fr)`).
- Weekday row Mon–Sun (12 px/700 `#9a9484`), date cells 42 px tall, radius 8 px, 15 px text; background/colour/border driven by period type, today marked with heavier weight.
- Below the calendars: colour legend (Vietstays booking, Blocked, External).

**Clicking a period card** swaps the right card to the period detail (3.6.1). The ✕ in the detail card returns to the calendar.

#### 3.6.1 Period detail
Same card frame as the calendar card. Contents:
1. **Header**: 11 px colour dot + type name (12 px/700 uppercase, in the type colour), below it the date range 23 px/700. A plain ✕ (20 px, `#8a9187`, no frame) top right – same close glyph as the "Add new apartment" modal.
2. **Grid 2.15 : 1** (gap 28 px, `align-items:start`; single column below 1100 px):
   - **Middle column** – four groups, each with an 18 px/700 heading + underline, fields in `repeat(auto-fit, minmax(170px, 1fr))`:
     1. *Stay*: Guest, Nights, Check-in, Check-out — label 12.5 px/700 uppercase, value 19 px/700
     2. *Extras & discount*: Cleaning, Extra cleaning (fallback "None"), Discount code, Commission (value "Host agent · [name]" or "Cash points (3%)") — same large format
     3. *Channel & reference*: Channel, Reference, Registered by — deliberately smaller (label 11 px, value 14.5 px), passive info
     4. *Internal note*: free-text input, full width
   - **Right column – Finance** (sand card `#f7f2e6`, border `#e8dcc0`, radius 12 px, padding 18 px): heading 18 px/700, then three blocks with a 10.5 px uppercase micro label and "description … amount" rows:
     - *Income*: Nightly rate (nights × rate), Cleaning, optional Discount code (−, red), **Effective GMV** (emphasised)
     - *Split*: Platform fee 5 % (−), Cash points 3 % (−), Host agent, **Host net** (emphasised)
     - *Vietstays net*: Platform fee, − Cash points, **Vietstays net**
     - For blocked periods this is replaced by "About the block" with explanatory text.
     - At the bottom of this column (pushed down with `margin-top:auto`, above a divider): **Delete** (white, grey text) and **Edit** (dark `#12352b`, white text), right-aligned.

### 3.7 Modal: Add new apartment (5 steps)
- Box 860 px wide (max 94 vw), max-height 90 vh, radius 14 px, header/body/footer where only the body scrolls. Plain ✕ top right.
- Step indicator (dots + line) and tabs: **Location → Type & name → Photos → Facilities → Presentation**. You may jump back to visited steps, not forward.
- Per-step validation: *Location* requires district + building (a new building must be submitted for approval first); *Type & name* requires apartment type. The Next button is orange when the step is valid, otherwise sand and inactive.
- Photos: upload with category panel, selected photo, drag to reorder, "just added" highlight.
- Facilities: sub-tabs apartment / building, custom facilities can be typed in.
- On save: ID generated from the name (unique suffix on collision), **suggested price** = `BUILDING_BASE_PRICE[type] × STANDARD_MULTIPLIER[standard]` (Studio 800,000 · 1BR 1.2 M · 2BR 2 M · 3BR 3 M · 4BR 4.5 M; Standard ×1.0 · Above average ×1.1 · Premium ×1.2), status "Draft", and the app navigates straight to the apartment detail.
- Footer: "Cancel" left, "Back"/"Next"/"Save" right.

### 3.8 Modal: Add booking / Block dates / External booking
Same shell as 3.7 (860 px, header/body/footer, plain ✕). Three variants driven by type:
| Variant | Title | Fields |
|---|---|---|
| manual | Add booking | apartment, guest (search/new), email, phone (country code + number), guests, nights, nightly rate, check-in, check-out, discount, price summary |
| block | Block dates | apartment + reason (note) on the same row, from, to |
| external | External booking | apartment, source (Airbnb/Booking.com/Trip.com), reference + "Fetch booking", guest, email (highlighted as important), phone, dates, amount, green info box about converting the guest |

- Guest search shows a suggestion list; a new guest triggers an orange note.
- Discount: on/off toggle → three types (Percentage, Ambassador code, Host Agent) with matching field.
- Price summary: total before discount, discount row, **total the guest pays**, and with Host Agent also commission + **net to you**.
- Manual bookings: footnote about the 5 % platform fee.

### 3.9 Modal: Business partners
Informational modal (480 px) about the umbrella organisation concept: intro, example view with avatar group, shared calendar overview.

---

## 4. Interactions & behaviour
- **Tab navigation** in the apartment detail scrolls to the matching section in the middle column.
- **Hover link** between period list and calendar (highlight + dim + tooltip following the cursor).
- **Sticky bottom bars**: booking detail dims while scrolling; apartment detail fades in past 40 px of scroll.
- **Drag & drop** in the booking calendar with a confirmation modal and notification options.
- **Toast** bottom-centre (dark, radius 8 px) on saved actions.
- **Dropdown menus** close on outside click / selection.
- Every clickable surface uses `cursor:pointer`; hovering rows/menu items gives a sand background (`#f7f3e8`).

## 5. Responsive behaviour
One breakpoint: **1100 px**. Below it: the apartment detail grid becomes one column, calendars use `auto-fit minmax(230px,1fr)`, the period detail 2.15:1 grid becomes one column, and the live status card moves above the content instead of into the right rail.

## 6. State (from the prototype – use as a starting point)
- Navigation: `mainSection` ('bookings' | 'apartments'), `view2` ('list' | 'calendar'), `selectedBookingId`, `aptDetailId`, `aptActiveTab`, `presStep`.
- Lists/filters: `dateRange`, `customFrom/To`, `filterDistrict`, `filterBuilding`, `filterApartment`, `filterStatuses[]`, `outstandingOnly`, `filterCommission`, `search`.
- Calendar: `calendarWindowStart`, `calTypeFilter`, `draggingId`, `movedBookings`, `moveModal`, `moveNotify`.
- Changes: `pendingChanges` (per booking), `aptDirtyCount`, `aptBarVisible`, `isScrollingDetail`.
- Periods: `aptSelPeriodIdx`, `aptPeriodModal`, `aptMonthEditIdx`.
- Modals: `addType` ('manual'|'block'|'external'), `addForm`, `addFormError`, `addAptOpen`, `addAptTab`, `addAptMaxTabIdx`, `addAptForm`, `addAptError`.
- Layout: `narrowLayout` (window.innerWidth < 1100).
- Locale: `locale` ('nb' | 'en' | 'vi'), see section 12.

## 7. Business rules
- **Nights** = check-out − check-in.
- **Room total** = nightly rate × nights. **GMV** = room total + cleaning − discount.
- **Discount** = (room total + cleaning) × discount %.
- **Platform fee** = 5 % of GMV. **Cash points** = 3 % of GMV.
- **Host net** = GMV − platform fee − cash points (minus host agent commission where applicable).
- **Vietstays net** = platform fee − cash points.
- All amounts in VND, thousands separated per locale, no decimals.
- Apartment status: Draft → Pending approval (when a draft is saved) → Active.
- Channel names: `vietstays` = "Vietstays direct booking", `blokkering` = "Manual block", `external` = "External (Airbnb)".

## 8. Data model (proposal)
```
Apartment { id, name, district, building, type, standard, price, priceModel: 'fixed'|'seasonal',
            minStay, cleaningFee, seasons[], images[], facilities[], buildingFacilities[],
            description, status, createdDate, periods[] }
Period    { type: 'vietstays'|'external'|'blocked', dates, nights, guest?, label?,
            note?, extras?, discountCode?, discountPct?, hostAgent?, reference?, registeredBy?, active? }
Booking   { id, apartment, guest, email, phone, country, from, to, nights, guests,
            dailyRate, cleaningFee, extras[], discount{type,value,code}, amount, status,
            source, reference, doorCode, wifiNetwork, wifiPassword, notes }
Guest     { id, name, email, phone, country, registered }
Building  { id, name, district, address, facilities[] }
```

## 9. Assets
- `apartment-photo.jpg` – hero image in booking detail and photo examples. Replace with real images from the API.
- Icons in the prototype are unicode glyphs (▦ 🏠 📋 👤 🏢 🤝 ★ ◎ 💰 📊 ✉ 📣 ⚙). Swap for the project's icon library.

## 10. Suggested build order
1. Shell: sidebar + breadcrumbs + routing for the two main sections.
2. Design tokens as theme variables.
3. Apartment list + **Add apartment flow** (5 steps, validation, price suggestion, status).
4. Apartment detail shell with tabs, right rail and sticky bottom bar.
5. Availability tab: period list + calendar + hover link, then period detail.
6. Booking list + filters, then booking detail with order summary and edit flow.
7. Booking calendar with drag & drop.

## 11. Files in this package

| File | What it is |
|---|---|
| `Host Dashboard (standalone).html` | **Start here.** Single self-contained file — open in a browser, no server needed. Contains every screen described above. |
| `Host Dashboard.dc.html` | Source of the same design (template + logic). Read this when you need exact values or handler logic. |
| `Forvaltningsbedrift.dc.html` | The host-owned management-company screens (create/join flow, member split, operations team). Separate file, same design language. |
| `support.js`, `image-slot.js` | Runtime the two source files load. Not part of the design — do not port. |
| `apartment-photo.jpg` | The one bundled photo. All other imagery is a drop-target placeholder. |
| `LOGIC-SPEC.md` | Business logic: naming, pricing, wizard rules, state. Read after this file. |
| `README.no.md` | Norwegian version of this document. |

## 12. Localization (build this in from day one)

**Locales:** `nb` (Norwegian bokmål – primary, what the prototype shows), `en` (English), `vi` (Vietnamese – operations staff in Vietnam).

**Architecture**
- One translation file per locale (`locales/nb.json`, `en.json`, `vi.json`), flat namespaced keys: `apartment.availability.title`, `period.detail.stay.checkIn`, `booking.modal.manual.title`.
- Never concatenate strings. Use interpolation with named variables: `"changesPending": "{{count}} changes pending"`, and plural forms via the i18n library (`_one` / `_other`).
- Locale-aware formatting through `Intl`: dates `Intl.DateTimeFormat(locale, {day:'numeric',month:'short',year:'numeric'})`, amounts `Intl.NumberFormat(locale)` with the currency suffix as its own key (`common.currency` = "VND").
- Percentages, ratios and colour tokens are locale-independent – keep them out of translation files.
- Weekday and month names must come from `Intl`, not from hardcoded arrays (the prototype hardcodes `ma ti on to fr lø sø`).
- Enum values (status, period type, channel, standard) are stored as **codes** in the data model and translated at render time: `status.draft`, `status.pendingApproval`, `status.active`, `periodType.vietstays`, `periodType.blocked`, `periodType.external`.
- Allow for text expansion: Vietnamese and English labels run 20–40 % longer than Norwegian. Every fixed-width element (sidebar items, buttons, table headers, the 12.5 px uppercase labels) must tolerate wrapping or truncation with a tooltip.

**Permission model (who decides the language)**
| Role | Can do |
|---|---|
| **Superadmin** | Sets the default locale for any account – admin or host – and may **lock** it (`localeLocked: true`) so the account cannot change it. Only superadmin can change another admin's locale. |
| **Admin** | Changes their own locale (unless locked by superadmin). Cannot change other admins' or hosts' locale; may only **preview** the UI in another locale for support purposes (session-only, see below). |
| **Host** | Changes their own locale from their profile / settings page, unless superadmin has locked it. |

Rules
- `users.locale` and `users.localeLocked` live on the account record. Superadmin writes both; the account owner may write `locale` only when `localeLocked` is false.
- When locked, the selector in the account's own settings renders disabled with an explanatory hint ("Set by your administrator"), not hidden.
- Changing a locale on someone else's behalf is an audited action: store actor, target account, old and new value, timestamp.
- Superadmin also sets the **tenant default locale**, used for new accounts and for anything sent before a user has logged in (invitations, system emails).

**Resolution order at render time**
1. Session preview locale (admin support preview, non-persisted).
2. `users.locale` on the signed-in account.
3. Tenant default locale set by superadmin.
4. `Accept-Language`.
5. Fallback `nb`.

Locale is part of app state and re-renders the whole tree; no page reload.

**UI to build**
1. **Host / admin – own language** (profile or settings page): a labelled select with the three locales, saved immediately with the standard dark toast on success. Disabled state with hint when `localeLocked`.
2. **Superadmin – account administration**: in the account/user detail view, a "Language" field with the locale select plus a "Lock language for this account" toggle. Same control appears in bulk form (set locale for selected accounts) in the account list.
3. **Superadmin – tenant settings**: default locale for new accounts.
4. **Admin support preview** (top-right of the breadcrumb bar, next to the account menu): compact selector showing the current locale code (NO / EN / VI), 13.5 px/700, text `#dfe6dd` on the dark strip, opening a 160 px dropdown (white, radius 10 px, border `#ddd5bd`, shadow `0 12px 30px rgba(0,0,0,.15)`, items 10 px 12 px, hover `#f7f3e8`, active item `#e0793a`). Choosing a locale here affects the current session only and never writes to any account record; while active, show a sand pill (`#f2ead9`, text `#8a6a3d`) reading "Preview: EN" with a reset action. Visible to admin and superadmin only – never to hosts.
- Recommended: a hidden dev-only pseudo-locale (`en-XA`, strings padded and bracketed) to catch hardcoded text and clipped layouts.

**Key inventory to seed the translation files** – every UI string in the prototype, grouped: sidebar nav (section 2), booking list filters and column headers (3.1), calendar controls and legend (3.2, 3.6), booking detail cards and sticky bar (3.3), apartment list card (3.4), apartment detail tabs, right rail and sticky bar (3.5), availability card and period detail groups (3.6, 3.6.1), the add-apartment steps and validation messages (3.7), the three booking modals including the green conversion notice and the 5 % footnote (3.8), business partners modal (3.9), toasts and error banners (4).
