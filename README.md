# LaTeX Math Plugin

**LaTeX Math** is a [Grav](https://getgrav.org) plugin that draws LaTeX math in a page, with your choice of [KaTeX](https://katex.org) or [MathJax](https://www.mathjax.org). Math matches the theme's light or dark mode and prints cleanly. Works with Grav 1.7 and Grav 2.

## Why Math as Text?

Math written as text suits open educational resources (OER) and open publishing:

- **Easy to revise and remix** – anyone reusing your content can edit a formula's text, with no image or equation editor needed
- **Covered by the page's licence** – a formula is part of the text, so there's no separate image to credit or track
- **Accessible** – screen readers read the math, and it stays sharp at any zoom
- **Portable** – the same math shows on GitHub, in Docsify-This and in many Markdown editors, and stays readable as plain text anywhere else

## Installation

Download the plugin, unzip it into `user/plugins`, and rename the folder to `latex-math`.

## Adding Math

Write inline math between single dollar signs, and a block between double dollar signs on their own lines:

```markdown
The area of a circle is $A = \pi r^2$.

$$
\int_0^1 x^2 \, dx = \frac{1}{3}
$$
```

Which draws:

The area of a circle is $A = \pi r^2$.

$$
\int_0^1 x^2 \, dx = \frac{1}{3}
$$

A block can also go in a code block marked `math`, as on GitHub, and MathJax's `\( … \)` works too, as does a block between `\[` and `\]`, each on its own line (only then, as Markdown also uses `\[` for a plain bracket). A formula with a mistake shows an error in red, and the rest still draw. Copying a formula copies its LaTeX, ready to paste into another page. Chemistry works too, such as `\ce{H2O}`.

See KaTeX's [supported functions](https://katex.org/docs/supported.html) or MathJax's [TeX commands](https://docs.mathjax.org/en/latest/input/tex/macros/index.html) for what each engine draws. Moving from another math plugin, such as StaticMath? Turn it off once this one is installed, as both read `$`.

> [!TIP]
> Prices such as $5 and $10 stay as text, and `\$` shows a dollar sign. For a page with many prices, add `latex-math: { inline_dollars: false }` to its front matter.

## Settings

- **Math Engine** – KaTeX (the default) is faster; MathJax covers more of LaTeX. A page's front matter can choose its own, with `latex-math: { engine: mathjax }`
- **Inline $…$ Math** – on by default, and a page's front matter can turn it off; `$$`, `\( … \)`, `\[` … `\]` and ```` ```math ```` always work

The engine is only added to pages with math.

## Credits

Includes [KaTeX](https://github.com/KaTeX/KaTeX) 0.19.0 by Khan Academy and contributors (`katex.min.js`, `katex.min.css` and its `.woff2` fonts, and `contrib/copy-tex.min.js`, MIT licence, plus `contrib/mhchem.min.js` with Martin Hensel's mhchem, Apache 2.0 licence), and [MathJax](https://github.com/mathjax/MathJax) 4.1.3 by the MathJax Consortium (`tex-chtml.js`, `a11y/assistive-mml.js` and the `input/tex/extensions` folder, Apache 2.0 licence) with its [New Computer Modern](https://www.npmjs.com/package/@mathjax/mathjax-newcm-font) font files (`chtml.js` and the `chtml` folder from `@mathjax/mathjax-newcm-font`, Apache 2.0 licence). The KaTeX and MathJax licences are in their folders in `js/`.

`examples/latex-math-demo.md` is the page behind the [online demo](https://demo.hibbittsdesign.org/grav-plugin-latex-math/), with a testing checklist, `examples/latex-math-test.md` covers edge cases, and `examples/latex-math-page-setting-test.md` checks a page's own settings – together, they check math after an update.
