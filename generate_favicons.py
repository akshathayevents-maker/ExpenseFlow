#!/usr/bin/env python3
"""
ExpenseFlow favicon generator.
Design: Dark navy rounded square, bold "ef" monogram, green accent glow.
"""
from PIL import Image, ImageDraw, ImageFont
import os
import struct
import zlib

PUBLIC = os.path.join(os.path.dirname(__file__), 'public')

NAVY   = (11,  18,  32,  255)   # #0b1220
GREEN  = (16, 185, 129)         # #10b981
WHITE  = (255, 255, 255, 255)

FONT_BOLD = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'


def rounded_rect_mask(size, radius):
    mask = Image.new('L', (size, size), 0)
    d = ImageDraw.Draw(mask)
    d.rounded_rectangle([0, 0, size - 1, size - 1], radius=radius, fill=255)
    return mask


def add_glow(img, size, glow_color, strength=0.22):
    """Radial green glow from top-left corner."""
    overlay = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    d = ImageDraw.Draw(overlay)
    r, g, b = glow_color
    steps = 18
    for i in range(steps, 0, -1):
        alpha = int(strength * 255 * (1 - (i - 1) / steps) ** 1.5)
        rr = int(size * 0.9 * i / steps)
        d.ellipse(
            [-rr // 2, -rr // 2, rr // 2, rr // 2],
            fill=(r, g, b, alpha)
        )
    return Image.alpha_composite(img, overlay)


def draw_ef_bars(draw, size):
    """
    Draw a geometric 'E' made from three clean bars.
    Works perfectly at all sizes — no font rendering artifacts.
    Top bar = green accent. Middle + bottom = white.
    """
    pad   = int(size * 0.18)
    bar_h = max(2, int(size * 0.13))
    gap   = max(1, int(size * 0.10))
    full_w = size - pad * 2
    mid_w  = int(full_w * 0.72)

    total_h = bar_h * 3 + gap * 2
    y0 = (size - total_h) // 2

    # Top bar — green
    draw.rectangle([pad, y0, pad + full_w, y0 + bar_h], fill=(*GREEN, 255))
    # Middle bar — white, shorter (E notch)
    y1 = y0 + bar_h + gap
    draw.rectangle([pad, y1, pad + mid_w, y1 + bar_h], fill=WHITE)
    # Bottom bar — white, full width
    y2 = y1 + bar_h + gap
    draw.rectangle([pad, y2, pad + full_w, y2 + bar_h], fill=WHITE)


def make_icon(size):
    radius = max(3, int(size * 0.20))
    img    = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    bg     = Image.new('RGBA', (size, size), NAVY)
    mask   = rounded_rect_mask(size, radius)
    img.paste(bg, mask=mask)
    img    = add_glow(img, size, GREEN, strength=0.24)
    draw   = ImageDraw.Draw(img)
    draw_ef_bars(draw, size)
    return img


def save_png(img, path, size=None):
    if size:
        img = img.resize((size, size), Image.LANCZOS)
    img.save(path, 'PNG')
    print(f'  ✓  {os.path.basename(path)}')


def save_ico(path):
    sizes = [16, 32, 48]
    images = [make_icon(s) for s in sizes]
    images[0].save(
        path, format='ICO',
        sizes=[(s, s) for s in sizes],
        append_images=images[1:]
    )
    print(f'  ✓  {os.path.basename(path)}')


def main():
    print('\nExpenseFlow — generating favicon suite...\n')
    base = make_icon(512)

    save_png(base,       os.path.join(PUBLIC, 'favicon-16x16.png'),       16)
    save_png(base,       os.path.join(PUBLIC, 'favicon-32x32.png'),       32)
    save_png(base,       os.path.join(PUBLIC, 'apple-touch-icon.png'),   180)
    save_png(base,       os.path.join(PUBLIC, 'android-chrome-192x192.png'), 192)
    save_png(base,       os.path.join(PUBLIC, 'android-chrome-512x512.png'), 512)
    save_ico(            os.path.join(PUBLIC, 'favicon.ico'))

    # SVG favicon — pure vector, browser-native, no rasterisation
    svg = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
  <defs>
    <radialGradient id="g" cx="0%" cy="0%" r="90%">
      <stop offset="0%"   stop-color="#10b981" stop-opacity="0.35"/>
      <stop offset="100%" stop-color="#0b1220" stop-opacity="0"/>
    </radialGradient>
    <clipPath id="c">
      <rect width="100" height="100" rx="22" ry="22"/>
    </clipPath>
  </defs>
  <rect width="100" height="100" rx="22" ry="22" fill="#0b1220"/>
  <rect width="100" height="100" rx="22" ry="22" fill="url(#g)"/>
  <g clip-path="url(#c)">
    <!-- Top bar: green -->
    <rect x="18" y="28" width="64" height="13" rx="2" fill="#10b981"/>
    <!-- Middle bar: white, shorter -->
    <rect x="18" y="44" width="46" height="13" rx="2" fill="#ffffff"/>
    <!-- Bottom bar: white, full -->
    <rect x="18" y="60" width="64" height="13" rx="2" fill="#ffffff"/>
  </g>
</svg>'''
    svg_path = os.path.join(PUBLIC, 'favicon.svg')
    with open(svg_path, 'w') as f:
        f.write(svg)
    print(f'  ✓  favicon.svg')

    # site.webmanifest
    manifest = '''{
  "name": "ExpenseFlow",
  "short_name": "ExpenseFlow",
  "description": "Expense & payment request management",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#0b1220",
  "theme_color": "#10b981",
  "icons": [
    {
      "src": "/android-chrome-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/android-chrome-512x512.png",
      "sizes": "512x512",
      "type": "image/png"
    },
    {
      "src": "/favicon.svg",
      "sizes": "any",
      "type": "image/svg+xml",
      "purpose": "any maskable"
    }
  ]
}'''
    with open(os.path.join(PUBLIC, 'site.webmanifest'), 'w') as f:
        f.write(manifest)
    print(f'  ✓  site.webmanifest')

    print('\nDone.\n')


if __name__ == '__main__':
    main()
