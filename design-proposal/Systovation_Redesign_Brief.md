# SYSTOVATION PUBLISHING — Website Redesign Brief
## Merging Existing Infrastructure with New Content & Design

Date: March 25, 2026
Current live URL: https://systovation.pages.dev
Target URL: https://www.systovation.com

---

## WHAT THIS DOCUMENT DOES

The current Systovation site has strong functional infrastructure (JSON-driven catalog, flipbook preview, lead capture, admin panel). This document preserves ALL existing functionality while replacing:

1. **The visual design** — from sage green/warm white to charcoal/copper (premium literary)
2. **The homepage content** — from generic showcase to positioned publishing house
3. **The "Get Published" page content** — from generic services to the content in the attached `systovation_homepage.html`
4. **The booking link** — from cal.com/sukant-ratnakar/45min to cal.com/sukantratnakar/lets-talk

**Attached files:**
1. `systovation_homepage.html` — Complete homepage with new content, layout, and styling
2. This document — Complete redesign instructions

---

## IMMEDIATE SECURITY ACTION

**Change the admin panel password immediately.** The current password (visible in the original handoff document) must be replaced before any further work. Use a strong password that is not derived from family names or dates.

Admin URL: /admin.html

---

## DESIGN SYSTEM — REPLACE ENTIRELY

### Remove Old Design Tokens
```
DELETE:
--bg: #FAFAF8
--bg-alt: #F5F4F0
--accent: #2D5A4A
--accent-light: #3D7A6A
--gold: #B8860B
```

### New Design Tokens — Charcoal + Copper

```css
:root {
  /* Backgrounds */
  --charcoal: #2C2C2C;           /* Primary dark — ink-like warmth */
  --warm-charcoal: #3A3A3A;      /* Alternating dark sections */
  --parchment: #F7F3EB;          /* Light backgrounds — papery, NOT white */
  --cream: #EDE8DD;              /* Subtle variation, forms */
  
  /* Copper Accent */
  --copper: #B87333;             /* Primary accent — book foil stamp feel */
  --copper-soft: #CFA064;        /* Pull quotes, italic accent text */
  --copper-tint: rgba(184,115,51,0.12);  /* Subtle borders, hover states */
  
  /* Text */
  --text-dark: #1C1C1C;          /* Headings on light backgrounds */
  --text-body: #4A4A4A;          /* Body on light backgrounds */
  --text-light: #8A8A8A;         /* Captions, secondary text */
  --text-on-dark: rgba(255,255,255,0.80);  /* Body on charcoal */
  --text-muted-dark: rgba(255,255,255,0.40); /* Subtitles on charcoal */
  
  /* Functional */
  --highlight-bg: #FAF5ED;       /* Callout boxes */
  --border: rgba(184,115,51,0.12); /* Card/section borders */
  --shadow: 0 4px 20px rgba(0,0,0,0.08);
  --radius: 4px;                 /* Sharp corners — NOT 12px */
  
  /* Typography */
  --font-display: 'Libre Baskerville', serif;
  --font-body: 'Source Sans Pro', sans-serif;
}
```

### Typography — Replace Fonts

**Remove:** Playfair Display + Inter
**Add:**
```css
@import url('https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Source+Sans+Pro:wght@300;400;600&display=swap');
```

| Element | Font | Weight | Size |
|---------|------|--------|------|
| H1 / Hero | Libre Baskerville | 400 | 36-40px |
| H2 / Section titles | Libre Baskerville | 400 | 28-32px |
| H3 / Card titles | Libre Baskerville | 400 | 18-20px |
| Section labels | Source Sans Pro | 400 | 11px uppercase, letter-spacing 3px, copper |
| Body text | Source Sans Pro | 300 | 15px |
| Card descriptions | Source Sans Pro | 300 | 14px |
| Buttons | Source Sans Pro | 400 | 12px uppercase, letter-spacing 2px |

### Design Rules

1. **No pure white backgrounds.** Use #F7F3EB (parchment) for all light sections.
2. **No pure white text on dark.** Use rgba(255,255,255,0.80) for body.
3. **Alternate charcoal/parchment sections.** Never two same-tone adjacent.
4. **Border-radius: 4px everywhere** — not 12px. Sharp corners feel more bookish.
5. **No drop shadows on cards.** Use 1px solid rgba(184,115,51,0.12) borders.
6. **Copper divider lines:** 40px wide, 1px height, #B87333, centered.
7. **Paragraph max-width: 540px.** Body text should not stretch edge-to-edge.
8. **Section padding:** 80-90px top/bottom desktop, 50-60px mobile.
9. **No scroll animations.** No parallax, no fade-ins, no slide-ins.

