# Handoff: Vietstays Host Dashboard (forvalter-flate)

## Om denne pakken
Filene her er **designreferanse laget i HTML** – en klikkbar prototype som viser tenkt utseende og oppførsel. Den er **ikke** produksjonskode som skal kopieres rett inn. Oppgaven er å gjenskape designet i prosjektets egen stack (React/Next, Vue, e.l.) med etablerte mønstre og komponentbibliotek. Finnes ingen stack ennå, velg selv – strukturen under er rammeverk-nøytral.

**Fidelitet: hi-fi.** Farger, typografi, spacing og interaksjoner er ferdig bestemt. Gjenskap så nært som mulig.

**Slik bruker du prototypen:** åpne `Host Dashboard.dc.html` i en nettleser (dobbeltklikk). Alt er interaktivt: naviger i sidemenyen, åpne bookinger, leiligheter, kalender og modaler. Bruk den som sannhetskilde for detaljer som ikke er beskrevet her. Prototypen er bygget for skjermbredder ≥ 1400 px; under 1100 px kollapser layouten til én kolonne (se «Responsiv oppførsel»).

---

## 1. Design tokens

### Farger
| Rolle | Hex |
|---|---|
| Primær mørk grønn (sidebar, knapper, bunnrad) | `#12352b` |
| Aksent oransje (CTA, aktiv fane, blokkering) | `#e0793a` |
| Aksent sand/gull (varsel, «endringer venter») | `#e0a458` |
| Sidebar sekundærtekst | `#c9d6cd` / `#dfe6dd` |
| Sidebakgrunn (app) | `#faf7ee` |
| Panelbakgrunn sand | `#f7f2e6` / `#f7f3e8` |
| Kort/flate hvit | `#ffffff` |
| Kantlinje lys | `#eee6d0` |
| Kantlinje input | `#ddd5bd` |
| Kantlinje sand mørkere | `#e8dcc0` |
| Skillelinje svak | `#f2ead9` |
| Tekst primær | `#1c2b23` |
| Tekst sekundær | `#5e6b62` / `#43503f` |
| Tekst dempet | `#8a9187` |
| Label dempet (uppercase) | `#9a9484` |
| Placeholder / nøytral grå | `#c9c2ac`, `#b8b0a0` |
| Suksess grønn (tekst) | `#1f7a44`, bakgrunn `#e5f3ea`, kant `#bfe3cd` |
| Advarsel oransje (tekst) | `#b5651d`, bakgrunn `#fbead3`, kant `#f0cf9e` |
| Negativt beløp / feil | `#c4502a`, `#8a3a2a` |
| Utkast-status | bg `#f2ead9`, tekst `#8a6a3d` |

**Statusfarger for periodetyper (kalender + venstre kant i liste):**
Vietstays-booking `#12352b` · Blokkering `#e0793a` · Ekstern `#c9c2ac`

### Typografi
Systemfont-stack (samme som prototypen). Skala i bruk:
| Bruk | Størrelse / vekt |
|---|---|
| Sidetittel («Alle leiligheter») | 26 px / 700 |
| Pris-display | 38 px / 700 (aktiv pris 26 px / 700) |
| Modaltittel | 22 px / 700 |
| Periodetittel i detaljkort | 23 px / 700 |
| Seksjonsoverskrift i kort («Aktive / kommende», «Opphold», «Økonomi») | 18 px / 700 |
| Feltverdi, viktig (gjest, netter, check-in/ut, cleaning, rabatt) | 19 px / 700 |
| Kortoverskrift mindre | 16 px / 700 |
| Brødtekst / inputtekst | 14–14,5 px / 400–600 |
| Knappetekst | 13,5–14,5 px / 600–700 |
| Feltverdi, passiv (kanal, referanse, registrert av) | 14,5 px / 700 |
| Label uppercase, stor gruppe | 12,5 px / 700, letter-spacing .05em |
| Label uppercase, liten | 11 px / 700, letter-spacing .06em |
| Micro-label i økonomiboks | 10,5 px / 700, letter-spacing .07em |

