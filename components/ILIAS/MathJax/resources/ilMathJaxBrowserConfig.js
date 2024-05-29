// MathJax Configuration
// see https://docs.mathjax.org/en/latest/options/index.html
window.MathJax = {
  options: {
    // skipHtmlTags: {'[+]': ['body']}, // easy way to prevent MathJax except for allowed places
    ignoreHtmlClass: 'tex2jax_ignore', //  class that marks tags not to search
    processHtmlClass: 'tex2jax_process', //  class that marks tags that should be searched
  },
  tex: {
    inlineMath: [
      ['[tex]', '[/tex]'],
      ['<span class="latex">', '</span>'],
      ['\\(', '\\)']],

    displayMath: [
      ['\\[', '\\]']],
  },
  svg: {
    fontCache: 'global',
  },
};
