---
title: 'LaTeX Math Page Setting Test'
latex-math:
    engine: mathjax
    inline_dollars: false
---

Tests a page's own settings in its front matter: MathJax as the engine, and $…$ math turned off, with the site's settings left as they are (**Math Engine** on KaTeX, and **Inline $…$ Math** on). Copy this page into a site's `user/pages` (for example as `99.latex-math-page-setting-test/default.md`). For the other edge cases, see `latex-math-test.md`.

Expect: plain text, as this page turns off $…$ math: tickets cost $5 to $10, and $x$ stays as typed.

Expect: drawn math, as MathJax-style markers and blocks always work: \(x^2\), and

$$
a^2 + b^2 = c^2
$$

Expect: the math is drawn by MathJax – right-clicking a formula opens MathJax's menu.