### Radius, skygge, spacing
- Radius: 8 px (knapper, input), 10 px (menyer), 12 px (kort i liste, økonomiboks), 14 px (panelkort, modaler), 50 % (fargeprikker).
- Skygger: meny `0 12px 30px rgba(0,0,0,.15)`, modal-overlay `rgba(0,0,0,.4)`, sticky bunnrad `0 -4px 16px rgba(0,0,0,.15)`, toast `0 8px 20px rgba(0,0,0,.2)`.
- Venstre fargekant på periodekort: `box-shadow: inset 10px 0 0 0 <typefarge>`.
- Spacing-rytme: 4 / 6 / 8 / 10 / 12 / 14 / 16 / 18 / 20 / 22 / 24 / 26 / 28 / 36 px.
- Panelpadding: 24 px 26 px. Modalpadding: header 24 px 36 px 0, body 20 px 36 px, footer 16 px 36 px.
- Overganger: `opacity .3s ease`, `transform .3s ease`, sticky bunnrad i bookingdetalj `opacity .35s ease`.

---

## 2. Informasjonsarkitektur

Fast venstre sidebar (mørk `#12352b`, bredde ~250 px) med grupper:
- (uten tittel): Dashboard
- **Leiligheter og bookinger**: Mine leiligheter (undernivå: + Legg til ny leilighet, Prefilled apartment details, Alle leiligheter) · Mine bookinger (undernivå: + Legg til ny, Alle bookinger, Kalender)
- **Mitt team**: Driftsteam, Forvaltningsbedrift, Host Agents, Ambassadører, Kampanje
- **Økonomi og rapporter**: Økonomi, Rapporter
- **Kommunikasjon og konto**: Kommunikasjon, Marketing, Host-poeng / Exit, Innstillinger

Aktivt undermenyvalg: bakgrunn `#e0793a`, hvit tekst, vekt 700. Inaktive: `#c9d6cd`, vekt 500.
Toppen av innholdsområdet har en brødsmulerad på mørk bakgrunn.

Hovedseksjoner implementert i prototypen: **Bookinger** (liste, detalj, kalender) og **Leiligheter** (liste, detalj, ny leilighet). Øvrige menypunkter er ikke bygget.

---

## 3. Skjermer

### 3.1 Alle bookinger (liste)
- Filterrad: datointervall, bydel, bygg, leilighet, status (multi), «kun utestående», provisjon, fritekstsøk, nullstill.
- Tabellrader med gjest, leilighet, periode, netter, beløp, status-pill, kanal-ikon.
- Handling øverst til høyre: **+ Legg til** (dropdown: Manuell booking / Blokker datoer / Ekstern booking), bredde 260 px, radius 10 px.
- Visningsbryter liste / kalender.

### 3.2 Kalendervisning (bookinger)
- Horisontal tidslinje: rader = leiligheter, kolonner = dager. Vindu på ~14 dager, navigasjon forrige/neste/«I dag», hopp til dato.
- Bookinger vises som blokker med typefarge; **dra-og-slipp** flytter en booking til ny leilighet/dato → åpner bekreftelsesmodal (varsle gjest via e-post/SMS/ingen).
- Filter på type (alle / Vietstays / ekstern / blokkering).

