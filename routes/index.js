var Component = require('../models/component');

// GET /
exports.index = function(req, res, next) {
  Component.find(function(err, components) {
    if (err) return next(err);
    res.render('index', { components: components });
  });
};