var URL = require('url');
var githubContents = require('github-contents');
var Component = require('../models/component');

// POST /hooks/github
exports.github = function(req, res, next) {
  var payload = JSON.parse(req.body.payload);
  var repoUrl = URL.parse(payload.repository.url);

  githubContents(repoUrl.path.substring(1), 'component.json', function(err, contents) {
    if (err) {
      return next(err);
    }

    var component = new Component(JSON.parse(contents));
    var upsertData = component.toObject();
    delete upsertData._id;

    Component.update({ name: component.name }, upsertData, { upsert: true }, function(err) {
      if (err) {
        return next(err);
      }

      res.json(200);
    });
  });
};