# ExpenseFlow Hospitality Design System

## Product Principle

ExpenseFlow Hall Management should feel like **Luxury Venue Operations**, not generic booking management.

The product is organized around operational moments:

- What is happening today
- What needs attention
- What is busy or at risk
- What the kitchen must prepare
- Who needs payment or customer follow-up

Financials are important, but secondary to live operations.

## Information Hierarchy

1. **Today**
   The dashboard leads with today’s event flow, kitchen prep, arrivals, and payment follow-ups.

2. **Attention Required**
   Risks and follow-ups must be explicit: balances due, heavy kitchen load, near-full occupancy.

3. **Occupancy**
   Calendar intelligence is embedded into dashboards as bars, timelines, and next-event previews.

4. **Kitchen Load**
   Breakfast, lunch, dinner, and guest covers are first-class hospitality signals.

5. **Financial Context**
   Revenue and balances are quiet supporting details, not the dominant product story.

## Visual Language

- Palette: warm off-white, charcoal, muted gray, soft borders
- Accents: muted emerald, muted gold, soft slate, restrained danger
- Avoid: rainbow cards, Bootstrap blue dominance, saturated status blocks
- Radius: 10px for controls, 14px for cards, 20px+ for command surfaces
- Elevation: subtle by default, slightly lifted on hover
- Typography: uppercase micro-labels, large calm titles, tabular financial values

## Interaction Language

- Hover states are soft and useful, not decorative.
- Mobile prioritizes actions: today’s events, quick booking, payment follow-up, call/WhatsApp.
- Dashboards should feel alive through timelines, event strips, and operational alerts.

## Component Intent

- `ef-today-command`: main operational cockpit
- `ef-moment`: timeline item for prep, arrival, payment, or service moment
- `ef-attention-item`: actionable operational risk
- `ef-occupancy-bars`: embedded calendar intelligence
- `ef-kitchen-grid`: catering load snapshot
- `ef-event-card`: event-first booking preview
