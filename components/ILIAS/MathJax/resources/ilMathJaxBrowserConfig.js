/**
 * Browser configuration for MathJax
 * Will be copied by composer install to public/assets/js/
 * @see \ILIAS\MathJax::init
 */

window.MathJax = {
  tex: {
    inlineMath: [
      ['[tex]', '[/tex]'],
      ['<span class="latex">', '</span>'],
      ['\\(', '\\)']],

    displayMath: [
      ['\\[','\\]'] ]
  },
  svg: {
    fontCache: 'global'
  }
};