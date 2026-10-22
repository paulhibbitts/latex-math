// Developed with the assistance of Claude Code (claude.ai)

/*
 * LaTeX Math plugin – draws each formula on the page with KaTeX or MathJax (window.GravMath.engine, set by latex-math.php).
 * Formulas are <span class="math-inline"> and <div class="math-block"> boxes from latex-math.php.
 */

// Wrapped in a function that runs straight away, so its names can't clash with the theme's scripts
(function () {
  var settings = window.GravMath;

  // Each formula: its box, its LaTeX, and whether it's drawn as a block
  var formulas = [];

  /**
   * Finds every formula box.
   */
  function findFormulas() {
    var boxes = document.querySelectorAll('.math-inline, .math-block');
    for (var i = 0; i < boxes.length; i++) {
      formulas.push({
        box: boxes[i],
        // textContent turns &lt; back into <, giving the LaTeX as written
        latex: boxes[i].textContent,
        display: boxes[i].classList.contains('math-block')
      });
    }
  }

  /**
   * Draws every formula with KaTeX. A formula with a mistake shows in red, and the rest still draw.
   */
  function drawWithKatex() {
    for (var i = 0; i < formulas.length; i++) {
      var formula = formulas[i];
      try {
        katex.render(formula.latex, formula.box, {
          displayMode: formula.display,
          throwOnError: false,
          // also adds hidden MathML, which screen readers read
          output: 'htmlAndMathml'
        });
        formula.box.setAttribute('data-processed', 'true');
      } catch (error) {
        console.error('LaTeX Math plugin:', error);
        showLatex(formula);
      }
    }
  }

  /**
   * Draws every formula with MathJax, one after another, then adds MathJax's styles for them.
   */
  function drawWithMathJax() {
    // starts when MathJax is ready; each formula then waits for the one before
    var drawing = MathJax.startup.promise;

    formulas.forEach(function (formula) {
      drawing = drawing
        .then(function () {
          return MathJax.tex2chtmlPromise(formula.latex, { display: formula.display });
        })
        .then(function (drawn) {
          formula.box.textContent = '';
          formula.box.appendChild(drawn);
          formula.box.setAttribute('data-processed', 'true');
        })
        .catch(function (error) {
          console.error('LaTeX Math plugin:', error);
          showLatex(formula);
        });
    });

    drawing.then(function () {
      MathJax.startup.document.clear();
      MathJax.startup.document.updateDocument();
    });
  }

  /**
   * Shows a formula's LaTeX as text, when it can't be drawn.
   */
  function showLatex(formula) {
    formula.box.textContent = formula.latex;
    formula.box.setAttribute('data-processed', 'true');
  }

  function start() {
    if (!settings) return;

    findFormulas();
    if (formulas.length === 0) return;

    if (settings.engine === 'mathjax' && window.MathJax && MathJax.startup) {
      drawWithMathJax();
    } else if (settings.engine === 'katex' && window.katex) {
      drawWithKatex();
    } else {
      // the engine didn't load: show the LaTeX rather than leave blank spaces
      formulas.forEach(showLatex);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
