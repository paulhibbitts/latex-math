---
title: 'LaTeX Math Tests'
---

Edge cases for the LaTeX Math plugin, each with what to expect. Copy this page into a site's `user/pages` (for example as `99.latex-math-test/default.md`), and check it with each engine, in light and dark mode. For the main features, see `latex-math-demo.md`.

## Markdown Characters in Math

Expect: subscripts and a product, not italics or bold: $x_1 + x_2 = y_{a*b}$ and $a_i b_i$.

## Blocks

Expect: three blocks – on separate lines, on one line, and within a sentence.

$$
\int_0^1 x^2 \, dx = \frac{1}{3}
$$

$$ e^{i\pi} + 1 = 0 $$

Within a sentence: $$ \sum_{n=1}^{\infty} \frac{1}{n^2} = \frac{\pi^2}{6} $$ and the sentence goes on.

## MathJax-Style Markers

Expect: inline math \(x_1 + x_2\), then a block, with `\[` and `\]` each on its own line.

\[
\int_0^1 x^3 \, dx = \frac{1}{4}
\]

## A Blank Line Inside a Block

Expect: one block – the blank line doesn't end it.

$$
a = b

c = d
$$

## A Code Block

Expect: a drawn block, not code.

```math
\sqrt{a^2 + b^2}
```

## Dollar Signs as Text

Expect: plain text – prices like $5 and $10, a cost of \$20, and a space after a dollar sign, $ like this.

## Special Characters

Expect: a less-than and a greater-than: $x < y$ and $y > z$, and an ampersand in a matrix:

$$
\begin{pmatrix} a & b \\ c & d \end{pmatrix}
$$

## In a List and a Table

- Expect: math in a list item: $\alpha + \beta$
- And another: $\sqrt{2}$

| Name | Formula |
|------|---------|
| Expect: math in a table | $E = mc^2$ |

## Extra Packages

Expect: each draws with either engine – a crossed-out x, a red y, a boxed z, water as a chemical formula, and an equation numbered (1).

$\cancel{x}$, $\color{red}{y}$, $\boxed{z}$ and $\ce{H2O}$

$$
E = mc^2 \tag{1}
$$

## A Formula with a Mistake

Expect: the mistake shows as an error, and the formula after it still draws: $\frac{1}{$ then $a^2$.

## Not Drawn

Expect: square brackets written as `\[` and `\]` – as tools that turn web pages into Markdown write them – show as plain brackets: see \[1\], and the line below.

\[ A note on its own line \]

Expect: all three stay as code.

`$inline code$`

```
$$
\text{a plain code block}
$$
```

~~~markdown
```math
\text{a math block shown as source}
```
~~~
