var Component = require('../models/component');

// GET /components
exports.index = function(req, res, next) {
  Component.find(function(err, components) {
    if (err) return next(err);
    res.render('components/index', { components: components });
  });
};