---
title: 'LaTeX Math Plugin'
---

The LaTeX Math plugin draws math written as LaTeX right in the page, with KaTeX or MathJax. Math matches the site's light or dark mode, so try the light/dark switch, and it prints cleanly.

This page is also a test page: copy it into a site's `user/pages` (for example as `99.latex-math-demo/default.md`) and check it against the list at the end. Edge cases are in `latex-math-test.md`, and a page's own settings in `latex-math-page-setting-test.md`.

## Inline Math

Math within a sentence goes between single dollar signs: the area of a circle is $A = \pi r^2$, and a line through the origin is $y = mx$.

## A Worked Example

A block goes between double dollar signs, each on its own line. The average of $n$ values is their sum divided by $n$:

$$
\bar{x} = \frac{1}{n} \sum_{i=1}^{n} x_i
$$

For the values 2, 4 and 9, that's $\bar{x} = \frac{2 + 4 + 9}{3} = 5$.

The math is written as text, so anyone reusing the page can revise it:

~~~markdown
The average of $n$ values is their sum divided by $n$:

$$
\bar{x} = \frac{1}{n} \sum_{i=1}^{n} x_i
$$
~~~

## Steps Lined Up

Each `&` lines the steps up at the equals signs, and `\\` starts a new line:

$$
\begin{aligned}
(a + b)^2 &= (a + b)(a + b) \\
          &= a^2 + ab + ba + b^2 \\
          &= a^2 + 2ab + b^2
\end{aligned}
$$

## The Quadratic Formula

The solutions of $ax^2 + bx + c = 0$ are:

$$
x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}
$$

## A Matrix

$$
A = \begin{pmatrix} 1 & 2 \\ 3 & 4 \end{pmatrix}, \quad
A^{-1} = \frac{1}{-2} \begin{pmatrix} 4 & -2 \\ -3 & 1 \end{pmatrix}
$$

## A Code Block, as on GitHub

A block can also go in a code block marked `math`, as GitHub does:

```math
e^{i\pi} + 1 = 0
```

## MathJax-Style Markers

Math written for MathJax works too: \(c^2 = a^2 + b^2\) within a sentence, and a block with `\[` and `\]` each on its own line:

\[
\int_0^1 x^2 \, dx = \frac{1}{3}
\]

## Dollar Signs as Text

Prices such as $5 and $10 stay as text, and `\$` shows a dollar sign: a book costs \$25.

## A Formula with a Mistake

A formula with a mistake shows an error in red, and the rest of the page still works: $\frac{1}{$

## Testing Checklist

When checking this page after an update, each item should hold:

- Every formula above draws, except the one with a mistake, which shows an error in red
- No LaTeX shows while the page loads – just a blank space until each formula appears
- Switching light and dark mode changes the math's colour to match the text
- Printing from dark mode gives dark math on a light page
- Copying a formula, then pasting it as plain text, gives its LaTeX between dollar signs (KaTeX; with MathJax, right-click a formula instead)
- In Dollar Signs as Text, the prices and the \$25 show as typed
- The source example in A Worked Example stays as code