### 3.3 Bookingdetalj / endre booking
- Toppbilde av leiligheten, tittel, booking-ID, registreringsdato.
- Nøkkelfelter i rad: innsjekk, utsjekk, netter, gjester (redigerbare med blyantikon).
- **Ordreoppsummering** med linjer: dagspris × netter, cleaning, ekstra rengjøring, rabatt, totalpris, provisjon.
- Tidslinje for opphold + rengjøringsplan.
- Høyre kolonne: Handlinger (send melding, endre booking, kanseller), Status, Neste oppgave, Gjesteinformasjon, Bookingsoversikt, Notat, Tilgang (dørkode, wifi – passiv info).
- **Sticky bunnrad** (mørk, sitter i bunnen av scrollområdet): «N endringer venter» i `#e0a458`, «Nullstill mine endringer», «Lagre», «Lagre og send til gjest». Primærknappen blir oransje `#e0793a` når det finnes endringer, ellers sand `#f2ead9`. Raden dempes til 25 % opasitet mens brukeren scroller og går tilbake til 100 % ~150 ms etter at scrollingen stopper.

### 3.4 Alle leiligheter (liste)
- Kort per leilighet: navn, bydel · bygg, type · standard, pris/natt, status-pill, fullføringsprosent.
- Handling: **+ Legg til ny leilighet** (åpner 5-stegs modal, se 3.7).

### 3.5 Leilighetsdetalj – skall
- Faneheader: **Tilgjengelighet**, **Pris & vilkår**, **Presentasjon**. Aktiv fane: tekst `#12352b` + 3 px understrek `#e0793a`; inaktiv `#8a9187`.
- Innholdet scroller i midtkolonnen (padding 24 px 20 px 100 px).
- Høyre rail 350 px (`#f7f2e6`, venstre kant `#eee6d0`): live-statuskort (aktiv innsjekk / blokkert / ledig – farge etter tilstand), identitetskort (navn, lokasjon, ID, status, fullføringsprosent med progressbar), registreringssjekkliste med klikkbare punkter som hopper til riktig steg i Presentasjon.
- **Sticky bunnrad** (mørk): «← Alle leiligheter», «Alt lagret» / «N endringer venter» i `#e0c896`, oransje «Lagre». Raden er **skjult ved åpning** (opacity 0, translateY 12 px, pointer-events none) og glir/fader inn når scrollTop > 40 px.

### 3.6 Fanen Tilgjengelighet
Todelt grid: venstre `minmax(270px, 320px)`, høyre `1fr`, gap 20 px, kortene strekker seg til samme høyde.

**Venstre kort – «Aktive / kommende»**
- Overskrift 18 px/700 med understrek.
- Liste av periodekort (radius 12 px, inset venstre fargekant 10 px etter type): dato-intervall 19 px/700, undertittel (gjestenavn eller blokkeringsgrunn) 14,5 px, chevron «›» til høyre. Intern scrollbar når listen er høyere enn kortet.
- Hover på et kort: perioden markeres i kalenderen til høyre, øvrige datoer dempes, og en mørk tooltip viser typenavn ved musepekeren.
- Bunn: «+ Legg til periode» i `#e0793a`.

**Høyre kort – kalender**
- To måneder side ved side (grid `repeat(2, minmax(0,1fr))`, gap 36 px; ved smal skjerm `auto-fit minmax(230px,1fr)`).
- Ukedagsrad ma–sø (12 px/700 `#9a9484`), datoceller 42 px høye, radius 8 px, 15 px tekst; bakgrunn/farge/kant settes av periodetype, i dag markeres med vekt.
- Under kalenderne: fargeforklaring (Vietstays-booking, Blokkering, Ekstern).

**Klikk på et periodekort** bytter høyre kort til periodedetalj (3.6.1). ✕ i detaljkortet går tilbake til kalenderen.

