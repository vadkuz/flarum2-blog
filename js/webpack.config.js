const config = require('flarum-webpack-config');

module.exports = config({
  // Flarum 2.x: `useExtensions` has been removed. Imports from other extensions
  // must use the `ext:` prefix and are handled via the export registry.
});
