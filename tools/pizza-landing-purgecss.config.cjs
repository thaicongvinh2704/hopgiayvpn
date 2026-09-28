const path = require('node:path');
const theme = path.resolve(__dirname, '../wp-content/themes/custom-box-theme');
const file = relative => path.join(theme, relative).replaceAll('\\', '/');
module.exports = {
  content: [
    file('header.php'),
    file('footer.php'),
    file('inc/setup.php'),
    file('assets/js/main.js'),
    file('page-pizza-boxes-manufacturer.php'),
  ],
  css: [file('assets/css/main.min.css'), file('assets/css/responsive.min.css')],
  safelist: {
    standard: ['active', 'open', 'current-menu-item', 'current-menu-ancestor'],
    deep: [/^(header|footer|mobile|menu|nav|search|drawer|sticky|is-|has-)/],
  },
};
