<?php

// Developed with the assistance of Claude Code (claude.ai)

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Utils;
use RocketTheme\Toolbox\Event\Event;

/**
 * LaTeX Math – draws LaTeX math with KaTeX or MathJax, written as $…$, $$ … $$ or in a ```math block, as on GitHub,
 * or as \( … \) and \[ … \], as with MathJax.
 *
 *   1. onMarkdownInitialized – sets the math aside before Markdown can change it (such as _ into italics)
 *   2. onOutputGenerated     – on pages with math, adds the chosen engine and js/latex-math-init.js, which draws it
 *
 * Math uses the page's text colour, so it follows light and dark mode by itself.
 *
 * Works with Grav 1.7 and Grav 2 - hibbittsdesign.org
 */
class LatexMathPlugin extends Plugin
{
    // The engine versions in js/, added to their addresses so browsers fetch new copies after an update.
    // To update an engine:
    //   1. Replace its files with the same ones from https://cdn.jsdelivr.net/npm/<package>@<version>/ (the README's
    //      Credits lists them)
    //   2. Change its version here and in the README's Credits, and add a CHANGELOG entry
    //   3. Check the pages in examples/ with that engine, in light and dark mode
    const KATEX_VERSION = '0.19.0';
    const MATHJAX_VERSION = '4.1.3';

