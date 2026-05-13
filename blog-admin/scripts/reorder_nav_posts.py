"""One-off: reorder Thesis / Book / Evidence / Strategy in blog/posts/*.html"""
import pathlib

ROOT = pathlib.Path(__file__).resolve().parents[2]
POSTS = ROOT / "blog" / "posts"

old = """                <li><a href="../../index.html">Thesis</a></li>
                <li><a href="../../evidence.html">Evidence</a></li>
                <li><a href="../../strategy.html">Strategy</a></li>
                <li><a href="../../rolling-the-dice.html">The Book</a></li>"""

new = """                <li><a href="../../index.html">Thesis</a></li>
                <li><a href="../../rolling-the-dice.html">The Book</a></li>
                <li><a href="../../evidence.html">Evidence</a></li>
                <li><a href="../../strategy.html">Strategy</a></li>"""

for p in POSTS.glob("*.html"):
    t = p.read_text(encoding="utf-8")
    if old in t:
        p.write_text(t.replace(old, new), encoding="utf-8")
        print("updated", p.name)