### Button Style
```css
.cta-btn {
  display: inline-block;
  border: 1px solid #B87333;
  color: #B87333;
  background: transparent;
  padding: 14px 40px;
  font-family: 'Source Sans Pro', sans-serif;
  font-size: 12px;
  letter-spacing: 2px;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.3s;
}
.cta-btn:hover {
  background: #B87333;
  color: #2C2C2C;
}
```

---

## PAGE-BY-PAGE INSTRUCTIONS

### Page 1: HOMEPAGE (index.html) — FULL CONTENT REPLACEMENT

**Source:** `systovation_homepage.html`

Replace the current homepage content entirely with the content from the attached HTML file. The structure is:

| Section | Background | Content |
|---------|-----------|---------|
| Navigation | Transparent → charcoal on scroll | Systovation Publishing | Services | Process | Who We Publish | Start a Conversation |
| Hero | Charcoal | "Your experience deserves better than a manuscript sitting in a drawer." |
| Philosophy | Parchment | "We don't do assembly-line publishing." + pull quote |
| Services (5 cards) | Charcoal | Ghostwriting, Guided Self-Pub, Imprint, Editing, Strategy |
| Process (4 steps) | Cream | Discovery → Architecture → Craft → Publication |
| Who We Publish | Warm charcoal | Executives, Consultants, Professionals |
| The Imprint | Parchment | Why "Published by Systovation" matters + feature box |
| About | Warm charcoal | About the writer section (see below) |
| Final CTA | Charcoal | "Every book starts with a conversation." |
| Footer | #1A1A1A | Imprint tag + links + copyright |

**About the Writer section — use this exact text:**

> Systovation Publishing was founded by Sukant Ratnakar — a Certified Management Consultant, author of over 30 books, and someone who has been on both sides of the publishing table. As an author published by Hay House and Amaryllis, and as a self-published writer, he's experienced everything from the quality of institutional publishing to the independence of doing it yourself.
>
> Systovation exists to give professionals the best of both: the editorial depth, design quality, and legitimacy of a traditional publisher, with the speed, ownership, and creative control of self-publishing.
>
> *"I built this imprint because I watched too many good books get ruined by bad production — and too many experienced people give up on publishing because the process felt overwhelming."*
>
> Every project is personally overseen. The research methodology that powers our ghostwriting is augmented by advanced tools that allow us to deliver at a speed and depth that traditional publishers can't match. But the craft — the editing, the structure, the design sensibility — is irreducibly human.

**All CTAs link to:** https://cal.com/sukantratnakar/lets-talk

---

### Page 2: BOOKS CATALOG (books.html) — RESTYLE ONLY

Keep ALL existing functionality:
- JSON-driven catalog
- Genre filtering
- Book card grid