    /**
     * The Grav events this plugin listens to, and the method each one calls.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    /**
     * Turns on the plugin's other events, except in Admin, which has no math to draw.
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            return;
        }

        $this->enable([
            'onMarkdownInitialized' => ['onMarkdownInitialized', 0],
            'onOutputGenerated'     => ['onOutputGenerated', 0],
        ]);
    }

    /**
     * Teaches Grav's Markdown to set math aside, as text in a marked box that js/latex-math-init.js finds.
     */
    public function onMarkdownInitialized(Event $event)
    {
        $markdown = $event['markdown'];

        // A block: $$ on its own line, the math, then $$ (or all of it on one line). Or MathJax's \[ and \], each on
        // its own line – only then, as Markdown also writes [ as \[ (such as \[1\] for a reference).
        // Markdown calls blockMathBlock to start a block, then blockMathBlockContinue with each next line (null ends
        // the block), then blockMathBlockComplete. The two trues turn on those last two steps.
        $markdown->addBlockType('$', 'MathBlock', true, true);
        $markdown->addBlockType('\\', 'MathBlock', true, true);

        $markdown->blockMathBlock = function ($line) {
            $text = trim($line['text']);

            if (preg_match('/^\$\$(.+)\$\$$/', $text, $match)) {
                return ['complete' => true, 'element' => $this->mathBox('div', 'math-block', trim($match[1]))];
            }
            if ($text === '$$') {
                return ['closing' => '$$', 'element' => $this->mathBox('div', 'math-block', '')];
            }
            if ($text === '\\[') {
                return ['closing' => '\\]', 'element' => $this->mathBox('div', 'math-block', '')];
            }
            return null;
        };

        $markdown->blockMathBlockContinue = function ($line, $block) {
            if (isset($block['complete'])) {
                return null;
            }

            // a blank line inside the math stays part of it
            if (isset($block['interrupted'])) {
                $block['element']['text'] .= "\n";
                unset($block['interrupted']);
            }

            if (trim($line['text']) === $block['closing']) {
                $block['element']['text'] = trim($block['element']['text']);
                $block['complete'] = true;
                return $block;
            }

            $block['element']['text'] .= "\n" . $line['body'];
            return $block;
        };

        $markdown->blockMathBlockComplete = function ($block) {
            return $block;
        };

        // A ```math (or ~~~math) block, as on GitHub. Made here, as code highlighters such as Codesh turn code
        // blocks in languages they don't know into plain text. Index 0 puts it before Grav's own code blocks.
        $markdown->addBlockType('`', 'MathFence', true, true, 0);
        $markdown->addBlockType('~', 'MathFence', true, true, 0);

        $markdown->blockMathFence = function ($line) {
            if (preg_match('/^(`{3,}|~{3,})\s*math\s*$/', $line['text'], $match)) {
                return ['fence' => $match[1], 'element' => $this->mathBox('div', 'math-block', '')];
            }
            return null;
        };

        $markdown->blockMathFenceContinue = function ($line, $block) {
            if (isset($block['complete'])) {
                return null;
            }

            if (isset($block['interrupted'])) {
                $block['element']['text'] .= "\n";
                unset($block['interrupted']);
            }

            // the same fence as the opening one ends the block
            if (rtrim($line['text']) === $block['fence']) {
                $block['complete'] = true;
                return $block;
            }

            $block['element']['text'] .= "\n" . $line['body'];
            return $block;
        };

        $markdown->blockMathFenceComplete = function ($block) {
            $block['element']['text'] = trim($block['element']['text']);
            return $block;
        };

        // Within a sentence: \$ shows a plain dollar sign, and MathJax's \( … \) is math. Index 0 puts this before
        // Markdown's own \ handling, which would turn \( into (. Each result gives the characters used ('extent') and
        // what to show. In the patterns, \\\\ matches a single \ in the page.
        $markdown->addInlineType('\\', 'MathBackslash', 0);
        $markdown->inlineMathBackslash = function ($excerpt) {
            if (isset($excerpt['text'][1]) && $excerpt['text'][1] === '$') {
                return ['markup' => '$', 'extent' => 2];
            }
            if (preg_match('/^\\\\\((.+?)\\\\\)/s', $excerpt['text'], $match)) {
                return ['extent' => strlen($match[0]), 'element' => $this->mathBox('span', 'math-inline', trim($match[1]))];
            }
            return null;
        };

        // The site's setting, unless the page's front matter has its own (latex-math: inline_dollars: false)
        $settings = $event['page'] ? $this->mergeConfig($event['page']) : $this->config->get('plugins.latex-math');
        if (!($settings['inline_dollars'] ?? true)) {
            return;
        }

        // Within a sentence: $$…$$ (drawn as a block), or $…$ with no space just inside either $ and no digit
        // straight after the closing $ (so prices such as $5 and $10 stay as text, while $x^2$ is math)
        $markdown->addInlineType('$', 'MathInline');
        $markdown->inlineMathInline = function ($excerpt) {
            if (preg_match('/^\$\$(.+?)\$\$/', $excerpt['text'], $match)) {
                return ['extent' => strlen($match[0]), 'element' => $this->mathBox('span', 'math-block', trim($match[1]))];
            }
            if (preg_match('/^\$(?![\s$])((?:\\\\.|[^$\\\\])+?)(?<!\s)\$(?!\d)/', $excerpt['text'], $match)) {
                return ['extent' => strlen($match[0]), 'element' => $this->mathBox('span', 'math-inline', $match[1])];
            }
            return null;
        };
    }

    /**
     * A box holding math as text (Markdown escapes it, so < and & are safe).
     */
    protected function mathBox($tag, $class, $latex)
    {
        return ['name' => $tag, 'attributes' => ['class' => $class], 'text' => $latex];
    }

    /**
     * Adds the math engine to the finished page, but only when the page has math. This checks the whole page,
     * so math shown from other pages (such as in a full text or print view) is drawn too.
     */
    public function onOutputGenerated()
    {
        $output = $this->grav->output;

        if (!is_string($output) || !$this->hasMath($output)) {
            return;
        }

        // Only web pages (not RSS feeds or JSON), with a </body> to add the scripts before
        $page = $this->grav['page'];
        if (($page && $page->templateFormat() !== 'html') || stripos($output, '</body>') === false) {
            return;
        }

        // Insert (a length of 0 replaces nothing) before the last </body> only, in case the page shows HTML with
        // its own </body>
        $body_end = strripos($output, '</body>');
        $output = substr_replace($output, $this->pageAdditions(), $body_end, 0);

        // Styles go before the first </head> (the page's own), as styles at the end of the page arrive too late
        $head_end = stripos($output, '</head>');
        if ($head_end !== false) {
            $output = substr_replace($output, $this->headAdditions(), $head_end, 0);
        }

        $this->grav->output = $output;
    }