#### 3.6.1 Periodedetalj
Kortet har samme ramme som kalenderkortet. Innhold:
1. **Header**: fargeprikk 11 px + typenavn (12 px/700, uppercase, i typefargen), under det dato-intervallet 23 px/700. Rent ✕ (20 px, `#8a9187`, ingen ramme) øverst til høyre – samme kryss som i «Legg til ny leilighet».
2. **Grid 2,15 : 1** (gap 28 px, `align-items:start`; én kolonne under 1100 px):
   - **Midtkolonne** – fire grupper, hver med 18 px/700 overskrift + understrek, felter i `repeat(auto-fit, minmax(170px, 1fr))`:
     1. *Opphold*: Gjest, Netter, Check-in, Check-out — label 12,5 px/700 uppercase, verdi 19 px/700
     2. *Tillegg & rabatt*: Cleaning, Ekstra rengjøring (fallback «Ingen»), Rabattkode, Provisjon (verdi «Host agent · [navn]» eller «Cashpoeng (3%)») — samme store format
     3. *Kanal & referanse*: Kanal, Referanse, Registrert av — bevisst mindre (label 11 px, verdi 14,5 px), passiv info
     4. *Intern kommentar*: fritekst-input, full bredde
   - **Høyrekolonne – Økonomi** (sandkort `#f7f2e6`, kant `#e8dcc0`, radius 12 px, padding 18 px): overskrift 18 px/700, deretter tre blokker med micro-label 10,5 px uppercase og linjer «beskrivelse … beløp»:
     - *Inntekt*: Dagspris (netter × sats), Cleaning, evt. Rabattkode (−, rød), **Effektiv GMV** (uthevet)
     - *Fordeling*: Plattformgebyr 5 % (−), Cashpoeng 3 % (−), Host agent, **Host netto** (uthevet)
     - *Vietstays netto*: Plattformgebyr, − Cashpoeng, **Vietstays netto**
     - For blokkeringer vises i stedet «Om blokkeringen» med forklarende tekst.
     - Nederst i denne kolonnen (skjøvet ned med `margin-top:auto`, over en skillelinje): **Slett** (hvit, grå tekst) og **Rediger** (mørk `#12352b`, hvit tekst) høyrejustert.

### 3.7 Modal: Legg til ny leilighet (5 steg)
- Boks 860 px bred (max 94 vw), max-høyde 90 vh, radius 14 px, header/body/footer der bare body scroller. Rent ✕ øverst til høyre.
- Stegindikator (prikker + linje) og faner: **Lokasjon → Type & navn → Bilder → Fasiliteter → Presentasjon**. Man kan hoppe tilbake til besøkte steg, ikke fremover.
- Validering per steg: *Lokasjon* krever bydel + bygg (nytt bygg må sendes inn til godkjenning først); *Type & navn* krever leilighetstype. Neste-knappen er oransje når steget er gyldig, ellers sand og inaktiv.
- Bilder: opplasting med kategori-panel, valgt bilde, omrokkering (dra), «nettopp lagt til»-markering.
- Fasiliteter: undertabs leilighet / bygg, egne tilleggsfasiliteter kan skrives inn.
- Ved lagring: ID genereres fra navn (unikt suffiks ved kollisjon), **foreslått pris** = `BUILDING_BASE_PRICE[type] × STANDARD_MULTIPLIER[standard]` (Studio 800 000 · 1BR 1,2 M · 2BR 2 M · 3BR 3 M · 4BR 4,5 M; Standard ×1.0 · Over middels ×1.1 · Premium ×1.2), status «Utkast», og appen navigerer rett til leilighetsdetaljen.
- Footer: «Avbryt» venstre, «Tilbake»/«Neste»/«Lagre» høyre.

### 3.8 Modal: Legg til booking / Blokker datoer / Ekstern booking
Samme skall som 3.7 (860 px, header/body/footer, rent ✕). Tre varianter styrt av type:
| Variant | Tittel | Felter |
|---|---|---|
| manual | Legg til booking | leilighet, gjest (søk/ny), e-post, telefon (landkode + nummer), gjester, netter, dagspris, innsjekk, utsjekk, rabatt, prisoppsummering |
| block | Blokker datoer | leilighet + grunn (notat) på samme rad, fra, til |
| external | Ekstern booking | leilighet, kilde (Airbnb/Booking.com/Trip.com), referanse + «Hent booking», gjest, e-post (uthevet som viktig), telefon, datoer, beløp, grønn info-boks om å konvertere gjesten |