**Visual changes:**
- Page background: var(--charcoal) #2C2C2C
- Book cards: background var(--warm-charcoal), border 1px solid var(--copper-tint), border-radius 4px
- Card hover: border-color rgba(184,115,51,0.3)
- Genre filter pills: border 1px solid var(--copper-tint), copper text, active state fills copper
- Page heading: Libre Baskerville, 32px, white, centered
- Subline: Source Sans Pro, 15px, rgba(255,255,255,0.40)
- "Featured" / "New" badges: copper background (#B87333), charcoal text

**Keep:** 3D book mockup styling if it works with real covers. If it looks wrong with actual cover images, switch to flat cover image display with subtle border.

---

### Page 3: BOOK DETAIL (book/{slug}.html) — RESTYLE ONLY

Keep ALL existing functionality:
- Dynamic content from books.json
- Cover display
- Description
- Buy links
- Preview button → leads to flipbook

**Visual changes:**
- Background: var(--parchment) #F7F3EB for main content area
- Book title: Libre Baskerville, 28px, var(--text-dark)
- Author name: Source Sans Pro, 14px, var(--copper), uppercase, letter-spacing 1px
- Description text: Source Sans Pro, 15px, var(--text-body), line-height 1.9
- Buy buttons: copper ghost style (same as CTA button)
- "Preview" button: charcoal background, white text, hover → copper
- Metadata (pages, ISBN, date): Source Sans Pro, 13px, var(--text-light)
- Back to catalog link: Source Sans Pro, 12px, var(--copper), uppercase

---

### Page 4: FLIPBOOK READER (reader/{slug}.html) — RESTYLE ONLY

Keep ALL existing functionality:
- PDF.js rendering
- 10-page preview limit
- Keyboard + touch navigation
- Fullscreen mode
- "Buy Full Book" CTA on last page

**Visual changes:**
- Background: var(--charcoal) #2C2C2C (dark reading environment)
- Navigation controls: copper accent colour for arrows/buttons
- Page counter: Source Sans Pro, 12px, rgba(255,255,255,0.40)
- "Buy Full Book" CTA: copper ghost button
- Close/back button: copper colour

---

### Page 5: AUTHORS GALLERY (authors.html) — RESTYLE ONLY

Keep ALL existing functionality:
- JSON-driven author grid
- Featured authors section
- Author cards with initials fallback

**Visual changes:**
- Background: var(--charcoal)
- Featured author cards: larger, var(--warm-charcoal) background, copper border
- Regular author cards: var(--warm-charcoal), subtle border
- Author name: Libre Baskerville, 16px, white
- Author role/description: Source Sans Pro, 13px, var(--text-on-dark)
- Initials fallback circle: copper background, charcoal text
- Heading: "Our Authors" → Libre Baskerville, 32px, white

---

### Page 6: GET PUBLISHED (get-published.html) — CONTENT + STYLE UPDATE

This page needs BOTH new content and new styling. Merge the services/process content from `systovation_homepage.html` with the existing inquiry form functionality.

**Structure:**

| Section | Content |
|---------|---------|
| Hero | "Your expertise deserves a book that does it justice." |
| Services | Same 5 services as homepage but with expanded descriptions |
| Process | Same 4-step process (Discovery → Architecture → Craft → Publication) |
| Inquiry Form | **KEEP existing form** — name, book title, genre, stage, message |
| Booking | "Or book a conversation directly" → cal.com/sukantratnakar/lets-talk |

**Keep the existing form fields and lead capture functionality.** Just restyle to match new design:
- Form background: var(--parchment)
- Input fields: 1px solid #ddd border, border-radius 3px, Source Sans Pro 14px
- Input focus: border-color var(--copper)
- Submit button: charcoal background, white text, hover → copper
- Labels: Source Sans Pro, 11px, uppercase, letter-spacing 1.5px, var(--text-light)

**Replace the booking link:**
- Old: cal.com/sukant-ratnakar/45min
- New: https://cal.com/sukantratnakar/lets-talk

---

### Page 7: ADMIN PANEL (admin.html) — MINIMAL CHANGES

**CHANGE THE PASSWORD IMMEDIATELY.** Do not use the current password.

Otherwise, keep all functionality. Minor visual tweaks only:
- Primary accent colour from sage green to copper
- Button colours to match copper palette
- Everything else stays functional as-is

---

## NAVIGATION — ALL PAGES

```
[Systovation Publishing]     [Books] [Authors] [Get Published] [Start a Conversation]
```

- **Left:** "Systovation Publishing" — Libre Baskerville, 16px, white. "Publishing" in copper.
- **Right links:** Source Sans Pro, 11px, uppercase, letter-spacing 2px
  - "Books" → /books.html
  - "Authors" → /authors.html
  - "Get Published" → /get-published.html
  - "Start a Conversation" (copper bordered button) → https://cal.com/sukantratnakar/lets-talk
- Fixed position, transparent → charcoal on scroll
- Mobile: hamburger, keep "Start a Conversation" visible

---

## FOOTER — ALL PAGES

```
Systovation Publishing — an imprint of Quantraz Inc.

[Quantraz]  [Sukant Ratnakar]  [Email]

© 2026 Systovation Publishing · Quantraz Inc · Saskatoon, Canada
```

- Background: #1A1A1A
- Imprint line: Libre Baskerville, 12px, italic, rgba(255,255,255,0.15)
- Links: Source Sans Pro, 11px, uppercase, rgba(255,255,255,0.2)
- Copyright: Source Sans Pro, 11px, rgba(255,255,255,0.12)

---

## BOOKING LINK — GLOBAL REPLACEMENT

Search and replace ALL instances of:
```
cal.com/sukant-ratnakar/45min
```
With:
```
https://cal.com/sukantratnakar/lets-talk
```

This appears in:
- get-published.html
- Homepage CTA sections
- Any other pages with booking links

---

## EMAIL — FIX BEFORE LAUNCH

The site shows **publish@systovation.com** but this email is not connected.

**Option A (Recommended):** Connect publish@systovation.com to an actual mailbox or forwarder. This is the professional choice — it matches the domain and feels like a real publishing house.

**Option B (Temporary):** Replace all instances of publish@systovation.com with sukant@quantraz.com until the Systovation email is connected. A working email at a different domain is better than a dead email at the right domain.

**Never show an email address on the site that bounces.**

---

## DATA UPDATES

### books.json
- **"The Flip Effect" author is Sukant Ratnakar** — update from TBD immediately
- Upload real cover images to /assets/covers/ (replace Unsplash placeholders)
- Upload preview PDFs to /assets/pdfs/
- **CRITICAL: The current catalog has only 10 books. Systovation has published 50+ projects with 15+ authors. The catalog must be expanded to reflect the full body of work.** Add all published titles to books.json. This is the single most important credibility signal on the site — a publishing house with 50+ titles is taken seriously. A publishing house with 10 titles looks like it started last month.

### authors.json
- Currently shows 11 authors. **Expand to include ALL authors you've published** — all 15+
- Upload real headshots to /assets/authors/
- Verify all author bios are accurate and approved by the authors
- Add a short testimonial/quote from each author if available (even one sentence: "Working with Systovation was..." — this provides the emotional warmth the site currently lacks)

### Email
- **Use sukant@quantraz.com everywhere.** Replace ALL instances of publish@systovation.com with sukant@quantraz.com. One working email is better than a professional-looking email that bounces.

---

## FLIPBOOK STRATEGY — USE ANYFLIP

**Do NOT use OpenClaw's PDF.js flipbook for the reading experience.** Sukant has an existing AnyFlip subscription which provides superior page-turn animations, analytics, and mobile experience.

**Integration approach — keep the gate, change the destination:**

1. **KEEP** OpenClaw's lead capture modal (name, email, phone form that appears when user clicks "Preview")
2. **On form submit:** store the lead data (existing localStorage system)
3. **REDIRECT** to the AnyFlip preview URL for that book (instead of opening the local PDF.js reader)

**Implementation:**
- In books.json, add a new field for each book: `"anyflipUrl": "https://anyflip.com/xxxxx"`
- The "Preview" button triggers the lead capture form
- On successful capture, `window.open(book.anyflipUrl, '_blank')` instead of `/reader/{slug}.html`
- The /reader/ pages can remain as fallback but are no longer the primary preview experience

**Remove or repurpose:**
- /reader/{slug}.html pages → either delete or keep as fallback for books without AnyFlip links
- PDF.js CDN → can be removed if all previews go through AnyFlip

---

## MAKING THE SITE FEEL ALIVE — EMOTIONAL WARMTH PLAN

The current site is structurally sound but emotionally empty. A publishing house website needs to feel like it's full of stories, not empty shelves. Here's what creates warmth:

### 1. Real Book Covers Everywhere
- Homepage hero: show 3-5 actual book covers in an angled arrangement (not a carousel, not a slider — a static, editorial-quality composition)
- Books page: real covers in the grid (this is already planned — just needs actual images uploaded)
- Book detail pages: real cover, large, with shadow that makes it feel physical
- **Sukant has covers ready to upload — this is the single fastest improvement**

### 2. Author Quotes / Testimonials
Add to authors.json a `"quote"` field for each author. Even one sentence creates human warmth:
```json
{
  "name": "Chris Black",
  "quote": "Systovation took my rough manuscript and turned it into something I'm genuinely proud of."
}
```

Display these on:
- Homepage — a "What our authors say" section between the philosophy and services sections
- Authors gallery — below each author's photo/bio
- Individual book pages — author's quote about the writing/publishing experience

**If you don't have formal testimonials yet:** reach out to 3-5 of your published authors this week and ask for one sentence about their experience. Most will respond within 24 hours. This is high-value, low-effort content.

### 3. "The Catalogue" Section on Homepage
Between the services section and the process section, add a section that communicates scale:

**Section: "The Catalogue"**
- Background: var(--parchment)
- Layout: scrollable horizontal row of book covers (8-12 covers visible)
- Above the row: "50+ titles published across management, innovation, leadership, mindfulness, and personal development."
- Below the row: "Browse the full catalogue →" link to /books.html
- The visual impact of seeing 8-12 real book covers in a row transforms the site from "small operation" to "real publishing house"

### 4. Author Photo Grid on Homepage
In the "Who We Publish" section or as a standalone section:
- Display a grid of small circular author headshots (all 15+)
- No names, no bios — just faces
- Below: "15+ authors published. From first-time writers to established thought leaders."
- The visual effect: "real people trust this publisher with their work"

### 5. Numbers That Matter
Add a subtle stats bar somewhere on the homepage (charcoal background, copper numbers):
```
50+          15+           5
Books        Authors       Years
Published    Published     Publishing
```
- Numbers in Libre Baskerville, 36px, copper
- Labels in Source Sans Pro, 11px, uppercase, muted text
- This is factual social proof that costs nothing to create

### 6. A Real "Featured Book" on the Homepage
Instead of (or in addition to) the generic hero, show ONE featured book prominently:
- Large cover image (left)
- Title, author, one-paragraph description, "Read preview" button (right)
- This rotates monthly or stays on your newest/most impressive title
- It immediately tells the visitor: "This is a real publishing house with real books"

---

## WHAT NOT TO CHANGE

- **JSON data structure** — keep books.json and authors.json schema as-is
- **Page generation scripts** — scripts/generate-pages.js stays unchanged
- **Flipbook functionality** — PDF.js integration stays unchanged
- **Lead capture system** — localStorage + admin panel stays unchanged
- **Admin panel functionality** — only change password and accent colours
- **Cloudflare Pages deployment** — hosting stays unchanged
- **File structure** — keep the existing directory structure

---

## WHAT NOT TO ADD

- No scroll animations or parallax
- No stock photos (use real covers and headshots only)
- No AI-generated images
- No testimonials unless you have real, attributed quotes from authors
- No pricing on any public page
- No "packages" or "tiers" displayed publicly — pricing is discussed in conversation
- No social media feeds or widgets
- No cookie banners unless legally required

---

## LAUNCH CHECKLIST

### Before DNS switch (systovation.pages.dev → systovation.com):

**Security:**
- [ ] Admin password changed to something strong and unrelated to family names

**Content — Books:**
- [ ] "The Flip Effect" author updated to Sukant Ratnakar in books.json
- [ ] All 50+ published titles added to books.json (not just 10)
- [ ] All book covers uploaded (real images, not Unsplash placeholders)
- [ ] AnyFlip URLs added to books.json for each book with a preview
- [ ] Featured book selected for homepage display

**Content — Authors:**
- [ ] All 15+ authors added to authors.json
- [ ] Author headshots uploaded (at least for featured authors)
- [ ] Author quotes/testimonials collected (at least 3-5)
- [ ] All author bios verified and approved

**Content — Text:**
- [ ] Homepage content replaced with new version from systovation_homepage.html
- [ ] "About the Writer" uses correct publishers (Hay House, Amaryllis)
- [ ] Stats bar added (50+ Books | 15+ Authors | 5 Years Publishing)
- [ ] Catalogue scroll section shows real covers

**Design:**
- [ ] All pages restyled to charcoal + copper palette
- [ ] Fonts changed to Libre Baskerville + Source Sans Pro
- [ ] Border-radius changed from 12px to 4px throughout
- [ ] Mobile responsive tested on iPhone and Android

**Integrations:**
- [ ] All booking links updated to cal.com/sukantratnakar/lets-talk
- [ ] All email addresses show sukant@quantraz.com (not publish@systovation.com)
- [ ] AnyFlip integration working (lead capture → redirect to AnyFlip)
- [ ] Lead capture form tested (submit → check admin panel → redirect works)

**Links:**
- [ ] Footer links working (Quantraz, Sukant Ratnakar, Email)
- [ ] Buy links working for all books (Amazon, IngramSpark)
- [ ] "Browse the full catalogue" link working

### DNS switch:
- Add custom domain in Cloudflare Pages dashboard
- CNAME: systovation.com → systovation.pages.dev
- CNAME: www.systovation.com → systovation.pages.dev
- Verify SSL certificate issued

### After launch:
- [ ] Test all pages on live domain
- [ ] Test booking link from live domain
- [ ] Test email links from live domain
- [ ] Submit sitemap to Google Search Console
- [ ] Add Open Graph meta tags for social sharing
- [ ] Verify AnyFlip previews work from live domain
