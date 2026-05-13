"""Inject standing book-promo block before <footer> in blog posts (idempotent)."""
import pathlib

ROOT = pathlib.Path(__file__).resolve().parents[2]
POSTS = ROOT / "blog" / "posts"

PROMO = """
    <aside class="book-promo">
        <div class="book-promo-inner">
            <a href="../../rolling-the-dice.html" class="book-promo-cover">
                <img src="../../static/images/rollingff.webp" alt="Rolling the Dice with Nuclear Weapons by John Ward" loading="lazy">
            </a>
            <div class="book-promo-text">
                <span class="section-label">From the Author</span>
                <h3>Rolling the Dice with Nuclear Weapons</h3>
                <p>The full thesis — atmospheric science, archival case studies, and a policy blueprint for stability — in book form by John Ward.</p>
                <a href="../../rolling-the-dice.html" class="book-promo-cta">Read more about the book &rarr;</a>
            </div>
        </div>
    </aside>
"""

for p in POSTS.glob("*.html"):
    t = p.read_text(encoding="utf-8")
    if 'class="book-promo"' in t:
        continue
    needle = "    </main>\n\n    <footer class=\"site-footer\">"
    if needle not in t:
        print("SKIP pattern", p.name)
        continue
    t = t.replace(needle, "    </main>\n" + PROMO + "\n    <footer class=\"site-footer\">", 1)
    p.write_text(t, encoding="utf-8")
    print("injected", p.name)