- Gjestesøk viser forslagsliste; ny gjest gir en oransje merknad.
- Rabatt: av/på-bryter → tre typer (Gi rabatt %, Ambassadørkode, Host Agent) med tilhørende felt.
- Prisoppsummering: totalpris uten rabatt, rabattlinje, **totalpris gjest betaler**, og ved Host Agent også provisjon + **netto til deg**.
- Manuelle bookinger: fotnote om 5 % plattformgebyr.

### 3.9 Modal: Business partners
Informasjonsmodal (480 px) om paraplyorganisasjon: intro, eksempelvisning med avatargruppe, og oversikt over delte kalendere.

---

## 4. Interaksjoner og oppførsel
- **Fane-navigasjon** i leilighetsdetalj scroller til riktig seksjon i midtkolonnen.
- **Hover-kobling** mellom periodeliste og kalender (markering + demping + tooltip som følger musen).
- **Sticky bunnrader**: bookingdetalj dempes ved scroll; leilighetsdetalj fader inn ved scroll > 40 px.
- **Dra-og-slipp** i bookingkalenderen med bekreftelsesmodal og varslingsvalg.
- **Toast** nederst midt på skjermen (mørk, radius 8 px) ved lagrede handlinger.
- **Dropdown-menyer** lukkes ved klikk utenfor / valg.
- Alle klikkbare flater har `cursor:pointer`; hover på listerader/menyvalg gir sandbakgrunn (`#f7f3e8`).

## 5. Responsiv oppførsel
Ett bruddpunkt: **1100 px**. Under det: leilighetsdetaljens grid går til én kolonne, kalenderne til `auto-fit minmax(230px,1fr)`, periodedetaljens 2,15:1-grid til én kolonne, og live-statuskortet flyttes inn over innholdet i stedet for høyre rail.

## 6. State (fra prototypen – bruk som utgangspunkt)
- Navigasjon: `mainSection` ('bookings' | 'apartments'), `view2` ('list' | 'calendar'), `selectedBookingId`, `aptDetailId`, `aptActiveTab`, `presStep`.
- Lister/filtre: `dateRange`, `customFrom/To`, `filterDistrict`, `filterBuilding`, `filterApartment`, `filterStatuses[]`, `outstandingOnly`, `filterCommission`, `search`.
- Kalender: `calendarWindowStart`, `calTypeFilter`, `draggingId`, `movedBookings`, `moveModal`, `moveNotify`.
- Endringer: `pendingChanges` (per booking), `aptDirtyCount`, `aptBarVisible`, `isScrollingDetail`.
- Perioder: `aptSelPeriodIdx`, `aptPeriodModal`, `aptMonthEditIdx`.
- Modaler: `addType` ('manual'|'block'|'external'), `addForm`, `addFormError`, `addAptOpen`, `addAptTab`, `addAptMaxTabIdx`, `addAptForm`, `addAptError`.
- Layout: `narrowLayout` (window.innerWidth < 1100).

## 7. Forretningsregler
- **Netter** = utsjekk − innsjekk.
- **Rombeløp** = dagspris × netter. **GMV** = rombeløp + cleaning − rabatt.
- **Rabatt** = (rombeløp + cleaning) × rabatt% .
- **Plattformgebyr** = 5 % av GMV. **Cashpoeng** = 3 % av GMV.
- **Host netto** = GMV − plattformgebyr − cashpoeng (minus host agent-provisjon der den finnes).
- **Vietstays netto** = plattformgebyr − cashpoeng.
- Alle beløp i VND, formatert med punktum som tusenskille (nb-NO), uten desimaler.
- Leilighetsstatus: Utkast → Venter godkjenning (ved lagring av utkast) → Aktiv.
- Kanalnavn: `vietstays` = «Vietstays direktebooking», `blokkering` = «Manuell blokkering», `external` = «Ekstern (Airbnb)».