    /**
     * Whether the page has math: a box made in onMarkdownInitialized.
     */
    protected function hasMath($output)
    {
        return strpos($output, 'class="math-inline"') !== false
            || strpos($output, 'class="math-block"') !== false;
    }

    /**
     * The chosen engine: 'katex' (the default) or 'mathjax'. The site's setting, unless the page's front matter has
     * its own (latex-math: engine: mathjax).
     */
    protected function engine()
    {
        $page = $this->grav['page'];
        $settings = $page ? $this->mergeConfig($page) : $this->config->get('plugins.latex-math');

        if (($settings['engine'] ?? 'katex') === 'mathjax') {
            return 'mathjax';
        }
        return 'katex';
    }

    /**
     * Styles for the <head>: KaTeX's own (MathJax adds its own), and hiding the LaTeX until it's drawn – only when
     * JavaScript is on, so without it the LaTeX still shows.
     */
    protected function headAdditions()
    {
        $head = '';

        if ($this->engine() === 'katex') {
            $katex_css = Utils::url('plugin://latex-math/js/katex/katex.min.css') . '?v=' . self::KATEX_VERSION;
            $head .= '<link rel="stylesheet" href="' . $katex_css . '">' . "\n";
        }

        return $head . <<<HTML
<style>
@media (scripting: enabled) {
  .math-inline:not([data-processed]), .math-block:not([data-processed]) { visibility: hidden; }
}
div.math-block { margin: 1rem 0; overflow-x: auto; overflow-y: hidden; }
</style>

HTML;
    }

    /**
     * Scripts for before </body>: the chosen engine, then js/latex-math-init.js to draw the math with it.
     */
    protected function pageAdditions()
    {
        $engine = $this->engine();

        // ?v= makes browsers fetch a new copy after a change
        $init_js = Utils::url('plugin://latex-math/js/latex-math-init.js') . '?v=' . filemtime(__DIR__ . '/js/latex-math-init.js');

        $scripts = '';
        if ($engine === 'katex') {
            $katex_js = Utils::url('plugin://latex-math/js/katex/katex.min.js') . '?v=' . self::KATEX_VERSION;
            $scripts .= '<script src="' . $katex_js . '"></script>' . "\n";

            // KaTeX's copy-tex add-on: copying drawn math copies its LaTeX (MathJax has this in its right-click menu)
            $copy_tex_js = Utils::url('plugin://latex-math/js/katex/contrib/copy-tex.min.js') . '?v=' . self::KATEX_VERSION;
            $scripts .= '<script src="' . $copy_tex_js . '"></script>' . "\n";
        } else {
            // MathJax reads window.MathJax as it loads: js/latex-math-init.js draws the math (so no typeset of the whole
            // page), the fonts come from this plugin, and screen readers get hidden MathML as with KaTeX (MathJax's
            // speech needs .json files, which Grav's standard web server rules block in user/)
            $mathjax_js = Utils::url('plugin://latex-math/js/mathjax/tex-chtml.js') . '?v=' . self::MATHJAX_VERSION;
            $mathjax_settings = [
                'startup' => ['typeset' => false],
                'loader'  => ['paths' => ['mathjax-newcm' => Utils::url('plugin://latex-math/js/mathjax-newcm-font')]],
                'options' => ['menuOptions' => ['settings' => [
                    'enrich' => false, 'speech' => false, 'braille' => false, 'assistiveMml' => true,
                ]]],
            ];
            $scripts .= '<script>window.MathJax = ' . json_encode($mathjax_settings, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) . ";</script>\n";
            $scripts .= '<script src="' . $mathjax_js . '"></script>' . "\n";
        }

        // JSON_HEX_TAG stops a setting from ending the <script> early
        $settings = ['engine' => $engine];

        return "\n" . '<script>window.GravMath = ' . json_encode($settings, JSON_HEX_TAG) . ";</script>\n"
             . $scripts
             . '<script src="' . $init_js . '"></script>' . "\n";
    }
}
