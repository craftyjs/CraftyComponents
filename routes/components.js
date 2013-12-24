// GET /components
exports.index = function(req, res) {
  res.render('components/index', { title: 'Components' });
};