## 8. Datamodell (forslag)
```
Apartment { id, name, district, building, type, standard, price, priceModel: 'fast'|'sesong',
            minStay, cleaningFee, seasons[], images[], facilities[], buildingFacilities[],
            description, status, createdDate, periods[] }
Period    { type: 'vietstays'|'external'|'blokkering', dates, nights, guest?, label?,
            note?, extras?, discountCode?, discountPct?, hostAgent?, reference?, registeredBy?, active? }
Booking   { id, apartment, guest, email, phone, country, from, to, nights, guests,
            dailyRate, cleaningFee, extras[], discount{type,value,code}, amount, status,
            source, reference, doorCode, wifiNetwork, wifiPassword, notes }
Guest     { id, name, email, phone, country, registered }
Building  { id, name, district, address, facilities[] }
```

## 9. Assets
- `apartment-photo.jpg` – brukt som toppbilde i bookingdetalj og bildeeksempel. Erstatt med reelle bilder fra API.
- Ikoner i prototypen er unicode-tegn (▦ 🏠 📋 👤 🏢 🤝 ★ ◎ 💰 📊 ✉ 📣 ⚙). Bytt til prosjektets ikonbibliotek.

## 10. Anbefalt byggerekkefølge
1. Skall: sidebar + brødsmuler + routing for de to hovedseksjonene.
2. Design tokens som variabler/tema.
3. Leilighetsliste + **Legg til leilighet-flyten** (5 steg, validering, prisforslag, status).
4. Leilighetsdetalj-skall med faner, høyre rail og sticky bunnrad.
5. Fanen Tilgjengelighet: periodeliste + kalender + hover-kobling, deretter periodedetalj.
6. Bookingliste + filtre, så bookingdetalj med ordreoppsummering og endringsflyt.
7. Bookingkalender med dra-og-slipp.

## 11. Filer i pakken
- `Host Dashboard.dc.html` – hovedprototypen (alle skjermer og modaler).
- `apartment-photo.jpg` – bilde brukt i prototypen.
- Tidligere versjoner finnes i prosjektet (`Host Dashboard (5-step original).dc.html`, `(før omstrukturering)`, `(bilder-sidebar-checklist)`) hvis designhistorikk er interessant – ikke nødvendig for implementasjon.


---

## 12. Språk og roller (engelsk versjon i README.md har full spesifikasjon)

**Locales:** `nb` (primær), `en`, `vi`.

**Rettigheter**
| Rolle | Kan gjøre |
|---|---|
| **Superadmin** | Setter standardspråk for enhver konto – admin eller host – og kan **låse** det (`localeLocked`) slik at kontoen ikke kan endre selv. Bare superadmin kan endre en annen admins språk. |
| **Admin** | Endrer sitt eget språk (hvis ikke låst). Kan ikke endre andres språk, men kan **forhåndsvise** grensesnittet i et annet språk for support – kun for økten, lagres ikke. |
| **Host** | Velger sitt eget språk i profil/innstillinger, med mindre superadmin har låst det. |

- `users.locale` og `users.localeLocked` ligger på kontoen. Låst tilstand vises som deaktivert velger med hint («Satt av administrator»), ikke skjult.
- Endring av andres språk logges (aktør, konto, fra/til, tidspunkt).
- Superadmin setter også **tenant-standardspråk** for nye kontoer og systemutsendelser.

**Rekkefølge ved rendering:** support-forhåndsvisning → `users.locale` → tenant-standard → `Accept-Language` → `nb`.

**UI som skal bygges:** (1) språkvelger i egen profil/innstillinger, (2) språkfelt + låsebryter på kontoen i superadmins kontoadministrasjon (også som masseoperasjon), (3) tenant-standard i superadmin-innstillinger, (4) support-forhåndsvisning i toppbaren for admin/superadmin, med sandfarget «Preview: EN»-pille og nullstilling.
